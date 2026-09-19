<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Mail\BookingConfirmed;
use App\Models\Appointment;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

final class SendBookingConfirmedEmails implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly int $appointmentId,
    ) {}

    public function handle(): void
    {
        $appointment = Appointment::query()
            ->with(['professional.user', 'sessionType'])
            ->find($this->appointmentId);

        if (! $appointment) {
            return;
        }

        Mail::to($appointment->patient_email)->send(
            new BookingConfirmed(
                appointment: $appointment,
                recipientType: BookingConfirmed::RECIPIENT_PATIENT,
            )
        );

        $professionalEmail = $appointment->professional?->user?->email;

        if (! is_string($professionalEmail) || $professionalEmail === '') {
            return;
        }

        Mail::to($professionalEmail)->send(
            new BookingConfirmed(
                appointment: $appointment,
                recipientType: BookingConfirmed::RECIPIENT_PROFESSIONAL,
            )
        );
    }
}
