<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Services\CancellationPolicyService;
use App\Services\TimezoneService;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

final class PublicAppointmentRescheduleController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('signed'),
        ];
    }

    public function __invoke(
        Request $request,
        string $token,
        CancellationPolicyService $cancellationPolicyService,
        TimezoneService $timezoneService,
    ): View|Response {
        $appointment = Appointment::query()
            ->with(['therapist.user', 'sessionType'])
            ->where('token', $token)
            ->first();

        if (! $appointment instanceof Appointment) {
            return response()->view('appointments.public-not-found', status: 404);
        }

        $patientTimezone = $appointment->patient_timezone ?: 'UTC';

        app()->instance('user.timezone', $patientTimezone);

        $currentStartsAt = $timezoneService->formatForDisplay($appointment->starts_at, 'd/m/Y H:i');
        $currentEndsAt = $timezoneService->formatForDisplay($appointment->ends_at, 'H:i');

        $currentAppointmentDate = Carbon::parse($appointment->starts_at, 'UTC')
            ->setTimezone($patientTimezone)
            ->toDateString();

        $minimumDate = Carbon::now($patientTimezone)->toDateString();

        $initialDate = $currentAppointmentDate >= $minimumDate
            ? $currentAppointmentDate
            : $minimumDate;

        return view('appointments.public.reschedule', [
            'appointment' => $appointment,
            'canReschedule' => $cancellationPolicyService->canReschedule($appointment),
            'currentStartsAt' => $currentStartsAt,
            'currentEndsAt' => $currentEndsAt,
            'formAction' => $request->fullUrl(),
            'initialDate' => $initialDate,
            'minimumDate' => $minimumDate,
            'patientTimezone' => $patientTimezone,
            'slotsEndpoint' => route('api.slots.index'),
        ]);
    }
}