<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\Professional;
use App\Models\SessionType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class PublicLegalPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_legal_pages_render_and_are_linked_from_public_footer(): void
    {
        $pages = [
            'legal.index' => 'Información legal de Appointments Kit',
            'legal.terms' => 'Términos y condiciones',
            'legal.privacy' => 'Política de privacidad',
            'legal.emergency-notice' => 'Aviso ante emergencias',
        ];

        foreach ($pages as $routeName => $heading) {
            $this
                ->get(route($routeName, ['lang' => 'es']))
                ->assertOk()
                ->assertSeeText($heading)
                ->assertSeeText('Legal')
                ->assertSeeText('no procesa pagos');
        }

        $this
            ->get(route('home', ['lang' => 'es']))
            ->assertOk()
            ->assertSee(route('legal.terms'), false)
            ->assertSee(route('legal.privacy'), false)
            ->assertSee(route('legal.emergency-notice'), false);
    }

    public function test_internal_booking_confirmation_does_not_show_public_legal_acceptance(): void
    {
        [$professional, $sessionType] = $this->makeBookableProfessional();

        $this
            ->actingAs($professional->user)
            ->withSession([
                'booking.professional_id' => $professional->id,
                'booking.session_type_id' => $sessionType->id,
                'booking.date' => '2026-07-06',
                'booking.starts_at_utc' => '2026-07-06T12:00:00+00:00',
                'booking.patient_timezone' => 'America/Argentina/Buenos_Aires',
            ])
            ->get(route('book.confirm'))
            ->assertOk()
            ->assertSeeText(__('app.booking_internal.patient_details'))
            ->assertDontSee('name="accepted_terms"', false)
            ->assertDontSee('name="accepted_email_communications"', false)
            ->assertDontSeeText(__('legal.booking.emergency_title'));
    }

    /**
     * @return array{0: Professional, 1: SessionType}
     */
    private function makeBookableProfessional(): array
    {
        $user = User::factory()->create([
            'role' => Role::PROFESSIONAL,
        ]);

        $professional = Professional::factory()->for($user)->create([
            'timezone' => 'America/Argentina/Buenos_Aires',
            'google_meet_link' => 'https://meet.google.com/abc-defg-hij',
            'is_active' => true,
            'is_approved' => true,
        ]);

        $sessionType = SessionType::factory()->for($professional)->create([
            'duration_minutes' => 60,
            'is_active' => true,
        ]);

        return [$professional, $sessionType];
    }
}
