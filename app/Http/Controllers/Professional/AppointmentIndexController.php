<?php

declare(strict_types=1);

namespace App\Http\Controllers\Professional;

use App\Enums\AppointmentStatus;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Professional;
use App\Models\User;
use App\Services\TimezoneService;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Validation\Rule;

class AppointmentIndexController extends Controller implements HasMiddleware
{
    /**
     * @return array<int, Middleware>
     */
    public static function middleware(): array
    {
        return [
            new Middleware('auth'),
            new Middleware('verified'),
        ];
    }

    public function __invoke(Request $request, TimezoneService $timezoneService): View
    {
        $professional = $this->currentProfessional($request);

        app()->instance('user.timezone', $professional->timezone);

        $appointments = Appointment::query()
            ->with('professional.user')
            ->where('professional_id', $professional->id)
            ->orderByDesc('starts_at')
            ->paginate(15);

        return view('professional.appointments.index', [
            'appointments' => $appointments,
            'appointmentStatuses' => AppointmentStatus::cases(),
            'paymentStatuses' => PaymentStatus::cases(),
            'timezoneService' => $timezoneService,
            'professionalTimezone' => $professional->timezone,
        ]);
    }

    public function events(Request $request, TimezoneService $timezoneService): JsonResponse
    {
        $professional = $this->currentProfessional($request);
        $filters = $this->validatedCalendarFilters($request, [
            'start' => ['required', 'date'],
            'end' => ['required', 'date', 'after:start'],
        ]);
        $timezone = $professional->timezone;

        $rangeStartUtc = CarbonImmutable::parse((string) $filters['start'], $timezone)->setTimezone('UTC');
        $rangeEndUtc = CarbonImmutable::parse((string) $filters['end'], $timezone)->setTimezone('UTC');

        $appointments = $this->appointmentsQuery($professional, $filters)
            ->where('starts_at', '>=', $rangeStartUtc)
            ->where('starts_at', '<', $rangeEndUtc)
            ->orderBy('starts_at')
            ->get();

        return response()->json($appointments->map(function (Appointment $appointment) use ($timezoneService, $timezone): array {
            $startsAt = $timezoneService->toLocal($appointment->starts_at, $timezone);
            $endsAt = $timezoneService->toLocal($appointment->ends_at, $timezone);

            return [
                'id' => (string) $appointment->id,
                'title' => $appointment->patient_name,
                'start' => $startsAt->toIso8601String(),
                'end' => $endsAt->toIso8601String(),
                'backgroundColor' => $this->eventColorForStatus($appointment->status),
                'borderColor' => $this->eventColorForStatus($appointment->status),
                'textColor' => '#ffffff',
                'extendedProps' => [
                    'local_date' => $startsAt->toDateString(),
                    'patient_name' => $appointment->patient_name,
                    'professional_name' => $appointment->professional->user->name,
                    'status' => $appointment->status->value,
                    'payment_status' => $appointment->payment_status->value,
                ],
            ];
        })->values());
    }

    public function day(Request $request, TimezoneService $timezoneService): JsonResponse
    {
        $professional = $this->currentProfessional($request);
        $filters = $this->validatedCalendarFilters($request, [
            'date' => ['required', 'date_format:Y-m-d'],
        ]);
        $timezone = $professional->timezone;
        $date = (string) $filters['date'];

        $dayStartUtc = $timezoneService->toUtc($date.' 00:00:00', $timezone)->subDay();
        $dayEndUtc = $timezoneService->toUtc($date.' 23:59:59', $timezone)->addDay();

        $appointments = $this->appointmentsQuery($professional, $filters)
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
                    'patient_name' => $appointment->patient_name,
                    'patient_email' => $appointment->patient_email,
                    'patient_timezone' => $appointment->patient_timezone,
                    'professional_name' => $appointment->professional->user->name,
                    'time_range' => $timezoneService->formatForDisplay($appointment->starts_at, 'H:i', $timezone)
                        .' - '.$timezoneService->formatForDisplay($appointment->ends_at, 'H:i', $timezone),
                    'status' => $appointment->status->value,
                    'status_label' => __('app.appointment_status.'.$appointment->status->value),
                    'payment_status' => $appointment->payment_status->value,
                    'payment_status_label' => __('app.payment_status.'.$appointment->payment_status->value),
                ];
            })->values(),
        ]);
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function appointmentsQuery(Professional $professional, array $filters): Builder
    {
        return Appointment::query()
            ->with('professional.user')
            ->where('professional_id', $professional->id)
            ->when(! empty($filters['status']), function (Builder $query) use ($filters): void {
                $query->where('status', $filters['status']);
            })
            ->when(! empty($filters['payment_status']), function (Builder $query) use ($filters): void {
                $query->where('payment_status', $filters['payment_status']);
            });
    }

    private function currentProfessional(Request $request): Professional
    {
        /** @var User|null $user */
        $user = $request->user();

        if ($user === null || $user->professional === null) {
            abort(403);
        }

        return $user->professional;
    }

    /**
     * @param  array<string, mixed>  $rules
     * @return array<string, mixed>
     */
    private function validatedCalendarFilters(Request $request, array $rules): array
    {
        return $request->validate($rules + [
            'status' => ['nullable', Rule::enum(AppointmentStatus::class)],
            'payment_status' => ['nullable', Rule::enum(PaymentStatus::class)],
        ]);
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
