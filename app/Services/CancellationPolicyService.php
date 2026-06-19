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
    private const REFUND_DEADLINE_HOURS = 48;

    private const RESCHEDULE_DEADLINE_HOURS = 24;

    private const MAX_RESCHEDULES = 2;

    public function canRefund(Appointment $appointment, ?CarbonInterface $now = null): bool
    {
        return $this->canCancel($appointment, $now)
            && $this->paymentStatusValue($appointment) === PaymentStatus::PAID->value;
    }

    public function canCancel(Appointment $appointment, ?CarbonInterface $now = null): bool
    {
        return $this->isActionable($appointment, $now)
            && $this->startsAtUtc($appointment)->greaterThanOrEqualTo(
                $this->nowUtc($now)->addHours(self::REFUND_DEADLINE_HOURS)
            );
    }

    public function canReschedule(Appointment $appointment, ?CarbonInterface $now = null): bool
    {
        return $this->isActionable($appointment, $now)
            && (int) $appointment->reschedule_count < self::MAX_RESCHEDULES
            && $this->startsAtUtc($appointment)->greaterThanOrEqualTo(
                $this->nowUtc($now)->addHours(self::RESCHEDULE_DEADLINE_HOURS)
            );
    }

    public function maxReschedules(): int
    {
        return self::MAX_RESCHEDULES;
    }

    public function refundDeadlineHours(): int
    {
        return self::REFUND_DEADLINE_HOURS;
    }

    public function rescheduleDeadlineHours(): int
    {
        return self::RESCHEDULE_DEADLINE_HOURS;
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

        if ((int) $appointment->reschedule_count >= self::MAX_RESCHEDULES) {
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