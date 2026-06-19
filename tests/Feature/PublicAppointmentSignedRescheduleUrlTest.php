<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Appointment;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

final class PublicAppointmentSignedRescheduleUrlTest extends TestCase
{
    use RefreshDatabase;

    public function test_signed_reschedule_url_redirects_to_public_appointment_page(): void
    {
        $appointment = Appointment::factory()->create();

        $url = URL::signedRoute(
            'appointments.public.reschedule',
            ['token' => $appointment->token],
            Carbon::now('UTC')->addHours(24),
        );

        $this
            ->get($url)
            ->assertRedirect(route('appointments.public.show', ['token' => $appointment->token]));
    }

    public function test_tampered_signed_reschedule_url_returns_403(): void
    {
        $appointment = Appointment::factory()->create();

        $url = URL::signedRoute(
            'appointments.public.reschedule',
            ['token' => $appointment->token],
            Carbon::now('UTC')->addHours(24),
        );

        $tamperedUrl = str_replace($appointment->token, 'invalid-token', $url);

        $this
            ->get($tamperedUrl)
            ->assertForbidden();
    }

    public function test_expired_signed_reschedule_url_returns_403(): void
    {
        $now = Carbon::parse('2026-01-01 12:00:00', 'UTC');

        Carbon::setTestNow($now);

        $appointment = Appointment::factory()->create();

        $url = URL::signedRoute(
            'appointments.public.reschedule',
            ['token' => $appointment->token],
            $now->copy()->addHours(24),
        );

        Carbon::setTestNow($now->copy()->addHours(25));

        $this
            ->get($url)
            ->assertForbidden();

        Carbon::setTestNow();
    }
}