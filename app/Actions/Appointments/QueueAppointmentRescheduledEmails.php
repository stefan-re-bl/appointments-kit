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
        $appointment->loadMissing(['therapist.user', 'sessionType']);

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
            $appointment->therapist->user->email,
            $appointment->therapist->user->name,
        )->queue(
            new AppointmentRescheduled(
                appointment: $appointment,
                previousStartsAt: $previousStartsAt,
                previousEndsAt: $previousEndsAt,
                recipientTimezone: $appointment->therapist->timezone ?: 'UTC',
                recipientType: 'therapist',
            )
        );
    }
}