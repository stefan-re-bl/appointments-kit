<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Appointments\CancelAppointment;
use App\Models\Appointment;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Routing\Controllers\HasMiddleware;

final class PublicAppointmentCancellationController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [];
    }

    public function __invoke(string $token, CancelAppointment $cancelAppointment): RedirectResponse|Response
    {
        $appointment = Appointment::query()
            ->with(['therapist.user', 'sessionType'])
            ->where('token', $token)
            ->first();

        if (! $appointment instanceof Appointment) {
            return response()->view('appointments.public-not-found', status: 404);
        }

        try {
            $cancelAppointment->execute($appointment);
        } catch (DomainException) {
            return redirect()
                ->route('appointments.public.show', $appointment->token)
                ->with('warning', __('appointment_policy.flash.cancel_not_allowed'));
        }

        return redirect()
            ->route('appointments.public.show', $appointment->token)
            ->with('success', __('appointment_policy.flash.cancelled'));
    }
}