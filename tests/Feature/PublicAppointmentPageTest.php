<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\AppointmentStatus;
use App\Enums\PaymentStatus;
use App\Models\Appointment;
use App\Models\Professional;
use App\Models\SessionType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

final class PublicAppointmentPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_patient_can_view_public_appointment_page_with_patient_timezone(): void
    {
        $professional = Professional::factory()->create([
            'google_meet_link' => 'https://meet.google.com/test-room',
            'payment_instructions' => 'Transferencia al alias de prueba.',
        ]);

        $sessionType = SessionType::factory()
            ->for($professional)
            ->create([
                'name' => 'Sesión individual',
                'duration_minutes' => 60,
                'price' => 10000,
                'currency' => 'ARS',
            ]);

        $appointment = Appointment::factory()
            ->for($professional)
            ->for($sessionType, 'sessionType')
            ->create([
                'patient_name' => 'Cliente Demo',
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
            ->assertSeeText('Cliente Demo')
            ->assertSeeText('10/07/2026 15:00')
            ->assertSeeText('10/07/2026 16:00')
            ->assertSeeText('America/Argentina/Cordoba')
            ->assertSeeText('Transferencia al alias de prueba.')
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
        $professional = Professional::factory()->create();

        $sessionType = SessionType::factory()
            ->for($professional)
            ->create();

        $appointment = Appointment::factory()
            ->for($professional)
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
