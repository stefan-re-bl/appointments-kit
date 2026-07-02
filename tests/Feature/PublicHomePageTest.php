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
            ->assertSeeText('Umbralia')
            ->assertSeeText(__('app.home.hero.title'))
            ->assertSeeText(__('app.home.how.title'))
            ->assertSeeText(__('app.home.benefits.title'))
            ->assertSeeText(__('app.home.faq.title'))
            ->assertSeeText(__('app.home.emergency.title'))
            ->assertSee(route('book.index'), false)
            ->assertSee('images/umbralia-home-hero-indigo.png', false);
    }

    public function test_public_home_can_be_rendered_in_english(): void
    {
        $response = $this->get(route('home', ['lang' => 'en']));

        $response
            ->assertOk()
            ->assertSeeText('A professional space to talk, understand and move forward.')
            ->assertSeeText('Umbralia is not an emergency service.')
            ->assertSeeText('Book a session');
    }
}
