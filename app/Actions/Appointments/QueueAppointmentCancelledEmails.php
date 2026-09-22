<?php

declare(strict_types=1);

namespace App\Actions\Appointments;

use App\Mail\AppointmentCancelled;
use App\Models\Appointment;
use Illuminate\Support\Facades\Mail;

class QueueAppointmentCancelledEmails
{
    public function execute(Appointment $appointment): void
    {
        if (! (bool) config('features.email_notifications', true)) {
            return;
        }

        $appointment->loadMissing(['professional.user', 'service']);

        Mail::to(
            $appointment->customer_email,
            $appointment->customer_name,
        )->queue(
            new AppointmentCancelled(
                appointment: $appointment,
                recipientTimezone: $appointment->customer_timezone ?: 'UTC',
                recipientType: AppointmentCancelled::RECIPIENT_CUSTOMER,
            )
        );

        Mail::to(
            $appointment->professional->user->email,
            $appointment->professional->user->name,
        )->queue(
            new AppointmentCancelled(
                appointment: $appointment,
                recipientTimezone: $appointment->professional->timezone ?: 'UTC',
                recipientType: AppointmentCancelled::RECIPIENT_PROFESSIONAL,
            )
        );
    }
}
