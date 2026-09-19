<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\AppointmentStatus;
use App\Enums\PaymentStatus;
use App\Models\Appointment;
use App\Models\Professional;
use App\Models\SessionType;
use App\Models\User;
use Database\Seeders\DemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class DemoSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_seeder_creates_fictional_demo_services_data(): void
    {
        $this->seed(DemoSeeder::class);

        $this->assertDatabaseHas('users', [
            'name' => 'Admin Demo Services',
            'email' => 'admin@demo.test',
        ]);

        foreach (['Ana Martínez', 'Lucas Fernández', 'Sofía Gómez'] as $providerName) {
            $this->assertDatabaseHas('users', [
                'name' => $providerName,
            ]);
        }

        foreach (['Servicio inicial', 'Servicio estándar', 'Servicio extendido'] as $serviceName) {
            $this->assertDatabaseHas('session_types', [
                'name' => $serviceName,
            ]);
        }

        $this->assertSame(3, Professional::query()->count());
        $this->assertSame(9, SessionType::query()->count());
        $this->assertSame(12, Appointment::query()->count());

        $this->assertDatabaseHas('appointments', ['status' => AppointmentStatus::CONFIRMED->value]);
        $this->assertDatabaseHas('appointments', ['status' => AppointmentStatus::COMPLETED->value]);
        $this->assertDatabaseHas('appointments', ['status' => AppointmentStatus::CANCELLED->value]);
        $this->assertDatabaseHas('appointments', ['payment_status' => PaymentStatus::PENDING->value]);
        $this->assertDatabaseHas('appointments', ['payment_status' => PaymentStatus::PAID->value]);
        $this->assertDatabaseHas('appointments', ['payment_status' => PaymentStatus::WAIVED->value]);
        $this->assertDatabaseHas('appointments', ['reschedule_count' => 1]);
        $this->assertDatabaseHas('appointments', ['reschedule_count' => 2]);

        $this->assertSame(0, User::query()->where('email', 'like', '%@legacy-brand.test')->count());
    }
}
