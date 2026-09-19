<?php

declare(strict_types=1);

namespace Tests\Feature\Security;

use App\Enums\PaymentStatus;
use App\Enums\Role;
use App\Models\Appointment;
use App\Models\Professional;
use App\Models\SessionType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class AppointmentPolicyTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function professional_can_view_own_appointment(): void
    {
        [$user, $professional] = $this->createProfessionalUser();
        $appointment = $this->createAppointmentFor($professional);

        $this->assertTrue(Gate::forUser($user)->allows('view', $appointment));
    }

    #[Test]
    public function professional_cannot_view_another_professionals_appointment(): void
    {
        [$userA] = $this->createProfessionalUser();
        [, $professionalB] = $this->createProfessionalUser();

        $appointment = $this->createAppointmentFor($professionalB);

        $this->assertFalse(Gate::forUser($userA)->allows('view', $appointment));
    }

    #[Test]
    public function admin_can_view_any_appointment(): void
    {
        $admin = User::factory()->create([
            'role' => Role::ADMIN->value,
        ]);

        [, $professional] = $this->createProfessionalUser();
        $appointment = $this->createAppointmentFor($professional);

        $this->assertTrue(Gate::forUser($admin)->allows('view', $appointment));
    }

    #[Test]
    public function professional_cannot_update_payment_for_another_professionals_appointment(): void
    {
        [$userA] = $this->createProfessionalUser();
        [, $professionalB] = $this->createProfessionalUser();

        $appointment = $this->createAppointmentFor($professionalB);

        $response = $this
            ->actingAs($userA)
            ->patch(route('professional.appointments.payment.update', $appointment), [
                'payment_status' => PaymentStatus::PAID->value,
            ]);

        $response->assertForbidden();
    }

    /**
     * @return array{0: User, 1: Professional}
     */
    private function createProfessionalUser(): array
    {
        $user = User::factory()->create([
            'role' => Role::PROFESSIONAL->value,
        ]);

        $professional = Professional::factory()
            ->for($user)
            ->create();

        return [$user, $professional];
    }

    private function createAppointmentFor(Professional $professional): Appointment
    {
        $sessionType = SessionType::factory()
            ->for($professional)
            ->create();

        return Appointment::factory()->create([
            'professional_id' => $professional->id,
            'session_type_id' => $sessionType->id,
        ]);
    }
}
