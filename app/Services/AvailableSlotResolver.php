<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Therapist;
use Carbon\Carbon;
use Throwable;

final readonly class AvailableSlotResolver
{
    public function __construct(
        private SlotGenerationService $slotGenerationService,
    ) {}

    /**
     * @return array{start_utc: mixed, end_utc: mixed, label?: mixed}|null
     */
    public function resolve(
        Therapist $therapist,
        string $date,
        int $durationMinutes,
        string $selectedStartUtc,
        ?string $displayTimezone = null,
    ): ?array {
        $slots = $this->slotGenerationService->generate(
            $therapist,
            $date,
            $durationMinutes,
            $displayTimezone,
        );

        foreach ($slots as $slot) {
            if (! is_array($slot)) {
                continue;
            }

            if (! array_key_exists('start_utc', $slot) || ! array_key_exists('end_utc', $slot)) {
                continue;
            }

            if ($this->sameUtcInstant((string) $slot['start_utc'], $selectedStartUtc)) {
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
