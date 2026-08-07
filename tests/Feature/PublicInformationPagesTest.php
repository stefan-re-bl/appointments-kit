<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

final class PublicInformationPagesTest extends TestCase
{
    public function test_public_information_pages_render_in_spanish(): void
    {
        $pages = [
            'information.how-it-works' => 'De la orientación al acompañamiento online',
            'information.faq' => 'Respuestas antes de empezar',
            'information.patients' => 'Qué necesitás saber antes de tu sesión',
            'information.payment-and-cancellation' => 'Coordinación clara antes de comenzar',
        ];

        foreach ($pages as $routeName => $heading) {
            $this->get(route($routeName, ['lang' => 'es']))
                ->assertOk()
                ->assertSeeText($heading)
                ->assertSee('https://wa.me/5491150501775', false);
        }
    }

    public function test_payment_page_matches_the_current_cancellation_policy(): void
    {
        $this->get(route('information.payment-and-cancellation', ['lang' => 'es']))
            ->assertOk()
            ->assertSeeText('Cancelación con derecho a reembolso')
            ->assertSeeText('La cancelación del turno con derecho a reembolso puede solicitarse hasta 24 horas antes del horario acordado.')
            ->assertSeeText('Modificación de fecha u horario')
            ->assertSeeText('La modificación de fecha u horario puede solicitarse hasta 48 horas antes del turno.')
            ->assertSeeText('Cualquier excepción a estas condiciones quedará sujeta al acuerdo previo entre paciente y terapeuta.');
    }

    public function test_information_pages_render_in_english(): void
    {
        $this->get(route('information.patients', ['lang' => 'en']))
            ->assertOk()
            ->assertSeeText('What to know before your session')
            ->assertSeeText('Not an emergency service')
            ->assertSeeText('0800-999-0091')
            ->assertSeeText('0800-333-1665');

        $this->get(route('information.payment-and-cancellation', ['lang' => 'en']))
            ->assertOk()
            ->assertSeeText('Schedules')
            ->assertSeeText('Cancellation with refund eligibility')
            ->assertSeeText('Date or time changes');
    }

    public function test_public_navigation_links_to_all_information_pages(): void
    {
        $this->get(route('home', ['lang' => 'es']))
            ->assertOk()
            ->assertSee(route('information.how-it-works'), false)
            ->assertSee(route('information.faq'), false)
            ->assertSee(route('information.patients'), false)
            ->assertSee(route('information.payment-and-cancellation'), false);
    }
}
