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

    /**
     * @param array<string, mixed> $appointmentOverrides
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