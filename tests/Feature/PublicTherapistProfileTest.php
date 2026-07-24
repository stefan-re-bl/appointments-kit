<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\SessionType;
use App\Models\Therapist;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class PublicTherapistProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_profile_shows_approved_active_therapist_information(): void
    {
        $user = User::factory()->create([
            'name' => 'Marina Alvarez',
            'role' => Role::THERAPIST,
        ]);
        $therapist = Therapist::factory()->for($user)->create([
            'bio' => 'Acompaño procesos terapéuticos online.',
            'specialties' => 'Ansiedad, autoestima y duelos.',
            'therapeutic_approach' => 'Trabajo con enfoque integrativo.',
            'timezone' => 'America/Argentina/Buenos_Aires',
            'google_meet_link' => 'https://meet.google.com/abc-defg-hij',
            'is_active' => true,
            'is_approved' => true,
        ]);
        SessionType::factory()->for($therapist)->create([
            'name' => 'Consulta inicial',
            'duration_minutes' => 60,
            'price' => 25000,
            'currency' => 'ARS',
            'is_active' => true,
        ]);
        SessionType::factory()->for($therapist)->create([
            'name' => 'Sesión inactiva',
            'is_active' => false,
        ]);

        $this
            ->get(route('therapists.show', $therapist->slug))
            ->assertOk()
            ->assertSeeText('Marina Alvarez')
            ->assertSeeText('Acompaño procesos terapéuticos online.')
            ->assertSeeText('Ansiedad, autoestima y duelos.')
            ->assertSeeText('Trabajo con enfoque integrativo.')
            ->assertSeeText('America/Argentina/Buenos_Aires')
            ->assertSeeText('Consulta inicial')
            ->assertSeeText('ARS 25,000.00')
            ->assertDontSeeText('Sesión inactiva')
            ->assertDontSeeText(__('app.therapist_public.presentation_video_button'))
            ->assertSee(route('book.store.therapist'), false);
    }

    public function test_public_profile_shows_presentation_video_button_and_modal_when_video_exists(): void
    {
        $user = User::factory()->create([
            'name' => 'Marina Video',
            'role' => Role::THERAPIST,
        ]);
        $therapist = Therapist::factory()->for($user)->create([
            'presentation_video_url' => '/storage/therapists/presentation-videos/presentation.mp4',
            'google_meet_link' => 'https://meet.google.com/abc-defg-hij',
            'is_active' => true,
            'is_approved' => true,
        ]);

        $this
            ->get(route('therapists.show', $therapist->slug))
            ->assertOk()
            ->assertSeeText(__('app.therapist_public.presentation_video_button'))
            ->assertSeeText(__('app.therapist_public.presentation_video_title', ['name' => 'Marina Video']))
            ->assertSee('/storage/therapists/presentation-videos/presentation.mp4', false);
    }

    public function test_unavailable_therapist_profile_returns_friendly_404(): void
    {
        $user = User::factory()->create([
            'name' => 'Perfil Pendiente',
            'role' => Role::THERAPIST,
        ]);
        $therapist = Therapist::factory()->for($user)->create([
            'is_active' => true,
            'is_approved' => false,
        ]);

        $this
            ->get(route('therapists.show', $therapist->slug))
            ->assertNotFound()
            ->assertSeeText(__('app.therapist_public.not_found.title'))
            ->assertSeeText(__('app.therapist_public.not_found.cta'));
    }

    public function test_therapist_without_meeting_link_has_no_public_profile(): void
    {
        $therapist = Therapist::factory()->create([
            'google_meet_link' => null,
            'is_active' => true,
            'is_approved' => true,
        ]);

        $this
            ->get(route('therapists.show', $therapist->slug))
            ->assertNotFound();
    }

    public function test_booking_list_links_to_public_profiles(): void
    {
        $user = User::factory()->create([
            'name' => 'Terapeuta Visible',
            'role' => Role::THERAPIST,
        ]);
        $therapist = Therapist::factory()->for($user)->create([
            'google_meet_link' => 'https://meet.google.com/abc-defg-hij',
            'is_active' => true,
            'is_approved' => true,
        ]);

        $this
            ->get(route('book.index'))
            ->assertOk()
            ->assertSee(route('therapists.show', $therapist->slug), false)
            ->assertSeeText(__('app.therapist_public.view_profile'));
    }

    public function test_public_profile_can_start_booking_with_preselected_therapist(): void
    {
        $therapist = Therapist::factory()->create([
            'google_meet_link' => 'https://meet.google.com/abc-defg-hij',
            'is_active' => true,
            'is_approved' => true,
        ]);

        $this
            ->from(route('therapists.show', $therapist->slug))
            ->post(route('book.store.therapist'), [
                'therapist_id' => $therapist->id,
            ])
            ->assertRedirect(route('book.session'))
            ->assertSessionHas('booking.therapist_id', $therapist->id);
    }

    public function test_slugs_are_generated_uniquely_from_user_name(): void
    {
        $firstUser = User::factory()->create([
            'name' => 'Nombre Repetido',
            'role' => Role::THERAPIST,
        ]);
        $secondUser = User::factory()->create([
            'name' => 'Nombre Repetido',
            'role' => Role::THERAPIST,
        ]);

        $firstTherapist = Therapist::factory()->for($firstUser)->create();
        $secondTherapist = Therapist::factory()->for($secondUser)->create();

        $this->assertSame('nombre-repetido', $firstTherapist->slug);
        $this->assertSame('nombre-repetido-2', $secondTherapist->slug);
    }
}
