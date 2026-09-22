<?php

declare(strict_types=1);

namespace App\Actions\Appointments;

use App\Models\Appointment;
use App\Services\AvailableSlotResolver;
use Carbon\Carbon;

final readonly class RescheduleAction
{
    public function __construct(
        private AvailableSlotResolver $availableSlotResolver,
        private RescheduleAppointment $rescheduleAppointment,
    ) {}

    public function execute(Appointment $appointment, string $date, string $startUtc): Appointment|false
    {
        $appointment->loadMissing(['professional.user', 'service']);

        $selectedSlot = $this->availableSlotResolver->resolve(
            $appointment->professional,
            $date,
            (int) $appointment->service->duration_minutes,
            $startUtc,
            $appointment->customer_timezone ?: 'UTC',
        );

        if ($selectedSlot === null) {
            return false;
        }

        $newStartsAt = Carbon::parse((string) $selectedSlot['start_utc'], 'UTC')->utc();
        $newEndsAt = Carbon::parse((string) $selectedSlot['end_utc'], 'UTC')->utc();

        return $this->rescheduleAppointment->execute(
            $appointment,
            $newStartsAt,
            $newEndsAt,
        );
    }
}
