<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\AppointmentStatus;
use App\Enums\PaymentStatus;
use App\Enums\Role;
use App\Models\Appointment;
use App\Models\Professional;
use App\Models\SessionType;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class AdminAppointmentCalendarTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_appointments_page_renders_calendar_configuration(): void
    {
        $admin = $this->adminUser();

        $this
            ->actingAs($admin)
            ->withCookie('user_timezone', 'Europe/Madrid')
            ->get(route('admin.appointments.index'))
            ->assertOk()
            ->assertSeeText(__('app.admin.appointments.calendar.title'))
            ->assertSee('adminAppointmentsCalendar', false)
            ->assertSee('eventsUrl', false)
            ->assertSee('dayUrl', false)
            ->assertSee('Buenos_Aires', false);
    }

    public function test_admin_calendar_events_endpoint_returns_filtered_range_events(): void
    {
        $admin = $this->adminUser();
        [$professional, $sessionType] = $this->professionalWithSession('Profesional Calendario');
        [$otherProfessional, $otherSessionType] = $this->professionalWithSession('Otro Profesional');

        $included = Appointment::factory()->for($professional)->for($sessionType)->create([
            'patient_name' => 'Cliente Visible',
            'starts_at' => CarbonImmutable::parse('2026-07-24 13:00:00', 'UTC'),
            'ends_at' => CarbonImmutable::parse('2026-07-24 14:00:00', 'UTC'),
            'status' => AppointmentStatus::CONFIRMED,
            'payment_status' => PaymentStatus::PENDING,
        ]);

        Appointment::factory()->for($otherProfessional)->for($otherSessionType)->create([
            'patient_name' => 'Cliente Oculto',
            'starts_at' => CarbonImmutable::parse('2026-07-24 15:00:00', 'UTC'),
            'ends_at' => CarbonImmutable::parse('2026-07-24 16:00:00', 'UTC'),
            'status' => AppointmentStatus::CONFIRMED,
            'payment_status' => PaymentStatus::PENDING,
        ]);

        Appointment::factory()->for($professional)->for($sessionType)->create([
            'patient_name' => 'Cliente Pendiente',
            'starts_at' => CarbonImmutable::parse('2026-07-24 17:00:00', 'UTC'),
            'ends_at' => CarbonImmutable::parse('2026-07-24 18:00:00', 'UTC'),
            'status' => AppointmentStatus::PENDING,
            'payment_status' => PaymentStatus::PENDING,
        ]);

        $this
            ->actingAs($admin)
            ->withCookie('user_timezone', 'Europe/Madrid')
            ->getJson(route('admin.appointments.events', [
                'start' => '2026-07-01',
                'end' => '2026-08-01',
                'professional_id' => $professional->id,
                'status' => AppointmentStatus::CONFIRMED->value,
                'timezone' => 'Europe/Madrid',
            ]))
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.id', (string) $included->id)
            ->assertJsonPath('0.title', 'Profesional Calendario - Cliente Visible')
            ->assertJsonPath('0.extendedProps.local_date', '2026-07-24')
            ->assertJsonPath('0.extendedProps.status', AppointmentStatus::CONFIRMED->value);
    }

    public function test_admin_day_endpoint_returns_all_appointments_for_local_day(): void
    {
        $admin = $this->adminUser();
        [$professional, $sessionType] = $this->professionalWithSession('Profesional Día');

        $previousLocalDay = Appointment::factory()->for($professional)->for($sessionType)->create([
            'patient_name' => 'Cliente Día Anterior',
            'starts_at' => CarbonImmutable::parse('2026-07-24 02:30:00', 'UTC'),
            'ends_at' => CarbonImmutable::parse('2026-07-24 03:00:00', 'UTC'),
            'status' => AppointmentStatus::CONFIRMED,
        ]);

        $morning = Appointment::factory()->for($professional)->for($sessionType)->create([
            'patient_name' => 'Cliente Mañana',
            'patient_email' => 'manana@example.test',
            'patient_timezone' => 'America/Santiago',
            'starts_at' => CarbonImmutable::parse('2026-07-24 13:00:00', 'UTC'),
            'ends_at' => CarbonImmutable::parse('2026-07-24 14:00:00', 'UTC'),
            'status' => AppointmentStatus::CONFIRMED,
            'payment_status' => PaymentStatus::PAID,
            'price' => 25000,
            'currency' => 'ARS',
        ]);

        $late = Appointment::factory()->for($professional)->for($sessionType)->create([
            'patient_name' => 'Cliente Noche',
            'starts_at' => CarbonImmutable::parse('2026-07-24 20:30:00', 'UTC'),
            'ends_at' => CarbonImmutable::parse('2026-07-24 21:00:00', 'UTC'),
            'status' => AppointmentStatus::PENDING,
            'payment_status' => PaymentStatus::PENDING,
        ]);

        $this
            ->actingAs($admin)
            ->withCookie('user_timezone', 'Europe/Madrid')
            ->getJson(route('admin.appointments.day', [
                'date' => '2026-07-24',
                'professional_id' => $professional->id,
                'timezone' => 'Europe/Madrid',
            ]))
            ->assertOk()
            ->assertJsonPath('date', '24/07/2026')
            ->assertJsonCount(2, 'appointments')
            ->assertJsonPath('appointments.0.id', $morning->id)
            ->assertJsonPath('appointments.0.patient_name', 'Cliente Mañana')
            ->assertJsonPath('appointments.0.patient_email', 'manana@example.test')
            ->assertJsonPath('appointments.0.patient_timezone', 'America/Santiago')
            ->assertJsonPath('appointments.0.customer_name', 'Cliente Mañana')
            ->assertJsonPath('appointments.0.customer_email', 'manana@example.test')
            ->assertJsonPath('appointments.0.customer_timezone', 'America/Santiago')
            ->assertJsonPath('appointments.0.professional_name', 'Profesional Día')
            ->assertJsonPath('appointments.0.service', 'Servicio estándar')
            ->assertJsonPath('appointments.0.time_range', '10:00 - 11:00')
            ->assertJsonPath('appointments.0.status_label', __('app.appointment_status.confirmed'))
            ->assertJsonPath('appointments.0.payment_status_label', __('app.payment_status.paid'))
            ->assertJsonPath('appointments.0.price', 'ARS 25,000.00')
            ->assertJsonPath('appointments.1.id', $late->id)
            ->assertJsonMissing([
                'id' => $previousLocalDay->id,
            ]);
    }

    private function adminUser(): User
    {
        return User::factory()->create([
            'role' => Role::ADMIN,
        ]);
    }

    /**
     * @return array{Professional, SessionType}
     */
    private function professionalWithSession(string $name): array
    {
        $user = User::factory()->create([
            'name' => $name,
            'role' => Role::PROFESSIONAL,
        ]);
        $professional = Professional::factory()->for($user)->create();
        $sessionType = SessionType::factory()->for($professional)->create([
            'name' => 'Servicio estándar',
        ]);

        return [$professional, $sessionType];
    }
}
