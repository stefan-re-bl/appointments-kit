<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\Professional;
use App\Models\SessionType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class PublicProfessionalProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_profile_shows_approved_active_professional_information(): void
    {
        $user = User::factory()->create([
            'name' => 'Marina Alvarez',
            'role' => Role::PROFESSIONAL,
        ]);
        $professional = Professional::factory()->for($user)->create([
            'bio' => 'Coordino servicios profesionales online.',
            'specialties' => 'Gestión, soporte y seguimiento.',
            'therapeutic_approach' => 'Trabajo con un proceso claro y ordenado.',
            'timezone' => 'America/Argentina/Buenos_Aires',
            'google_meet_link' => 'https://meet.google.com/abc-defg-hij',
            'is_active' => true,
            'is_approved' => true,
        ]);
        SessionType::factory()->for($professional)->create([
            'name' => 'Servicio inicial',
            'duration_minutes' => 60,
            'price' => 25000,
            'currency' => 'ARS',
            'is_active' => true,
        ]);
        SessionType::factory()->for($professional)->create([
            'name' => 'Servicio inactivo',
            'is_active' => false,
        ]);

        $this
            ->get(route('professionals.show', $professional->slug))
            ->assertOk()
            ->assertSeeText('Marina Alvarez')
            ->assertSeeText('Coordino servicios profesionales online.')
            ->assertSeeText('Gestión, soporte y seguimiento.')
            ->assertSeeText('Trabajo con un proceso claro y ordenado.')
            ->assertSeeText('America/Argentina/Buenos_Aires')
            ->assertDontSeeText('Servicio inicial')
            ->assertDontSeeText('ARS 25,000.00')
            ->assertDontSeeText('Servicio inactivo')
            ->assertDontSeeText(__('app.professional_public.presentation_video_button'))
            ->assertDontSee(route('book.store.professional'), false);
    }

    public function test_public_profile_shows_presentation_video_button_and_modal_when_video_exists(): void
    {
        $user = User::factory()->create([
            'name' => 'Marina Video',
            'role' => Role::PROFESSIONAL,
        ]);
        $professional = Professional::factory()->for($user)->create([
            'presentation_video_url' => '/storage/professionals/presentation-videos/presentation.mp4',
            'google_meet_link' => 'https://meet.google.com/abc-defg-hij',
            'is_active' => true,
            'is_approved' => true,
        ]);

        $this
            ->get(route('professionals.show', $professional->slug))
            ->assertOk()
            ->assertSeeText(__('app.professional_public.presentation_video_button'))
            ->assertSeeText(__('app.professional_public.presentation_video_title', ['name' => 'Marina Video']))
            ->assertSee('/storage/professionals/presentation-videos/presentation.mp4', false);
    }

    public function test_public_professional_directory_shows_profiles_without_booking_flow_references(): void
    {
        $user = User::factory()->create([
            'name' => 'Ana Directoria',
            'role' => Role::PROFESSIONAL,
        ]);
        $professional = Professional::factory()->for($user)->create([
            'bio' => 'Coordino procesos de servicio online.',
            'specialties' => 'Gestión y seguimiento.',
            'therapeutic_approach' => 'Proceso claro y contextual.',
            'presentation_video_url' => '/storage/professionals/presentation-videos/ana.mp4',
            'timezone' => 'America/Argentina/Buenos_Aires',
            'google_meet_link' => 'https://meet.google.com/abc-defg-hij',
            'is_active' => true,
            'is_approved' => true,
        ]);
        SessionType::factory()->for($professional)->create([
            'name' => 'Servicio de orientación',
            'duration_minutes' => 45,
            'price' => 18000,
            'currency' => 'ARS',
            'is_active' => true,
        ]);
        SessionType::factory()->for($professional)->create([
            'name' => 'Servicio oculto',
            'is_active' => false,
        ]);

        $inactiveUser = User::factory()->create([
            'name' => 'Perfil No Visible',
            'role' => Role::PROFESSIONAL,
        ]);
        Professional::factory()->for($inactiveUser)->create([
            'is_active' => false,
            'is_approved' => true,
            'google_meet_link' => 'https://meet.google.com/hidden',
        ]);

        $this
            ->get(route('professionals.index'))
            ->assertOk()
            ->assertSeeText(__('app.professional_public.directory.title'))
            ->assertSeeText('Ana Directoria')
            ->assertSeeText('Coordino procesos de servicio online.')
            ->assertSeeText('Gestión y seguimiento.')
            ->assertSeeText('Proceso claro y contextual.')
            ->assertSeeText('America/Argentina/Buenos_Aires')
            ->assertSee('presentation-videos\\/ana.mp4', false)
            ->assertDontSeeText('Servicio de orientación')
            ->assertDontSeeText('ARS 18,000.00')
            ->assertDontSeeText('Servicio oculto')
            ->assertDontSeeText('Perfil No Visible')
            ->assertDontSee(route('book.store.professional'), false)
            ->assertDontSee('professional_id', false);
    }

    public function test_home_view_professionals_cta_points_to_public_directory(): void
    {
        $this
            ->get(route('home'))
            ->assertOk()
            ->assertSee(route('professionals.index'), false);
    }

    public function test_unavailable_professional_profile_returns_friendly_404(): void
    {
        $user = User::factory()->create([
            'name' => 'Perfil Pendiente',
            'role' => Role::PROFESSIONAL,
        ]);
        $professional = Professional::factory()->for($user)->create([
            'is_active' => true,
            'is_approved' => false,
        ]);

        $this
            ->get(route('professionals.show', $professional->slug))
            ->assertNotFound()
            ->assertSeeText(__('app.professional_public.not_found.title'))
            ->assertSeeText(__('app.professional_public.not_found.cta'));
    }

    public function test_professional_without_meeting_link_has_no_public_profile(): void
    {
        $professional = Professional::factory()->create([
            'google_meet_link' => null,
            'is_active' => true,
            'is_approved' => true,
        ]);

        $this
            ->get(route('professionals.show', $professional->slug))
            ->assertNotFound();
    }

    public function test_public_directory_shows_available_professionals(): void
    {
        $user = User::factory()->create([
            'name' => 'Profesional Visible',
            'role' => Role::PROFESSIONAL,
        ]);
        $professional = Professional::factory()->for($user)->create([
            'google_meet_link' => 'https://meet.google.com/abc-defg-hij',
            'is_active' => true,
            'is_approved' => true,
        ]);

        $this
            ->get(route('professionals.index'))
            ->assertOk()
            ->assertSeeText('Profesional Visible');
    }

    public function test_public_professional_routes_use_generic_slugs_and_keep_legacy_redirects(): void
    {
        $professional = Professional::factory()->create([
            'google_meet_link' => 'https://meet.google.com/abc-defg-hij',
            'is_active' => true,
            'is_approved' => true,
        ]);

        $this->assertStringEndsWith('/professionals', route('professionals.index'));
        $this->assertStringContainsString('/professionals/', route('professionals.show', $professional->slug));

        $this->get('/therapists')
            ->assertRedirect('/professionals');

        $this->get('/therapists/'.$professional->slug)
            ->assertRedirect(route('professionals.show', $professional->slug));
    }

    public function test_professional_can_start_internal_booking_with_own_profile(): void
    {
        $professional = Professional::factory()->create([
            'google_meet_link' => 'https://meet.google.com/abc-defg-hij',
            'is_active' => true,
            'is_approved' => true,
        ]);
        SessionType::factory()->for($professional)->create([
            'is_active' => true,
        ]);

        $this
            ->actingAs($professional->user)
            ->get(route('book.index'))
            ->assertRedirect(route('book.date'))
            ->assertSessionHas('booking.professional_id', $professional->id);
    }

    public function test_slugs_are_generated_uniquely_from_user_name(): void
    {
        $firstUser = User::factory()->create([
            'name' => 'Nombre Repetido',
            'role' => Role::PROFESSIONAL,
        ]);
        $secondUser = User::factory()->create([
            'name' => 'Nombre Repetido',
            'role' => Role::PROFESSIONAL,
        ]);

        $firstProfessional = Professional::factory()->for($firstUser)->create();
        $secondProfessional = Professional::factory()->for($secondUser)->create();

        $this->assertSame('nombre-repetido', $firstProfessional->slug);
        $this->assertSame('nombre-repetido-2', $secondProfessional->slug);
    }
}
