<?php

declare(strict_types=1);

namespace App\Services\Reports;

use App\Enums\AppointmentStatus;
use App\Enums\PaymentStatus;
use App\Models\Appointment;
use App\Services\TimezoneService;
use Carbon\CarbonImmutable;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

final readonly class AppointmentReportService
{
    public function __construct(
        private TimezoneService $timezoneService,
    ) {
    }

    /**
     * @param array<string, mixed> $filters
     * @return array{
     *     filters: array{date_from: string|null, date_to: string|null, therapist_id: int|null},
     *     timezone: string,
     *     metrics: array<string, int|float>,
     *     currency_summaries: Collection<int, array<string, int|float|string>>,
     *     therapist_summaries: Collection<int, array<string, int|float|string>>,
     *     rows: Collection<int, array<string, int|float|string|null>>
     * }
     */
    public function build(array $filters = []): array
    {
        $normalizedFilters = $this->normalizeFilters($filters);

        $query = Appointment::query()
            ->with(['therapist.user', 'sessionType'])
            ->orderBy('starts_at')
            ->orderBy('id');

        if ($normalizedFilters['therapist_id'] !== null) {
            $query->where('therapist_id', $normalizedFilters['therapist_id']);
        }

        $this->applyDateRange($query, $normalizedFilters);

        /** @var Collection<int, Appointment> $appointments */
        $appointments = $query->get();

        return [
            'filters' => $normalizedFilters,
            'timezone' => $this->currentTimezone(),
            'metrics' => $this->buildMetrics($appointments),
            'currency_summaries' => $this->buildCurrencySummaries($appointments),
            'therapist_summaries' => $this->buildTherapistSummaries($appointments),
            'rows' => $this->buildRows($appointments),
        ];
    }

    /**
     * @param array<string, mixed> $filters
     * @return array{date_from: string|null, date_to: string|null, therapist_id: int|null}
     */
    private function normalizeFilters(array $filters): array
    {
        $therapistId = $filters['therapist_id'] ?? null;

        return [
            'date_from' => isset($filters['date_from']) && $filters['date_from'] !== ''
                ? (string) $filters['date_from']
                : null,
            'date_to' => isset($filters['date_to']) && $filters['date_to'] !== ''
                ? (string) $filters['date_to']
                : null,
            'therapist_id' => is_numeric($therapistId) ? (int) $therapistId : null,
        ];
    }

    /**
     * @param Builder<Appointment> $query
     * @param array{date_from: string|null, date_to: string|null, therapist_id: int|null} $filters
     */
    private function applyDateRange(Builder $query, array $filters): void
    {
        if ($filters['date_from'] !== null) {
            $query->where(
                'starts_at',
                '>=',
                $this->localDateToUtcBoundary($filters['date_from'], '00:00:00')->toDateTimeString(),
            );
        }

        if ($filters['date_to'] !== null) {
            $query->where(
                'starts_at',
                '<=',
                $this->localDateToUtcBoundary($filters['date_to'], '23:59:59')->toDateTimeString(),
            );
        }
    }

    private function localDateToUtcBoundary(string $date, string $time): CarbonImmutable
    {
        $utcDate = $this->timezoneService->toUtc(
            "{$date} {$time}",
            $this->currentTimezone(),
        );

        if ($utcDate instanceof DateTimeInterface) {
            return CarbonImmutable::instance($utcDate)->setTimezone('UTC');
        }

        return CarbonImmutable::parse((string) $utcDate, 'UTC')->setTimezone('UTC');
    }

    /**
     * @param Collection<int, Appointment> $appointments
     * @return array<string, int|float>
     */
    private function buildMetrics(Collection $appointments): array
    {
        $cancelledAppointments = $appointments->filter(
            fn (Appointment $appointment): bool => $this->statusValue($appointment) === AppointmentStatus::CANCELLED->value,
        );

        $chargeableAppointments = $appointments->filter(
            fn (Appointment $appointment): bool => $this->isChargeable($appointment),
        );

        $paidAppointments = $chargeableAppointments->filter(
            fn (Appointment $appointment): bool => $this->isCollected($appointment),
        );

        $pendingAppointments = $chargeableAppointments->filter(
            fn (Appointment $appointment): bool => $this->isPendingPayment($appointment),
        );

        $paymentConversionBase = $paidAppointments->count() + $pendingAppointments->count();

        return [
            'total_appointments' => $appointments->count(),
            'cancelled_appointments' => $cancelledAppointments->count(),
            'active_chargeable_appointments' => $chargeableAppointments->count(),
            'paid_appointments' => $paidAppointments->count(),
            'pending_payment_appointments' => $pendingAppointments->count(),
            'payment_conversion_rate' => $paymentConversionBase > 0
                ? round(($paidAppointments->count() / $paymentConversionBase) * 100, 2)
                : 0.0,
        ];
    }

    /**
     * @param Collection<int, Appointment> $appointments
     * @return Collection<int, array<string, int|float|string>>
     */
    private function buildCurrencySummaries(Collection $appointments): Collection
    {
        return $appointments
            ->filter(fn (Appointment $appointment): bool => $this->isChargeable($appointment))
            ->groupBy(fn (Appointment $appointment): string => $this->currency($appointment))
            ->map(function (Collection $items, string $currency): array {
                /** @var Collection<int, Appointment> $items */
                $paidAppointments = $items->filter(
                    fn (Appointment $appointment): bool => $this->isCollected($appointment),
                );

                $pendingAppointments = $items->filter(
                    fn (Appointment $appointment): bool => $this->isPendingPayment($appointment),
                );

                return [
                    'currency' => $currency,
                    'appointments_count' => $items->count(),
                    'estimated_amount' => $this->sumAmount($items),
                    'collected_amount' => $this->sumAmount($paidAppointments),
                    'pending_amount' => $this->sumAmount($pendingAppointments),
                ];
            })
            ->values();
    }

    /**
     * @param Collection<int, Appointment> $appointments
     * @return Collection<int, array<string, int|float|string>>
     */
    private function buildTherapistSummaries(Collection $appointments): Collection
    {
        return $appointments
            ->filter(fn (Appointment $appointment): bool => $this->isChargeable($appointment))
            ->groupBy(
                fn (Appointment $appointment): string => $appointment->therapist_id . '|' . $this->currency($appointment),
            )
            ->map(function (Collection $items): array {
                /** @var Collection<int, Appointment> $items */
                /** @var Appointment $first */
                $first = $items->first();

                $paidAppointments = $items->filter(
                    fn (Appointment $appointment): bool => $this->isCollected($appointment),
                );

                $pendingAppointments = $items->filter(
                    fn (Appointment $appointment): bool => $this->isPendingPayment($appointment),
                );

                return [
                    'therapist_id' => $first->therapist_id,
                    'therapist_name' => $first->therapist?->user?->name ?? '—',
                    'therapist_email' => $first->therapist?->user?->email ?? '—',
                    'currency' => $this->currency($first),
                    'appointments_count' => $items->count(),
                    'paid_appointments' => $paidAppointments->count(),
                    'pending_payment_appointments' => $pendingAppointments->count(),
                    'estimated_amount' => $this->sumAmount($items),
                    'collected_amount' => $this->sumAmount($paidAppointments),
                    'pending_amount' => $this->sumAmount($pendingAppointments),
                ];
            })
            ->sortByDesc(fn (array $row): float => (float) $row['pending_amount'])
            ->values();
    }

    /**
     * @param Collection<int, Appointment> $appointments
     * @return Collection<int, array<string, int|float|string|null>>
     */
    private function buildRows(Collection $appointments): Collection
    {
        return $appointments->map(function (Appointment $appointment): array {
            return [
                'appointment_id' => $appointment->id,
                'patient_name' => $appointment->patient_name,
                'patient_email' => $appointment->patient_email,
                'therapist_name' => $appointment->therapist?->user?->name ?? '—',
                'therapist_email' => $appointment->therapist?->user?->email ?? '—',
                'session_type_name' => $appointment->sessionType?->name ?? '—',
                'starts_at_display' => $this->timezoneService->formatForDisplay($appointment->starts_at, 'd/m/Y H:i'),
                'ends_at_display' => $this->timezoneService->formatForDisplay($appointment->ends_at, 'd/m/Y H:i'),
                'starts_at_utc' => $this->formatUtc($appointment->starts_at),
                'ends_at_utc' => $this->formatUtc($appointment->ends_at),
                'status' => $this->statusValue($appointment),
                'payment_status' => $this->paymentStatusValue($appointment),
                'paid_at_display' => $appointment->paid_at !== null
                    ? $this->timezoneService->formatForDisplay($appointment->paid_at, 'd/m/Y H:i')
                    : null,
                'paid_at_utc' => $appointment->paid_at !== null
                    ? $this->formatUtc($appointment->paid_at)
                    : null,
                'price' => $this->amount($appointment),
                'currency' => $this->currency($appointment),
                'estimated_amount' => $this->isChargeable($appointment) ? $this->amount($appointment) : 0.0,
                'collected_amount' => $this->isCollected($appointment) ? $this->amount($appointment) : 0.0,
                'pending_amount' => $this->isPendingPayment($appointment) ? $this->amount($appointment) : 0.0,
            ];
        });
    }

    private function isChargeable(Appointment $appointment): bool
    {
        return $this->statusValue($appointment) !== AppointmentStatus::CANCELLED->value
            && $this->paymentStatusValue($appointment) !== PaymentStatus::WAIVED->value;
    }

    private function isCollected(Appointment $appointment): bool
    {
        return $this->isChargeable($appointment)
            && $this->paymentStatusValue($appointment) === PaymentStatus::PAID->value
            && $appointment->paid_at !== null;
    }

    private function isPendingPayment(Appointment $appointment): bool
    {
        return $this->isChargeable($appointment)
            && $this->paymentStatusValue($appointment) === PaymentStatus::PENDING->value;
    }

    private function statusValue(Appointment $appointment): string
    {
        $status = $appointment->status;

        return $status instanceof AppointmentStatus ? $status->value : (string) $status;
    }

    private function paymentStatusValue(Appointment $appointment): string
    {
        $paymentStatus = $appointment->payment_status;

        return $paymentStatus instanceof PaymentStatus ? $paymentStatus->value : (string) $paymentStatus;
    }

    private function sumAmount(Collection $appointments): float
    {
        return round(
            (float) $appointments->sum(fn (Appointment $appointment): float => $this->amount($appointment)),
            2,
        );
    }

    private function amount(Appointment $appointment): float
    {
        return round((float) $appointment->price, 2);
    }

    private function currency(Appointment $appointment): string
    {
        $currency = $appointment->currency;

        return is_string($currency) && $currency !== '' ? $currency : 'N/A';
    }

    private function currentTimezone(): string
    {
        $timezone = app()->bound('user.timezone')
            ? app('user.timezone')
            : config('app.timezone', 'UTC');

        if (is_string($timezone) && in_array($timezone, timezone_identifiers_list(), true)) {
            return $timezone;
        }

        return 'UTC';
    }

    private function formatUtc(mixed $date): string
    {
        if ($date instanceof DateTimeInterface) {
            return CarbonImmutable::instance($date)->setTimezone('UTC')->format('Y-m-d H:i:s');
        }

        return CarbonImmutable::parse((string) $date, 'UTC')->setTimezone('UTC')->format('Y-m-d H:i:s');
    }
}