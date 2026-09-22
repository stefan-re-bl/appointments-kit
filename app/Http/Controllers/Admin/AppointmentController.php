<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\AppointmentStatus;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\FilterAppointmentsRequest;
use App\Models\Appointment;
use App\Models\Professional;
use App\Services\TimezoneService;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Validation\Rule;

final class AppointmentController extends Controller implements HasMiddleware
{
    private const ADMIN_TIMEZONE = 'America/Argentina/Buenos_Aires';

    /**
     * @return array<int, Middleware>
     */
    public static function middleware(): array
    {
        return [
            new Middleware('auth'),
            new Middleware('verified'),
            new Middleware('admin'),
        ];
    }

    public function index(FilterAppointmentsRequest $request, TimezoneService $timezoneService): View
    {
        $filters = $request->validated();

        $timezone = self::ADMIN_TIMEZONE;

        $today = CarbonImmutable::now($timezone)->toDateString();

        $todayStartUtc = $timezoneService->toUtc($today.' 00:00:00', $timezone);
        $todayEndUtc = $timezoneService->toUtc($today.' 23:59:59', $timezone);

        $summary = [
            'total' => Appointment::query()->count(),
            'confirmed' => Appointment::query()
                ->where('status', AppointmentStatus::CONFIRMED->value)
                ->count(),
            'pending_payment' => Appointment::query()
                ->where('payment_status', PaymentStatus::PENDING->value)
                ->count(),
            'today' => Appointment::query()
                ->whereBetween('starts_at', [$todayStartUtc, $todayEndUtc])
                ->count(),
        ];

        $professionals = Professional::query()
            ->with('user')
            ->orderBy('id')
            ->get();

        return view('admin.appointments.index', [
            'professionals' => $professionals,
            'appointmentStatuses' => AppointmentStatus::cases(),
            'paymentStatuses' => PaymentStatus::cases(),
            'filters' => $filters,
            'summary' => $summary,
            'timezone' => $timezone,
        ]);
    }

    public function events(Request $request, TimezoneService $timezoneService): JsonResponse
    {
        $filters = $this->validatedCalendarFilters($request, [
            'start' => ['required', 'date'],
            'end' => ['required', 'date', 'after:start'],
        ]);
        $timezone = $this->currentTimezone();

        $rangeStartUtc = CarbonImmutable::parse((string) $filters['start'], $timezone)->setTimezone('UTC');
        $rangeEndUtc = CarbonImmutable::parse((string) $filters['end'], $timezone)->setTimezone('UTC');

        $appointments = $this->appointmentsQuery($filters)
            ->where('starts_at', '>=', $rangeStartUtc)
            ->where('starts_at', '<', $rangeEndUtc)
            ->orderBy('starts_at')
            ->get();

        return response()->json($appointments->map(function (Appointment $appointment) use ($timezoneService, $timezone): array {
            $startsAt = $timezoneService->toLocal($appointment->starts_at, $timezone);
            $endsAt = $timezoneService->toLocal($appointment->ends_at, $timezone);

            return [
                'id' => (string) $appointment->id,
                'title' => $appointment->professional->user->name.' - '.$appointment->customer_name,
                'start' => $startsAt->toIso8601String(),
                'end' => $endsAt->toIso8601String(),
                'backgroundColor' => $this->eventColorForStatus($appointment->status),
                'borderColor' => $this->eventColorForStatus($appointment->status),
                'textColor' => '#ffffff',
                'extendedProps' => [
                    'local_date' => $startsAt->toDateString(),
                    'patient_name' => $appointment->customer_name,
                    'customer_name' => $appointment->customer_name,
                    'professional_name' => $appointment->professional->user->name,
                    'status' => $appointment->status->value,
                    'payment_status' => $appointment->payment_status->value,
                ],
            ];
        })->values());
    }

    public function day(Request $request, TimezoneService $timezoneService): JsonResponse
    {
        $filters = $this->validatedCalendarFilters($request, [
            'date' => ['required', 'date_format:Y-m-d'],
        ]);
        $timezone = $this->currentTimezone();
        $date = (string) $filters['date'];

        $dayStartUtc = $timezoneService->toUtc($date.' 00:00:00', $timezone)->subDay();
        $dayEndUtc = $timezoneService->toUtc($date.' 23:59:59', $timezone)->addDay();

        $appointments = $this->appointmentsQuery($filters)
            ->whereBetween('starts_at', [$dayStartUtc, $dayEndUtc])
            ->orderBy('starts_at')
            ->get()
            ->filter(
                fn (Appointment $appointment): bool => $timezoneService
                    ->toLocal($appointment->starts_at, $timezone)
                    ->toDateString() === $date
            )
            ->values();

        return response()->json([
            'date' => CarbonImmutable::parse($date, $timezone)->format('d/m/Y'),
            'appointments' => $appointments->map(function (Appointment $appointment) use ($timezoneService, $timezone): array {
                return [
                    'id' => $appointment->id,
                    'patient_name' => $appointment->customer_name,
                    'patient_email' => $appointment->customer_email,
                    'patient_timezone' => $appointment->customer_timezone,
                    'customer_name' => $appointment->customer_name,
                    'customer_email' => $appointment->customer_email,
                    'customer_timezone' => $appointment->customer_timezone,
                    'professional_name' => $appointment->professional->user->name,
                    'session_type' => $appointment->service->name,
                    'service' => $appointment->service->name,
                    'time_range' => $timezoneService->formatForDisplay($appointment->starts_at, 'H:i', $timezone)
                        .' - '.$timezoneService->formatForDisplay($appointment->ends_at, 'H:i', $timezone),
                    'status' => $appointment->status->value,
                    'status_label' => __('app.appointment_status.'.$appointment->status->value),
                    'payment_status' => $appointment->payment_status->value,
                    'payment_status_label' => __('app.payment_status.'.$appointment->payment_status->value),
                    'price' => $appointment->currency.' '.number_format((float) $appointment->price, 2),
                ];
            })->values(),
        ]);
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function appointmentsQuery(array $filters): Builder
    {
        return Appointment::query()
            ->with(['professional.user', 'service'])
            ->when(! empty($filters['professional_id']), function (Builder $query) use ($filters): void {
                $query->where('professional_id', $filters['professional_id']);
            })
            ->when(! empty($filters['status']), function (Builder $query) use ($filters): void {
                $query->where('status', $filters['status']);
            })
            ->when(! empty($filters['payment_status']), function (Builder $query) use ($filters): void {
                $query->where('payment_status', $filters['payment_status']);
            });
    }

    /**
     * @param  array<string, mixed>  $rules
     * @return array<string, mixed>
     */
    private function validatedCalendarFilters(Request $request, array $rules): array
    {
        return $request->validate($rules + [
            'professional_id' => ['nullable', 'integer', Rule::exists('professionals', 'id')],
            'professional_id' => ['nullable', 'integer', Rule::exists('professionals', 'id')],
            'status' => ['nullable', Rule::enum(AppointmentStatus::class)],
            'payment_status' => ['nullable', Rule::enum(PaymentStatus::class)],
        ]) + [
            'professional_id' => $request->input('professional_id', $request->input('professional_id')),
        ];
    }

    private function currentTimezone(): string
    {
        return self::ADMIN_TIMEZONE;
    }

    private function eventColorForStatus(AppointmentStatus $status): string
    {
        return match ($status) {
            AppointmentStatus::CONFIRMED => '#047857',
            AppointmentStatus::PENDING => '#b45309',
            AppointmentStatus::CANCELLED => '#64748b',
            AppointmentStatus::COMPLETED => '#540D6D',
        };
    }
}
