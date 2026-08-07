<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\AppointmentStatus;
use App\Enums\PaymentStatus;
use App\Enums\Role;
use App\Models\Appointment;
use App\Models\SessionType;
use App\Models\Therapist;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TherapistAppointmentPaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_therapist_can_mark_own_appointment_as_paid(): void
    {
        [$user, $appointment] = $this->createAppointmentForTherapist();

        $response = $this
            ->actingAs($user)
            ->patch(route('therapist.appointments.payment.update', $appointment), [
                'payment_status' => PaymentStatus::PAID->value,
            ]);

        $response->assertRedirect();

        $appointment->refresh();

        $this->assertSame(PaymentStatus::PAID, $appointment->payment_status);
        $this->assertNotNull($appointment->paid_at);
    }

    public function test_paid_at_is_cleared_when_payment_is_marked_as_waived(): void
    {
        [$user, $appointment] = $this->createAppointmentForTherapist([
            'payment_status' => PaymentStatus::PAID,
            'paid_at' => CarbonImmutable::now('UTC')->subHour(),
        ]);

        $response = $this
            ->actingAs($user)
            ->patch(route('therapist.appointments.payment.update', $appointment), [
                'payment_status' => PaymentStatus::WAIVED->value,
            ]);

        $response->assertRedirect();

        $appointment->refresh();

        $this->assertSame(PaymentStatus::WAIVED, $appointment->payment_status);
        $this->assertNull($appointment->paid_at);
    }

    public function test_therapist_cannot_update_another_therapists_appointment_payment(): void
    {
        [$user] = $this->createAppointmentForTherapist();
        [, $otherAppointment] = $this->createAppointmentForTherapist();

        $response = $this
            ->actingAs($user)
            ->patch(route('therapist.appointments.payment.update', $otherAppointment), [
                'payment_status' => PaymentStatus::PAID->value,
            ]);

        $response->assertForbidden();
    }

    public function test_patient_can_see_updated_payment_status_on_public_appointment_page(): void
    {
        [, $appointment] = $this->createAppointmentForTherapist([
            'payment_status' => PaymentStatus::PAID,
            'paid_at' => CarbonImmutable::now('UTC'),
        ]);

        $response = $this->get(route('appointments.public.show', $appointment->token));

        $response->assertOk();
        $response->assertSee(__('app.payments.status.paid'));
    }

    public function test_therapist_appointments_page_renders_calendar_configuration(): void
    {
        [$user] = $this->createAppointmentForTherapist();

        $this
            ->actingAs($user)
            ->get(route('therapist.appointments.index'))
            ->assertOk()
            ->assertSeeText(__('app.appointments.management.calendar.title'))
            ->assertSee('adminAppointmentsCalendar', false)
            ->assertSee('eventsUrl', false)
            ->assertSee('dayUrl', false)
            ->assertSee('Buenos_Aires', false);
    }

    public function test_therapist_calendar_events_endpoint_returns_only_own_events(): void
    {
        [$user, $appointment] = $this->createAppointmentForTherapist([
            'patient_name' => 'Paciente Propio',
            'starts_at' => CarbonImmutable::parse('2026-07-24 13:00:00', 'UTC'),
            'ends_at' => CarbonImmutable::parse('2026-07-24 14:00:00', 'UTC'),
            'status' => AppointmentStatus::CONFIRMED,
            'payment_status' => PaymentStatus::PENDING,
        ]);
        [, $otherAppointment] = $this->createAppointmentForTherapist([
            'patient_name' => 'Paciente Ajeno',
            'starts_at' => CarbonImmutable::parse('2026-07-24 15:00:00', 'UTC'),
            'ends_at' => CarbonImmutable::parse('2026-07-24 16:00:00', 'UTC'),
            'status' => AppointmentStatus::CONFIRMED,
            'payment_status' => PaymentStatus::PENDING,
        ]);

        Appointment::factory()
            ->for($appointment->therapist)
            ->for($appointment->sessionType, 'sessionType')
            ->create([
                'patient_name' => 'Paciente Pendiente',
                'starts_at' => CarbonImmutable::parse('2026-07-24 17:00:00', 'UTC'),
                'ends_at' => CarbonImmutable::parse('2026-07-24 18:00:00', 'UTC'),
                'status' => AppointmentStatus::PENDING,
                'payment_status' => PaymentStatus::PENDING,
            ]);

        $this
            ->actingAs($user)
            ->getJson(route('therapist.appointments.events', [
                'start' => '2026-07-01',
                'end' => '2026-08-01',
                'status' => AppointmentStatus::CONFIRMED->value,
            ]))
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.id', (string) $appointment->id)
            ->assertJsonPath('0.title', 'Paciente Propio')
            ->assertJsonPath('0.extendedProps.local_date', '2026-07-24')
            ->assertJsonMissing([
                'id' => (string) $otherAppointment->id,
            ]);
    }

    public function test_therapist_day_endpoint_returns_only_own_appointments_for_local_day(): void
    {
        [$user, $appointment] = $this->createAppointmentForTherapist([
            'patient_name' => 'Paciente Mañana',
            'patient_email' => 'manana@example.test',
            'patient_timezone' => 'America/Santiago',
            'starts_at' => CarbonImmutable::parse('2026-07-24 13:00:00', 'UTC'),
            'ends_at' => CarbonImmutable::parse('2026-07-24 14:00:00', 'UTC'),
            'status' => AppointmentStatus::CONFIRMED,
            'payment_status' => PaymentStatus::PAID,
            'price' => 25000,
            'currency' => 'ARS',
        ]);
        [, $otherAppointment] = $this->createAppointmentForTherapist([
            'patient_name' => 'Paciente Ajeno',
            'starts_at' => CarbonImmutable::parse('2026-07-24 15:00:00', 'UTC'),
            'ends_at' => CarbonImmutable::parse('2026-07-24 16:00:00', 'UTC'),
        ]);
        $previousLocalDay = Appointment::factory()
            ->for($appointment->therapist)
            ->for($appointment->sessionType, 'sessionType')
            ->create([
                'patient_name' => 'Paciente Día Anterior',
                'starts_at' => CarbonImmutable::parse('2026-07-24 02:30:00', 'UTC'),
                'ends_at' => CarbonImmutable::parse('2026-07-24 03:00:00', 'UTC'),
                'status' => AppointmentStatus::CONFIRMED,
            ]);

        $this
            ->actingAs($user)
            ->getJson(route('therapist.appointments.day', [
                'date' => '2026-07-24',
            ]))
            ->assertOk()
            ->assertJsonPath('date', '24/07/2026')
            ->assertJsonCount(1, 'appointments')
            ->assertJsonPath('appointments.0.id', $appointment->id)
            ->assertJsonPath('appointments.0.patient_name', 'Paciente Mañana')
            ->assertJsonPath('appointments.0.patient_email', 'manana@example.test')
            ->assertJsonPath('appointments.0.patient_timezone', 'America/Santiago')
            ->assertJsonPath('appointments.0.time_range', '10:00 - 11:00')
            ->assertJsonPath('appointments.0.status_label', __('app.appointment_status.confirmed'))
            ->assertJsonPath('appointments.0.payment_status_label', __('app.payment_status.paid'))
            ->assertJsonMissingPath('appointments.0.session_type')
            ->assertJsonMissingPath('appointments.0.price')
            ->assertJsonMissing([
                'id' => $otherAppointment->id,
            ])
            ->assertJsonMissing([
                'id' => $previousLocalDay->id,
            ]);
    }

    /**
     * @param  array<string, mixed>  $appointmentOverrides
     * @return array{0: User, 1: Appointment}
     */
    private function createAppointmentForTherapist(array $appointmentOverrides = []): array
    {
        $user = User::factory()->create([
            'role' => Role::THERAPIST,
            'email_verified_at' => CarbonImmutable::now('UTC'),
        ]);

        $therapist = Therapist::factory()
            ->for($user)
            ->create([
                'timezone' => 'America/Argentina/Buenos_Aires',
            ]);

        $sessionType = SessionType::factory()
            ->for($therapist)
            ->create();

        $startsAt = CarbonImmutable::now('UTC')->addDays(5)->setTime(15, 0);
        $endsAt = $startsAt->addMinutes((int) $sessionType->duration_minutes);

        $appointment = Appointment::factory()
            ->for($therapist)
            ->for($sessionType, 'sessionType')
            ->create(array_merge([
                'patient_name' => 'Paciente Test',
                'patient_email' => 'paciente@example.com',
                'patient_timezone' => 'America/Argentina/Buenos_Aires',
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
                'status' => AppointmentStatus::CONFIRMED,
                'payment_status' => PaymentStatus::PENDING,
                'paid_at' => null,
            ], $appointmentOverrides));

        return [$user, $appointment];
    }
}
