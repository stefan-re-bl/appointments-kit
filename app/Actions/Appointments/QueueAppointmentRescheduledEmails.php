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
        $appointment->loadMissing(['professional.user', 'sessionType']);

        Mail::to(
            $appointment->patient_email,
            $appointment->patient_name,
        )->queue(
            new AppointmentRescheduled(
                appointment: $appointment,
                previousStartsAt: $previousStartsAt,
                previousEndsAt: $previousEndsAt,
                recipientTimezone: $appointment->patient_timezone ?: 'UTC',
                recipientType: 'patient',
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
                recipientType: 'professional',
            )
        );
    }
}
