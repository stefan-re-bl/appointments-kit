<?php

declare(strict_types=1);

namespace App\Actions\Appointments;

use App\Mail\AppointmentRescheduled;
use App\Models\Appointment;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Mail;

class QueueAppointmentRescheduledEmails
{
    public function execute(
        Appointment $appointment,
        CarbonInterface $previousStartsAt,
        CarbonInterface $previousEndsAt,
    ): void {
        if (! (bool) config('features.email_notifications', true)) {
            return;
        }

        $appointment->loadMissing(['professional.user', 'service']);

        Mail::to(
            $appointment->customer_email,
            $appointment->customer_name,
        )->queue(
            new AppointmentRescheduled(
                appointment: $appointment,
                previousStartsAt: $previousStartsAt,
                previousEndsAt: $previousEndsAt,
                recipientTimezone: $appointment->customer_timezone ?: 'UTC',
                recipientType: AppointmentRescheduled::RECIPIENT_CUSTOMER,
            )
        );

        Mail::to(
            $appointment->professional->user->email,
            $appointment->professional->user->name,
        )->queue(
            new AppointmentRescheduled(
                appointment: $appointment,
                previousStartsAt: $previousStartsAt,
                previousEndsAt: $previousEndsAt,
                recipientTimezone: $appointment->professional->timezone ?: 'UTC',
                recipientType: AppointmentRescheduled::RECIPIENT_PROFESSIONAL,
            )
        );
    }
}
