<?php

declare(strict_types=1);

namespace App\Actions\Appointments;

use App\Enums\AppointmentStatus;
use App\Enums\NotificationEvent;
use App\Models\Appointment;
use App\Services\CancellationPolicyService;
use App\Services\Notifications\WhatsAppDeliveryDispatcher;
use DomainException;
use Illuminate\Support\Facades\DB;

class CancelAppointment
{
    public function execute(Appointment $appointment): Appointment
    {
        $shouldQueueEmails = false;

        $cancelledAppointment = DB::transaction(function () use ($appointment, &$shouldQueueEmails): Appointment {
            $lockedAppointment = Appointment::query()
                ->with(['professional.user', 'sessionType'])
                ->whereKey($appointment->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($this->isAlreadyCancelled($lockedAppointment)) {
                return $lockedAppointment;
            }

            if (! app(CancellationPolicyService::class)->canCancel($lockedAppointment)) {
                throw new DomainException('Appointment cancellation is not allowed by the current cancellation policy.');
            }

            $lockedAppointment->forceFill([
                'status' => AppointmentStatus::CANCELLED,
            ])->save();

            $shouldQueueEmails = true;

            return $lockedAppointment
                ->refresh()
                ->loadMissing(['professional.user', 'sessionType']);
        });

        if ($shouldQueueEmails) {
            app(QueueAppointmentCancelledEmails::class)->execute($cancelledAppointment);
            app(WhatsAppDeliveryDispatcher::class)->skipPendingForAppointment(
                $cancelledAppointment,
                NotificationEvent::APPOINTMENT_REMINDER,
            );
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
