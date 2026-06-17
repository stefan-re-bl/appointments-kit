<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CleanupExpiredAppointmentsCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_cancels_expired_pending_appointments(): void
    {
        $expiredPendingAppointment = Appointment::factory()->create([
            'status' => AppointmentStatus::PENDING,
            'created_at' => CarbonImmutable::now('UTC')->subMinutes(16),
            'updated_at' => CarbonImmutable::now('UTC')->subMinutes(16),
        ]);

        $this->artisan('appointments:cleanup-expired')
            ->expectsOutput('Expired pending appointments cancelled: 1')
            ->assertSuccessful();

        $this->assertDatabaseHas('appointments', [
            'id' => $expiredPendingAppointment->id,
            'status' => AppointmentStatus::CANCELLED->value,
        ]);
    }

    public function test_it_does_not_cancel_recent_pending_appointments(): void
    {
        $recentPendingAppointment = Appointment::factory()->create([
            'status' => AppointmentStatus::PENDING,
            'created_at' => CarbonImmutable::now('UTC')->subMinutes(10),
            'updated_at' => CarbonImmutable::now('UTC')->subMinutes(10),
        ]);

        $this->artisan('appointments:cleanup-expired')
            ->expectsOutput('Expired pending appointments cancelled: 0')
            ->assertSuccessful();

        $this->assertDatabaseHas('appointments', [
            'id' => $recentPendingAppointment->id,
            'status' => AppointmentStatus::PENDING->value,
        ]);
    }

    public function test_it_does_not_cancel_confirmed_appointments_even_if_old(): void
    {
        $confirmedAppointment = Appointment::factory()->create([
            'status' => AppointmentStatus::CONFIRMED,
            'created_at' => CarbonImmutable::now('UTC')->subMinutes(30),
            'updated_at' => CarbonImmutable::now('UTC')->subMinutes(30),
        ]);

        $this->artisan('appointments:cleanup-expired')
            ->expectsOutput('Expired pending appointments cancelled: 0')
            ->assertSuccessful();

        $this->assertDatabaseHas('appointments', [
            'id' => $confirmedAppointment->id,
            'status' => AppointmentStatus::CONFIRMED->value,
        ]);
    }

    public function test_dry_run_counts_without_cancelling(): void
    {
        $expiredPendingAppointment = Appointment::factory()->create([
            'status' => AppointmentStatus::PENDING,
            'created_at' => CarbonImmutable::now('UTC')->subMinutes(20),
            'updated_at' => CarbonImmutable::now('UTC')->subMinutes(20),
        ]);

        $this->artisan('appointments:cleanup-expired --dry-run')
            ->expectsOutput('Expired pending appointments found: 1')
            ->assertSuccessful();

        $this->assertDatabaseHas('appointments', [
            'id' => $expiredPendingAppointment->id,
            'status' => AppointmentStatus::PENDING->value,
        ]);
    }
}