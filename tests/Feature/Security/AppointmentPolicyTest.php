<?php

declare(strict_types=1);

namespace Tests\Feature\Security;

use App\Enums\PaymentStatus;
use App\Enums\Role;
use App\Models\Appointment;
use App\Models\SessionType;
use App\Models\Therapist;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class AppointmentPolicyTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function therapist_can_view_own_appointment(): void
    {
        [$user, $therapist] = $this->createTherapistUser();
        $appointment = $this->createAppointmentFor($therapist);

        $this->assertTrue(Gate::forUser($user)->allows('view', $appointment));
    }

    #[Test]
    public function therapist_cannot_view_another_therapists_appointment(): void
    {
        [$userA] = $this->createTherapistUser();
        [, $therapistB] = $this->createTherapistUser();

        $appointment = $this->createAppointmentFor($therapistB);

        $this->assertFalse(Gate::forUser($userA)->allows('view', $appointment));
    }

    #[Test]
    public function admin_can_view_any_appointment(): void
    {
        $admin = User::factory()->create([
            'role' => Role::ADMIN->value,
        ]);

        [, $therapist] = $this->createTherapistUser();
        $appointment = $this->createAppointmentFor($therapist);

        $this->assertTrue(Gate::forUser($admin)->allows('view', $appointment));
    }

    #[Test]
    public function therapist_cannot_update_payment_for_another_therapists_appointment(): void
    {
        [$userA] = $this->createTherapistUser();
        [, $therapistB] = $this->createTherapistUser();

        $appointment = $this->createAppointmentFor($therapistB);

        $response = $this
            ->actingAs($userA)
            ->patch(route('therapist.appointments.payment.update', $appointment), [
                'payment_status' => PaymentStatus::PAID->value,
            ]);

        $response->assertForbidden();
    }

    /**
     * @return array{0: User, 1: Therapist}
     */
    private function createTherapistUser(): array
    {
        $user = User::factory()->create([
            'role' => Role::THERAPIST->value,
        ]);

        $therapist = Therapist::factory()
            ->for($user)
            ->create();

        return [$user, $therapist];
    }

    private function createAppointmentFor(Therapist $therapist): Appointment
    {
        $sessionType = SessionType::factory()
            ->for($therapist)
            ->create();

        return Appointment::factory()->create([
            'therapist_id' => $therapist->id,
            'session_type_id' => $sessionType->id,
        ]);
    }
}