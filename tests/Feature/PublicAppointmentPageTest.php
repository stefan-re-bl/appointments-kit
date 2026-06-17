<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\AppointmentStatus;
use App\Enums\PaymentStatus;
use App\Models\Appointment;
use App\Models\SessionType;
use App\Models\Therapist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

final class PublicAppointmentPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_patient_can_view_public_appointment_page_with_patient_timezone(): void
    {
        $therapist = Therapist::factory()->create([
            'google_meet_link' => 'https://meet.google.com/test-room',
        ]);

        $sessionType = SessionType::factory()
            ->for($therapist)
            ->create([
                'name' => 'Sesión individual',
                'duration_minutes' => 60,
                'price' => 10000,
                'currency' => 'ARS',
            ]);

        $appointment = Appointment::factory()
            ->for($therapist)
            ->for($sessionType, 'sessionType')
            ->create([
                'patient_name' => 'Paciente Demo',
                'patient_email' => 'paciente@example.com',
                'patient_timezone' => 'America/Argentina/Cordoba',
                'starts_at' => Carbon::parse('2026-07-10 18:00:00', 'UTC'),
                'ends_at' => Carbon::parse('2026-07-10 19:00:00', 'UTC'),
                'status' => AppointmentStatus::CONFIRMED,
                'payment_status' => PaymentStatus::PENDING,
                'paid_at' => null,
            ]);

        $this->get(route('appointments.public.show', ['token' => $appointment->token]))
            ->assertOk()
            ->assertSeeText('Paciente Demo')
            ->assertSeeText('10/07/2026 15:00')
            ->assertSeeText('10/07/2026 16:00')
            ->assertSeeText('America/Argentina/Cordoba')
            ->assertSee('https://meet.google.com/test-room');
    }

    public function test_invalid_token_returns_friendly_404(): void
    {
        $this->get(route('appointments.public.show', ['token' => 'invalid-token']))
            ->assertNotFound()
            ->assertSeeText(__('app.appointment_public.not_found_title'));
    }

    public function test_payment_status_reflects_paid_appointment(): void
    {
        $therapist = Therapist::factory()->create();

        $sessionType = SessionType::factory()
            ->for($therapist)
            ->create();

        $appointment = Appointment::factory()
            ->for($therapist)
            ->for($sessionType, 'sessionType')
            ->create([
                'patient_timezone' => 'America/Argentina/Cordoba',
                'starts_at' => Carbon::parse('2026-07-10 18:00:00', 'UTC'),
                'ends_at' => Carbon::parse('2026-07-10 19:00:00', 'UTC'),
                'status' => AppointmentStatus::CONFIRMED,
                'payment_status' => PaymentStatus::PAID,
                'paid_at' => Carbon::parse('2026-07-09 15:30:00', 'UTC'),
            ]);

        $this->get(route('appointments.public.show', ['token' => $appointment->token]))
            ->assertOk()
            ->assertSeeText(__('app.appointment_public.payment.paid'))
            ->assertSeeText('09/07/2026 12:30');
    }
}