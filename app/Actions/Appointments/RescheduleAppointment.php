<?php

declare(strict_types=1);

namespace App\Actions\Appointments;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Services\CancellationPolicyService;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;

class RescheduleAppointment
{
    public function execute(
        Appointment $appointment,
        CarbonInterface|string $newStartsAtUtc,
        CarbonInterface|string $newEndsAtUtc,
    ): Appointment|false {
        $newStartsAt = $this->toUtcImmutable($newStartsAtUtc);
        $newEndsAt = $this->toUtcImmutable($newEndsAtUtc);

        if ($newStartsAt->greaterThanOrEqualTo($newEndsAt)) {
            return false;
        }

        $previousStartsAt = null;
        $previousEndsAt = null;

        $rescheduledAppointment = DB::transaction(function () use (
            $appointment,
            $newStartsAt,
            $newEndsAt,
            &$previousStartsAt,
            &$previousEndsAt,
        ): Appointment|false {
            $lockedAppointment = Appointment::query()
                ->with(['therapist.user', 'sessionType'])
                ->whereKey($appointment->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($this->cannotBeRescheduled($lockedAppointment)) {
                return false;
            }

            if (! app(CancellationPolicyService::class)->canReschedule($lockedAppointment)) {
                return false;
            }

            $hasOverlap = Appointment::query()
                ->whereKeyNot($lockedAppointment->id)
                ->overlappingSlot(
                    $lockedAppointment->therapist_id,
                    $newStartsAt,
                    $newEndsAt,
                )
                ->lockForUpdate()
                ->exists();

            if ($hasOverlap) {
                return false;
            }

            $previousStartsAt = $this->toUtcImmutable($lockedAppointment->starts_at);
            $previousEndsAt = $this->toUtcImmutable($lockedAppointment->ends_at);

            $lockedAppointment->forceFill([
                'starts_at' => $newStartsAt,
                'ends_at' => $newEndsAt,
                'reschedule_count' => ((int) $lockedAppointment->reschedule_count) + 1,
            ])->save();

            return $lockedAppointment
                ->refresh()
                ->loadMissing(['therapist.user', 'sessionType']);
        });

        if (! $rescheduledAppointment instanceof Appointment) {
            return false;
        }

        if (! $previousStartsAt instanceof CarbonInterface || ! $previousEndsAt instanceof CarbonInterface) {
            return false;
        }

        app(QueueAppointmentRescheduledEmails::class)->execute(
            appointment: $rescheduledAppointment,
            previousStartsAt: $previousStartsAt,
            previousEndsAt: $previousEndsAt,
        );

        return $rescheduledAppointment;
    }

    private function cannotBeRescheduled(Appointment $appointment): bool
    {
        $status = $appointment->status instanceof AppointmentStatus
            ? $appointment->status->value
            : (string) $appointment->status;

        return in_array($status, [
            AppointmentStatus::CANCELLED->value,
            AppointmentStatus::COMPLETED->value,
        ], true);
    }

    private function toUtcImmutable(CarbonInterface|string $date): CarbonImmutable
    {
        if ($date instanceof CarbonInterface) {
            return CarbonImmutable::instance($date)->utc();
        }

        return CarbonImmutable::parse($date, 'UTC')->utc();
    }
}