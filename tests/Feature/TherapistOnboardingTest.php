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
            ->assertSeeText(__('app.dashboard_onboarding.flow_steps.7.title'))
            ->assertSeeText(__('app.dashboard_onboarding.checklist.profile'))
            ->assertSee(route('profile.edit'), false)
            ->assertSee(route('availabilities.index'), false)
            ->assertSee(route('therapist.appointments.index'), false)
            ->assertSee(route('book.index'), false);
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
            'timezone' => 'America/Argentina/Buenos_Aires',
        ]);

        $this
            ->actingAs($user)
            ->patch(route('profile.update'), [
                'name' => 'Terapeuta Perfil',
                'email' => 'therapist-profile@example.test',
                'bio' => 'Bio actualizada para pacientes.',
                'google_meet_link' => 'https://meet.google.com/abc-defg-hij',
                'avatar_url' => 'https://example.test/avatar.jpg',
                'therapist_country' => 'AR',
                'therapist_timezone' => 'America/Argentina/Buenos_Aires',
            ])
            ->assertRedirect(route('profile.edit'));

        $this->assertDatabaseHas('therapists', [
            'user_id' => $user->id,
            'bio' => 'Bio actualizada para pacientes.',
            'google_meet_link' => 'https://meet.google.com/abc-defg-hij',
            'avatar_url' => 'https://example.test/avatar.jpg',
        ]);
    }

    public function test_guest_is_redirected_from_booking(): void
    {
        $this
            ->get(route('book.index'))
            ->assertRedirect(route('home'));
    }
}
