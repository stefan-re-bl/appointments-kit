<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

final class PublicHomePageTest extends TestCase
{
    public function test_public_home_contains_primary_sections_and_booking_cta(): void
    {
        $response = $this->get(route('home', ['lang' => 'es']));

        $response
            ->assertOk()
            ->assertSeeText('Appointments Kit')
            ->assertSeeText(__('app.home.hero.title'))
            ->assertSeeText(__('app.home.how.title'))
            ->assertSeeText(__('app.home.benefits.title'))
            ->assertSeeText(__('app.home.faq.title'))
            ->assertSeeText(__('app.home.emergency.title'))
            ->assertSeeText('Reservar cita')
            ->assertSee('images/client-home-hero.jpg', false);
    }

    public function test_public_home_uses_configured_branding_and_terminology(): void
    {
        config([
            'branding.name' => 'Demo Services',
            'branding.logo' => 'images/demo-logo.png',
            'branding.images.home_hero' => 'images/demo-hero.jpg',
            'branding.contact.whatsapp_number' => '5491111111111',
            'terminology.provider.plural' => 'especialistas',
            'terminology.customer.plural' => 'usuarios',
        ]);

        $response = $this->get(route('home', ['lang' => 'es']));

        $response
            ->assertOk()
            ->assertSeeText('Demo Services')
            ->assertSeeText('Usuarios')
            ->assertSeeText('Especialistas')
            ->assertSee('images/demo-logo.png', false)
            ->assertSee('images/demo-hero.jpg', false)
            ->assertSee('https://wa.me/5491111111111', false)
            ->assertDontSee('https://wa.me/5491150501775', false);
    }

    public function test_public_home_respects_public_feature_flags(): void
    {
        config([
            'features.public_information_pages' => false,
            'features.public_faq' => false,
            'features.public_contact_form' => false,
            'features.public_provider_directory' => false,
            'features.intro_video' => false,
        ]);

        $response = $this->get(route('home', ['lang' => 'es']));

        $response
            ->assertOk()
            ->assertDontSee(route('information.how-it-works'), false)
            ->assertDontSee(route('information.faq'), false)
            ->assertDontSee(route('information.patients'), false)
            ->assertDontSee(route('contact.create'), false)
            ->assertDontSee(route('professionals.index'), false)
            ->assertDontSee('videos/client-intro.mp4', false);
    }

    public function test_public_home_can_be_rendered_in_english(): void
    {
        $response = $this->get(route('home', ['lang' => 'en']));

        $response
            ->assertOk()
            ->assertSeeText('Professional scheduling with clear confirmed appointments.')
            ->assertSeeText('Service depends on availability.')
            ->assertSeeText('Book appointment');
    }
}
