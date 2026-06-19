<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Appointments\RescheduleAction;
use App\Models\Appointment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Validation\ValidationException;

final class PublicAppointmentRescheduleStoreController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('signed'),
        ];
    }

    /**
     * @throws ValidationException
     */
    public function __invoke(
        Request $request,
        string $token,
        RescheduleAction $rescheduleAction,
    ): RedirectResponse|Response {
        $appointment = Appointment::query()
            ->with(['therapist.user', 'sessionType'])
            ->where('token', $token)
            ->first();

        if (! $appointment instanceof Appointment) {
            return response()->view('appointments.public.not-found', status: 404);
        }

        app()->instance('user.timezone', $appointment->patient_timezone ?: 'UTC');

        $validated = $request->validate([
            'date' => ['required', 'date_format:Y-m-d'],
            'start_utc' => ['required', 'date'],
        ]);

        $rescheduledAppointment = $rescheduleAction->execute(
            $appointment,
            (string) $validated['date'],
            (string) $validated['start_utc'],
        );

        if (! $rescheduledAppointment instanceof Appointment) {
            throw ValidationException::withMessages([
                'start_utc' => __('appointment_reschedule.errors.slot_unavailable'),
            ]);
        }

        return redirect()
            ->route('appointments.public.show', ['token' => $rescheduledAppointment->token])
            ->with('status', __('appointment_reschedule.success'));
    }
}