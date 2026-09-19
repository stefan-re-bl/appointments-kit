<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\AppointmentStatus;
use App\Enums\PaymentStatus;
use App\Models\Appointment;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;

final class CancellationPolicyService
{
    public function canRefund(Appointment $appointment, ?CarbonInterface $now = null): bool
    {
        return $this->isActionable($appointment, $now)
            && $this->startsAtUtc($appointment)->greaterThanOrEqualTo(
                $this->nowUtc($now)->addHours($this->refundDeadlineHours())
            )
            && $this->paymentStatusValue($appointment) === PaymentStatus::PAID->value;
    }

    public function canCancel(Appointment $appointment, ?CarbonInterface $now = null): bool
    {
        return $this->isActionable($appointment, $now)
            && $this->startsAtUtc($appointment)->greaterThanOrEqualTo(
                $this->nowUtc($now)->addHours($this->cancelDeadlineHours())
            );
    }

    public function canReschedule(Appointment $appointment, ?CarbonInterface $now = null): bool
    {
        return $this->isActionable($appointment, $now)
            && (int) $appointment->reschedule_count < $this->maxReschedules()
            && $this->startsAtUtc($appointment)->greaterThanOrEqualTo(
                $this->nowUtc($now)->addHours($this->rescheduleDeadlineHours())
            );
    }

    public function cancelDeadlineHours(): int
    {
        return max(0, (int) config('booking.policies.cancellation_notice_hours', 24));
    }

    public function maxReschedules(): int
    {
        return max(0, (int) config('booking.policies.max_reschedules', 2));
    }

    public function refundDeadlineHours(): int
    {
        return max(0, (int) config('booking.policies.refund_notice_hours', 24));
    }

    public function rescheduleDeadlineHours(): int
    {
        return max(0, (int) config('booking.policies.reschedule_notice_hours', 48));
    }

    public function messageKey(Appointment $appointment, ?CarbonInterface $now = null): string
    {
        if (! $this->isActionable($appointment, $now)) {
            return 'appointment_policy.messages.not_actionable';
        }

        if ($this->canRefund($appointment, $now)) {
            return 'appointment_policy.messages.refund_available';
        }

        if ($this->canCancel($appointment, $now)) {
            return 'appointment_policy.messages.cancel_without_refund';
        }

        if ((int) $appointment->reschedule_count >= $this->maxReschedules()) {
            return 'appointment_policy.messages.reschedule_limit_reached';
        }

        if ($this->canReschedule($appointment, $now)) {
            return 'appointment_policy.messages.reschedule_only';
        }

        return 'appointment_policy.messages.too_late';
    }

    private function isActionable(Appointment $appointment, ?CarbonInterface $now = null): bool
    {
        $status = $this->statusValue($appointment);

        if (! in_array($status, [
            AppointmentStatus::PENDING->value,
            AppointmentStatus::CONFIRMED->value,
        ], true)) {
            return false;
        }

        return $this->startsAtUtc($appointment)->greaterThan($this->nowUtc($now));
    }

    private function startsAtUtc(Appointment $appointment): CarbonImmutable
    {
        $startsAt = $appointment->starts_at;

        if ($startsAt instanceof CarbonInterface) {
            return CarbonImmutable::instance($startsAt->toDateTimeImmutable())->setTimezone('UTC');
        }

        return CarbonImmutable::parse((string) $startsAt, 'UTC');
    }

    private function nowUtc(?CarbonInterface $now = null): CarbonImmutable
    {
        if ($now instanceof CarbonInterface) {
            return CarbonImmutable::instance($now->toDateTimeImmutable())->setTimezone('UTC');
        }

        return CarbonImmutable::now('UTC');
    }

    private function statusValue(Appointment $appointment): string
    {
        if ($appointment->status instanceof AppointmentStatus) {
            return $appointment->status->value;
        }

        return (string) $appointment->status;
    }

    private function paymentStatusValue(Appointment $appointment): string
    {
        if ($appointment->payment_status instanceof PaymentStatus) {
            return $appointment->payment_status->value;
        }

        return (string) $appointment->payment_status;
    }
}
