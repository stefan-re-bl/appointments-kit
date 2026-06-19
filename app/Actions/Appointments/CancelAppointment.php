<?php

declare(strict_types=1);

namespace App\Actions\Appointments;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use Illuminate\Support\Facades\DB;

class CancelAppointment
{
    public function execute(Appointment $appointment): Appointment
    {
        $shouldQueueEmails = false;

        $cancelledAppointment = DB::transaction(function () use ($appointment, &$shouldQueueEmails): Appointment {
            $lockedAppointment = Appointment::query()
                ->with(['therapist.user', 'sessionType'])
                ->whereKey($appointment->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($this->isAlreadyCancelled($lockedAppointment)) {
                return $lockedAppointment;
            }

            $lockedAppointment->forceFill([
                'status' => AppointmentStatus::CANCELLED,
            ])->save();

            $shouldQueueEmails = true;

            return $lockedAppointment->refresh();
        });

        if ($shouldQueueEmails) {
            app(QueueAppointmentCancelledEmails::class)->execute($cancelledAppointment);
        }

        return $cancelledAppointment;
    }

    private function isAlreadyCancelled(Appointment $appointment): bool
    {
        $status = $appointment->status instanceof AppointmentStatus
            ? $appointment->status->value
            : (string) $appointment->status;

        return $status === AppointmentStatus::CANCELLED->value;
    }
}