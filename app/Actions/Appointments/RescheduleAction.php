<?php

declare(strict_types=1);

namespace App\Actions\Appointments;

use App\Models\Appointment;
use App\Services\SlotGenerationService;
use Carbon\Carbon;
use Throwable;

final readonly class RescheduleAction
{
    public function __construct(
        private SlotGenerationService $slotGenerationService,
        private RescheduleAppointment $rescheduleAppointment,
    ) {
    }

    public function execute(Appointment $appointment, string $date, string $startUtc): Appointment|false
    {
        $appointment->loadMissing(['therapist.user', 'sessionType']);

        $selectedSlot = $this->findSelectedSlot($appointment, $date, $startUtc);

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

    /**
     * @return array{start_utc: mixed, end_utc: mixed, label?: mixed}|null
     */
    private function findSelectedSlot(Appointment $appointment, string $date, string $startUtc): ?array
    {
        $slots = $this->slotGenerationService->generate(
            $appointment->therapist,
            $date,
            (int) $appointment->sessionType->duration_minutes,
        );

        foreach ($slots as $slot) {
            if (! is_array($slot)) {
                continue;
            }

            if (! array_key_exists('start_utc', $slot) || ! array_key_exists('end_utc', $slot)) {
                continue;
            }

            if ($this->sameUtcInstant((string) $slot['start_utc'], $startUtc)) {
                return $slot;
            }
        }

        return null;
    }

    private function sameUtcInstant(string $left, string $right): bool
    {
        try {
            return Carbon::parse($left, 'UTC')->utc()
                ->equalTo(Carbon::parse($right, 'UTC')->utc());
        } catch (Throwable) {
            return false;
        }
    }
}