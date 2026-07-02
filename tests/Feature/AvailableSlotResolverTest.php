<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\SessionType;
use App\Models\Therapist;
use App\Models\User;
use App\Services\AvailableSlotResolver;
use App\Services\TimezoneService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class AvailableSlotResolverTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-07-01 12:00:00', 'UTC'));
        app()->instance('user.timezone', 'America/Argentina/Buenos_Aires');
    }

    protected function tearDown(): void
    {
        CarbonImmutable::setTestNow();

        parent::tearDown();
    }

    public function test_it_resolves_a_valid_available_slot_preserving_utc_boundaries(): void
    {
        [$therapist, $sessionType] = $this->makeBookableTherapist();

        $slot = app(AvailableSlotResolver::class)->resolve(
            $therapist,
            '2026-07-06',
            (int) $sessionType->duration_minutes,
            '2026-07-06T12:00:00+00:00',
        );

        $this->assertIsArray($slot);
        $this->assertSame('2026-07-06T12:00:00+00:00', $slot['start_utc']);
        $this->assertSame('2026-07-06T13:00:00+00:00', $slot['end_utc']);
        $this->assertSame('09:00', $slot['label']);
    }

    public function test_it_uses_explicit_display_timezone_without_reading_global_context(): void
    {
        app()->instance('user.timezone', 'UTC');

        [$therapist, $sessionType] = $this->makeBookableTherapist();

        $slot = app(AvailableSlotResolver::class)->resolve(
            $therapist,
            '2026-07-06',
            (int) $sessionType->duration_minutes,
            '2026-07-06T12:00:00+00:00',
            'America/New_York',
        );

        $this->assertIsArray($slot);
        $this->assertSame('2026-07-06T12:00:00+00:00', $slot['start_utc']);
        $this->assertSame('08:00', $slot['label']);
    }

    public function test_it_returns_null_for_invalid_or_unavailable_selected_slots(): void
    {
        [$therapist, $sessionType] = $this->makeBookableTherapist();

        $resolver = app(AvailableSlotResolver::class);

        $this->assertNull($resolver->resolve(
            $therapist,
            '2026-07-06',
            (int) $sessionType->duration_minutes,
            '2026-07-06T18:00:00+00:00',
        ));

        $this->assertNull($resolver->resolve(
            $therapist,
            '2026-07-06',
            (int) $sessionType->duration_minutes,
            'not-a-date',
        ));
    }

    public function test_it_does_not_resolve_slots_outside_therapist_availability(): void
    {
        [$therapist, $sessionType] = $this->makeBookableTherapist();

        $slot = app(AvailableSlotResolver::class)->resolve(
            $therapist,
            '2026-07-06',
            (int) $sessionType->duration_minutes,
            '2026-07-06T15:00:00+00:00',
        );

        $this->assertNull($slot);
    }

    /**
     * @return array{0: Therapist, 1: SessionType}
     */
    private function makeBookableTherapist(): array
    {
        $user = User::factory()->create([
            'role' => Role::THERAPIST,
        ]);

        $therapist = Therapist::factory()
            ->for($user)
            ->create([
                'timezone' => 'America/Argentina/Buenos_Aires',
                'is_active' => true,
                'is_approved' => true,
            ]);

        $sessionType = SessionType::factory()
            ->for($therapist)
            ->create([
                'duration_minutes' => 60,
                'is_active' => true,
            ]);

        $timezoneService = app(TimezoneService::class);

        $therapist->availabilities()->create([
            'day_of_week' => 1,
            'start_time' => $timezoneService->timeToUtc(
                '09:00',
                'America/Argentina/Buenos_Aires',
                1,
            ),
            'end_time' => $timezoneService->timeToUtc(
                '12:00',
                'America/Argentina/Buenos_Aires',
                1,
            ),
            'is_active' => true,
        ]);

        return [$therapist, $sessionType];
    }
}
