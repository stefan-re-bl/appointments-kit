<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Rules\ValidTimezone;
use App\Services\CancellationPolicyService;
use App\Services\SlotGenerationService;
use App\Services\TimezoneService;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\ValidationException;

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
            ->with(['professional.user', 'sessionType'])
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
        $slotsEndpoint = $request->has('expires')
            ? URL::temporarySignedRoute(
                'appointments.public.reschedule.slots',
                Carbon::createFromTimestampUTC((int) $request->query('expires')),
                ['token' => $appointment->token],
            )
            : URL::signedRoute('appointments.public.reschedule.slots', ['token' => $appointment->token]);

        return view('appointments.public.reschedule', [
            'appointment' => $appointment,
            'canReschedule' => $cancellationPolicyService->canReschedule($appointment),
            'currentStartsAt' => $currentStartsAt,
            'currentEndsAt' => $currentEndsAt,
            'formAction' => $request->fullUrl(),
            'initialDate' => $initialDate,
            'minimumDate' => $minimumDate,
            'patientTimezone' => $patientTimezone,
            'slotsEndpoint' => $slotsEndpoint,
        ]);
    }

    /**
     * @throws ValidationException
     */
    public function slots(
        Request $request,
        string $token,
        CancellationPolicyService $cancellationPolicyService,
        SlotGenerationService $slotGenerationService,
    ): JsonResponse|Response {
        $validated = $request->validate([
            'date' => ['required', 'date_format:Y-m-d'],
            'timezone' => ['required', 'string', new ValidTimezone],
        ]);

        $appointment = Appointment::query()
            ->with(['professional.user', 'sessionType'])
            ->where('token', $token)
            ->first();

        if (! $appointment instanceof Appointment) {
            return response()->view('appointments.public-not-found', status: 404);
        }

        if (! $cancellationPolicyService->canReschedule($appointment)) {
            abort(403);
        }

        $timezone = ValidTimezone::normalize($validated['timezone']);

        if ($timezone === null) {
            throw ValidationException::withMessages([
                'timezone' => __('booking_timezone.invalid'),
            ]);
        }

        $slots = $slotGenerationService->generate(
            $appointment->professional,
            $validated['date'],
            (int) $appointment->sessionType->duration_minutes,
            $timezone,
        );

        return response()->json($slots);
    }
}
