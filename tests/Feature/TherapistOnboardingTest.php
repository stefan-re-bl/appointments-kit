<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\SessionType;
use App\Models\Therapist;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class TherapistOnboardingTest extends TestCase
{
    use RefreshDatabase;

    public function test_pending_therapist_sees_onboarding_status_flow_checklist_and_links(): void
    {
        $user = User::factory()->create([
            'role' => Role::THERAPIST,
        ]);
        Therapist::factory()->for($user)->create([
            'bio' => null,
            'google_meet_link' => null,
            'is_active' => true,
            'is_approved' => false,
        ]);

        $this
            ->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSeeText(__('app.dashboard_onboarding.status.pending'))
            ->assertSeeText(__('app.dashboard_onboarding.flow_steps.0.title'))
            ->assertSeeText(__('app.dashboard_onboarding.flow_steps.8.title'))
            ->assertSeeText(__('app.dashboard_onboarding.checklist.profile'))
            ->assertSee(route('profile.edit'), false)
            ->assertSee(route('session-types.index'), false)
            ->assertSee(route('availabilities.index'), false)
            ->assertSee(route('therapist.appointments.index'), false);
    }

    public function test_dashboard_marks_completed_setup_items(): void
    {
        $user = User::factory()->create([
            'role' => Role::THERAPIST,
        ]);
        $therapist = Therapist::factory()->for($user)->create([
            'bio' => 'Trabajo con adultos en modalidad online.',
            'google_meet_link' => 'https://meet.google.com/abc-defg-hij',
            'is_active' => true,
            'is_approved' => true,
        ]);
        SessionType::factory()->for($therapist)->create([
            'is_active' => true,
        ]);
        $therapist->availabilities()->create([
            'day_of_week' => 1,
            'start_time' => '12:00',
            'end_time' => '15:00',
            'is_active' => true,
        ]);

        $this
            ->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSeeText(__('app.dashboard_onboarding.status.approved'))
            ->assertSeeText(__('app.dashboard_onboarding.complete_icon'));
    }

    public function test_profile_update_saves_therapist_bio_and_meeting_link(): void
    {
        $user = User::factory()->create([
            'name' => 'Terapeuta Perfil',
            'email' => 'therapist-profile@example.test',
            'role' => Role::THERAPIST,
        ]);
        Therapist::factory()->for($user)->create([
            'bio' => null,
            'google_meet_link' => null,
        ]);

        $this
            ->actingAs($user)
            ->patch(route('profile.update'), [
                'name' => 'Terapeuta Perfil',
                'email' => 'therapist-profile@example.test',
                'bio' => 'Bio actualizada para pacientes.',
                'google_meet_link' => 'https://meet.google.com/abc-defg-hij',
                'avatar_url' => 'https://example.test/avatar.jpg',
            ])
            ->assertRedirect(route('profile.edit'));

        $this->assertDatabaseHas('therapists', [
            'user_id' => $user->id,
            'bio' => 'Bio actualizada para pacientes.',
            'google_meet_link' => 'https://meet.google.com/abc-defg-hij',
            'avatar_url' => 'https://example.test/avatar.jpg',
        ]);
    }

    public function test_therapist_without_meeting_link_is_not_publicly_bookable(): void
    {
        $visibleUser = User::factory()->create([
            'name' => 'Terapeuta Con Link',
            'role' => Role::THERAPIST,
        ]);
        $hiddenUser = User::factory()->create([
            'name' => 'Terapeuta Sin Link',
            'role' => Role::THERAPIST,
        ]);

        Therapist::factory()->for($visibleUser)->create([
            'google_meet_link' => 'https://meet.google.com/abc-defg-hij',
            'is_active' => true,
            'is_approved' => true,
        ]);
        Therapist::factory()->for($hiddenUser)->create([
            'google_meet_link' => null,
            'is_active' => true,
            'is_approved' => true,
        ]);

        $this
            ->get(route('book.index'))
            ->assertOk()
            ->assertSeeText('Terapeuta Con Link')
            ->assertDontSeeText('Terapeuta Sin Link');
    }
}
