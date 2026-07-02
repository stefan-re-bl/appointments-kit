<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\Therapist;
use App\Models\User;
use App\Services\TimezoneService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class AvailabilityManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_therapist_can_create_valid_availability_with_utc_storage(): void
    {
        [$user] = $this->makeTherapist();

        $this
            ->actingAs($user)
            ->post(route('availabilities.store'), [
                'day_of_week' => 1,
                'slots' => [
                    [
                        'start_time' => '09:00',
                        'end_time' => '10:00',
                        'is_active' => '1',
                    ],
                ],
            ])
            ->assertRedirect(route('availabilities.index'))
            ->assertSessionHas('status', __('app.availability.saved'));

        $this->assertDatabaseHas('availabilities', [
            'day_of_week' => 1,
            'start_time' => '12:00:00',
            'end_time' => '13:00:00',
            'is_active' => true,
        ]);
    }

    public function test_therapist_can_create_inactive_availability_from_hidden_checkbox_value(): void
    {
        [$user] = $this->makeTherapist();

        $this
            ->actingAs($user)
            ->post(route('availabilities.store'), [
                'day_of_week' => 1,
                'slots' => [
                    [
                        'start_time' => '10:00',
                        'end_time' => '11:00',
                        'is_active' => '0',
                    ],
                ],
            ])
            ->assertRedirect(route('availabilities.index'));

        $this->assertDatabaseHas('availabilities', [
            'day_of_week' => 1,
            'start_time' => '13:00:00',
            'end_time' => '14:00:00',
            'is_active' => false,
        ]);
    }

    public function test_therapist_cannot_create_overlapping_availability(): void
    {
        [$user, $therapist] = $this->makeTherapist();

        $timezoneService = app(TimezoneService::class);

        $therapist->availabilities()->create([
            'day_of_week' => 1,
            'start_time' => $timezoneService->timeToUtc('09:00', 'America/Argentina/Buenos_Aires', 1),
            'end_time' => $timezoneService->timeToUtc('10:00', 'America/Argentina/Buenos_Aires', 1),
            'is_active' => true,
        ]);

        $this
            ->actingAs($user)
            ->from(route('availabilities.create'))
            ->post(route('availabilities.store'), [
                'day_of_week' => 1,
                'slots' => [
                    [
                        'start_time' => '09:30',
                        'end_time' => '10:30',
                        'is_active' => '1',
                    ],
                ],
            ])
            ->assertRedirect(route('availabilities.create'))
            ->assertSessionHasErrors('slots');

        $this->assertDatabaseCount('availabilities', 1);
    }

    public function test_availability_index_displays_local_times(): void
    {
        [$user, $therapist] = $this->makeTherapist();

        $timezoneService = app(TimezoneService::class);

        $therapist->availabilities()->create([
            'day_of_week' => 1,
            'start_time' => $timezoneService->timeToUtc('09:00', 'America/Argentina/Buenos_Aires', 1),
            'end_time' => $timezoneService->timeToUtc('10:00', 'America/Argentina/Buenos_Aires', 1),
            'is_active' => true,
        ]);

        $this
            ->actingAs($user)
            ->get(route('availabilities.index'))
            ->assertOk()
            ->assertSeeText('09:00 - 10:00');
    }

    public function test_therapist_cannot_delete_another_therapists_availability(): void
    {
        [, $ownerTherapist] = $this->makeTherapist();
        [$otherUser] = $this->makeTherapist();

        $availability = $ownerTherapist->availabilities()->create([
            'day_of_week' => 1,
            'start_time' => '12:00:00',
            'end_time' => '13:00:00',
            'is_active' => true,
        ]);

        $this
            ->actingAs($otherUser)
            ->delete(route('availabilities.destroy', $availability))
            ->assertForbidden();

        $this->assertDatabaseHas('availabilities', [
            'id' => $availability->id,
        ]);
    }

    /**
     * @return array{0: User, 1: Therapist}
     */
    private function makeTherapist(): array
    {
        $user = User::factory()->create([
            'role' => Role::THERAPIST,
        ]);

        $therapist = Therapist::factory()
            ->for($user)
            ->create([
                'timezone' => 'America/Argentina/Buenos_Aires',
            ]);

        return [$user, $therapist];
    }
}
