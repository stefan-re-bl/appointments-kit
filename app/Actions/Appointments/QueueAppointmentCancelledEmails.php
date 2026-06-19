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
        $appointment->loadMissing(['therapist.user', 'sessionType']);

        Mail::to(
            $appointment->patient_email,
            $appointment->patient_name,
        )->queue(
            new AppointmentCancelled(
                appointment: $appointment,
                recipientTimezone: $appointment->patient_timezone ?: 'UTC',
                recipientType: 'patient',
            )
        );

        Mail::to(
            $appointment->therapist->user->email,
            $appointment->therapist->user->name,
        )->queue(
            new AppointmentCancelled(
                appointment: $appointment,
                recipientTimezone: $appointment->therapist->timezone ?: 'UTC',
                recipientType: 'therapist',
            )
        );
    }
}