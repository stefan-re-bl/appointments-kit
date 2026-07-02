<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Availability;
use App\Models\Therapist;
use Illuminate\Support\Collection;

final readonly class AvailabilityService
{
    public function __construct(
        private TimezoneService $timezoneService,
    ) {}

    /**
     * @return Collection<int, Collection<int, Availability>>
     */
    public function groupedLocalAvailabilities(Therapist $therapist): Collection
    {
        $timezone = $therapist->timezone;

        return $therapist->availabilities()
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get()
            ->groupBy('day_of_week')
            ->map(function (Collection $group) use ($timezone): Collection {
                return $group->map(function (Availability $availability) use ($timezone): Availability {
                    $availability->start_time_local = $this->timezoneService->timeToLocal(
                        $availability->start_time,
                        $timezone,
                        $availability->day_of_week,
                    );

                    $availability->end_time_local = $this->timezoneService->timeToLocal(
                        $availability->end_time,
                        $timezone,
                        $availability->day_of_week,
                    );

                    return $availability;
                });
            });
    }

    /**
     * @param  array<int, array{start_time: string, end_time: string, is_active?: mixed}>  $slots
     */
    public function hasOverlaps(Therapist $therapist, int $dayOfWeek, array $slots): bool
    {
        $allSlots = array_merge(
            $this->existingLocalSlots($therapist, $dayOfWeek),
            $this->requestedLocalSlots($slots),
        );

        return $this->slotsOverlap($allSlots);
    }

    /**
     * @param  array<int, array{start_time: string, end_time: string, is_active?: mixed}>  $slots
     */
    public function createMany(Therapist $therapist, int $dayOfWeek, array $slots): void
    {
        $timezone = $therapist->timezone;

        foreach ($slots as $slot) {
            $therapist->availabilities()->create([
                'day_of_week' => $dayOfWeek,
                'start_time' => $this->timezoneService->timeToUtc($slot['start_time'], $timezone, $dayOfWeek),
                'end_time' => $this->timezoneService->timeToUtc($slot['end_time'], $timezone, $dayOfWeek),
                'is_active' => filter_var($slot['is_active'] ?? false, FILTER_VALIDATE_BOOLEAN),
            ]);
        }
    }

    /**
     * @return array<int, array{start: string, end: string}>
     */
    private function existingLocalSlots(Therapist $therapist, int $dayOfWeek): array
    {
        $timezone = $therapist->timezone;

        return $therapist->availabilities()
            ->where('day_of_week', $dayOfWeek)
            ->get()
            ->map(fn (Availability $availability): array => [
                'start' => $this->timezoneService->timeToLocal($availability->start_time, $timezone, $dayOfWeek),
                'end' => $this->timezoneService->timeToLocal($availability->end_time, $timezone, $dayOfWeek),
            ])
            ->toArray();
    }

    /**
     * @param  array<int, array{start_time: string, end_time: string, is_active?: mixed}>  $slots
     * @return array<int, array{start: string, end: string}>
     */
    private function requestedLocalSlots(array $slots): array
    {
        return array_map(
            fn (array $slot): array => [
                'start' => $slot['start_time'],
                'end' => $slot['end_time'],
            ],
            $slots,
        );
    }

    /**
     * @param  array<int, array{start: string, end: string}>  $slots
     */
    private function slotsOverlap(array $slots): bool
    {
        usort($slots, fn (array $a, array $b): int => strcmp($a['start'], $b['start']));

        for ($i = 1; $i < count($slots); $i++) {
            if ($slots[$i]['start'] < $slots[$i - 1]['end']) {
                return true;
            }
        }

        return false;
    }
}
