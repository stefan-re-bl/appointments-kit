<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\SessionType;
use App\Models\Therapist;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class PublicLegalPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_legal_pages_render_and_are_linked_from_public_footer(): void
    {
        $pages = [
            'legal.index' => 'Información legal de Umbralia',
            'legal.terms' => 'Términos y condiciones',
            'legal.privacy' => 'Política de privacidad',
            'legal.emergency-notice' => 'Aviso ante emergencias',
        ];

        foreach ($pages as $routeName => $heading) {
            $this
                ->get(route($routeName, ['lang' => 'es']))
                ->assertOk()
                ->assertSeeText($heading)
                ->assertSeeText('Umbralia no atiende emergencias')
                ->assertSeeText('no procesa pagos');
        }

        $this
            ->get(route('home', ['lang' => 'es']))
            ->assertOk()
            ->assertSee(route('legal.terms'), false)
            ->assertSee(route('legal.privacy'), false)
            ->assertSee(route('legal.emergency-notice'), false);
    }

    public function test_booking_confirmation_shows_required_legal_acceptance_and_emergency_notice(): void
    {
        [$therapist, $sessionType] = $this->makeBookableTherapist();

        $this
            ->withSession([
                'booking.therapist_id' => $therapist->id,
                'booking.session_type_id' => $sessionType->id,
                'booking.date' => '2026-07-06',
                'booking.starts_at_utc' => '2026-07-06T12:00:00+00:00',
                'booking.patient_timezone' => 'America/Argentina/Buenos_Aires',
            ])
            ->get(route('book.confirm'))
            ->assertOk()
            ->assertSee('name="accepted_terms"', false)
            ->assertSee('name="accepted_email_communications"', false)
            ->assertSee(route('legal.terms'), false)
            ->assertSee(route('legal.privacy'), false)
            ->assertSee(route('legal.emergency-notice'), false)
            ->assertSeeText(__('legal.booking.emergency_title'));
    }

    /**
     * @return array{0: Therapist, 1: SessionType}
     */
    private function makeBookableTherapist(): array
    {
        $user = User::factory()->create([
            'role' => Role::THERAPIST,
        ]);

        $therapist = Therapist::factory()->for($user)->create([
            'timezone' => 'America/Argentina/Buenos_Aires',
            'google_meet_link' => 'https://meet.google.com/abc-defg-hij',
            'is_active' => true,
            'is_approved' => true,
        ]);

        $sessionType = SessionType::factory()->for($therapist)->create([
            'duration_minutes' => 60,
            'is_active' => true,
        ]);

        return [$therapist, $sessionType];
    }
}
