<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Professional;
use App\Models\SessionType;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

final class PublicAppointmentSignedRescheduleUrlTest extends TestCase
{
    use RefreshDatabase;

    public function test_signed_reschedule_url_shows_reschedule_page(): void
    {
        $appointment = Appointment::factory()->create();

        $url = URL::signedRoute(
            'appointments.public.reschedule',
            ['token' => $appointment->token],
            Carbon::now('UTC')->addHours(24),
        );

        $this
            ->get($url)
            ->assertOk()
            ->assertViewIs('appointments.public.reschedule')
            ->assertViewHas('appointment', function (Appointment $viewAppointment) use ($appointment): bool {
                return $viewAppointment->is($appointment);
            })
            ->assertViewHas('canReschedule');
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

    public function test_signed_reschedule_slots_endpoint_returns_slots_payload(): void
    {
        $professional = Professional::factory()->create([
            'timezone' => 'UTC',
        ]);
        $sessionType = SessionType::factory()->for($professional)->create([
            'duration_minutes' => 60,
        ]);
        $appointment = Appointment::factory()
            ->for($professional)
            ->for($sessionType, 'sessionType')
            ->create([
                'starts_at' => Carbon::now('UTC')->addWeek(),
                'ends_at' => Carbon::now('UTC')->addWeek()->addHour(),
                'patient_timezone' => 'UTC',
            ]);

        $url = URL::signedRoute(
            'appointments.public.reschedule.slots',
            [
                'token' => $appointment->token,
                'date' => Carbon::now('UTC')->addWeek()->toDateString(),
                'timezone' => 'UTC',
            ],
            Carbon::now('UTC')->addHours(24),
        );

        $this
            ->getJson($url)
            ->assertOk()
            ->assertJson([]);
    }

    public function test_unsigned_reschedule_slots_endpoint_returns_403(): void
    {
        $appointment = Appointment::factory()->create();

        $this
            ->getJson(route('appointments.public.reschedule.slots', [
                'token' => $appointment->token,
                'date' => Carbon::now('UTC')->addWeek()->toDateString(),
                'timezone' => 'UTC',
            ]))
            ->assertForbidden();
    }
}
