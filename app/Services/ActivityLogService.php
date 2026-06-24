<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Appointment;
use BackedEnum;
use Carbon\CarbonImmutable;
use DateTimeInterface;
use Illuminate\Contracts\Auth\Authenticatable;

class ActivityLogService
{
    public function recordAppointmentStatusChange(
        Appointment $appointment,
        BackedEnum|string|null $from,
        BackedEnum|string|null $to,
        ?Authenticatable $causer = null,
        array $metadata = [],
    ): ActivityLog {
        return $this->createAppointmentLog(
            appointment: $appointment,
            event: ActivityLog::EVENT_APPOINTMENT_STATUS_CHANGED,
            oldValues: [
                'status' => $this->enumValue($from),
            ],
            newValues: [
                'status' => $this->enumValue($to),
            ],
            causer: $causer,
            metadata: $metadata,
        );
    }

    public function recordAppointmentPaymentUpdate(
        Appointment $appointment,
        BackedEnum|string|null $paymentStatusFrom,
        BackedEnum|string|null $paymentStatusTo,
        mixed $paidAtFrom,
        mixed $paidAtTo,
        ?Authenticatable $causer = null,
        array $metadata = [],
    ): ActivityLog {
        return $this->createAppointmentLog(
            appointment: $appointment,
            event: ActivityLog::EVENT_APPOINTMENT_PAYMENT_UPDATED,
            oldValues: [
                'payment_status' => $this->enumValue($paymentStatusFrom),
                'paid_at' => $this->dateValue($paidAtFrom),
            ],
            newValues: [
                'payment_status' => $this->enumValue($paymentStatusTo),
                'paid_at' => $this->dateValue($paidAtTo),
            ],
            causer: $causer,
            metadata: $metadata,
        );
    }

    private function createAppointmentLog(
        Appointment $appointment,
        string $event,
        array $oldValues,
        array $newValues,
        ?Authenticatable $causer = null,
        array $metadata = [],
    ): ActivityLog {
        $request = request();

        return ActivityLog::query()->create([
            'appointment_id' => $appointment->id,
            'causer_type' => $causer === null ? null : $causer::class,
            'causer_id' => $causer?->getAuthIdentifier(),
            'event' => $event,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'metadata' => array_merge([
                'source' => app()->runningInConsole() ? 'console' : 'web',
                'route' => $request->route()?->getName(),
            ], $metadata),
            'ip_address' => app()->runningInConsole() ? null : $request->ip(),
            'user_agent' => app()->runningInConsole() ? null : $request->userAgent(),
        ]);
    }

    private function enumValue(BackedEnum|string|null $value): ?string
    {
        if ($value instanceof BackedEnum) {
            return (string) $value->value;
        }

        return $value === null ? null : (string) $value;
    }

    private function dateValue(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ($value instanceof DateTimeInterface) {
            return CarbonImmutable::instance($value)->utc()->toDateTimeString();
        }

        return CarbonImmutable::parse((string) $value, 'UTC')->utc()->toDateTimeString();
    }
}