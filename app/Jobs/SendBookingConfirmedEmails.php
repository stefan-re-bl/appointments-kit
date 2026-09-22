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
        if (! (bool) config('features.email_notifications', true)) {
            return;
        }

        $appointment = Appointment::query()
            ->with(['professional.user', 'service'])
            ->find($this->appointmentId);

        if (! $appointment) {
            return;
        }

        Mail::to($appointment->customer_email)->send(
            new BookingConfirmed(
                appointment: $appointment,
                recipientType: BookingConfirmed::RECIPIENT_CUSTOMER,
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
