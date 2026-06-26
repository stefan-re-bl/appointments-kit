<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

final class PublicInformationPagesTest extends TestCase
{
    public function test_public_information_pages_render_in_spanish(): void
    {
        $pages = [
            'information.how-it-works' => 'De la elección de terapeuta a tu sesión online',
            'information.faq' => 'Respuestas antes de reservar',
            'information.patients' => 'Qué necesitás saber antes de tu sesión',
            'information.payment-and-cancellation' => 'Reglas claras para gestionar tu cita',
        ];

        foreach ($pages as $routeName => $heading) {
            $this->get(route($routeName, ['lang' => 'es']))
                ->assertOk()
                ->assertSeeText($heading)
                ->assertSee(route('book.index'), false);
        }
    }

    public function test_payment_page_matches_the_current_cancellation_policy(): void
    {
        $this->get(route('information.payment-and-cancellation', ['lang' => 'es']))
            ->assertOk()
            ->assertSeeText('Cancelación con 48 horas')
            ->assertSeeText('Reprogramación con 24 horas')
            ->assertSeeText('Cada cita admite como máximo dos reprogramaciones.')
            ->assertSeeText('Las devoluciones no son automáticas');
    }

    public function test_information_pages_render_in_english(): void
    {
        $this->get(route('information.patients', ['lang' => 'en']))
            ->assertOk()
            ->assertSeeText('What to know before your session')
            ->assertSeeText('Not an emergency service');

        $this->get(route('information.payment-and-cancellation', ['lang' => 'en']))
            ->assertOk()
            ->assertSeeText('Cancellation with 48 hours')
            ->assertSeeText('Rescheduling with 24 hours');
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
