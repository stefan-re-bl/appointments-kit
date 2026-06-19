<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\AppointmentStatus;
use App\Enums\PaymentStatus;
use App\Models\Appointment;
use App\Services\CancellationPolicyService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CancellationPolicyServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_paid_appointment_can_be_cancelled_and_refunded_at_least_48_hours_before_start(): void
    {
        $now = CarbonImmutable::parse('2026-06-19 12:00:00', 'UTC');

        $appointment = Appointment::factory()->create([
            'starts_at' => $now->addHours(48),
            'ends_at' => $now->addHours(49),
            'status' => AppointmentStatus::CONFIRMED,
            'payment_status' => PaymentStatus::PAID,
            'reschedule_count' => 0,
        ]);

        $service = app(CancellationPolicyService::class);

        $this->assertTrue($service->canCancel($appointment, $now));
        $this->assertTrue($service->canRefund($appointment, $now));
        $this->assertTrue($service->canReschedule($appointment, $now));
    }

    public function test_appointment_between_24_and_48_hours_can_only_be_rescheduled(): void
    {
        $now = CarbonImmutable::parse('2026-06-19 12:00:00', 'UTC');

        $appointment = Appointment::factory()->create([
            'starts_at' => $now->addHours(30),
            'ends_at' => $now->addHours(31),
            'status' => AppointmentStatus::CONFIRMED,
            'payment_status' => PaymentStatus::PAID,
            'reschedule_count' => 0,
        ]);

        $service = app(CancellationPolicyService::class);

        $this->assertFalse($service->canCancel($appointment, $now));
        $this->assertFalse($service->canRefund($appointment, $now));
        $this->assertTrue($service->canReschedule($appointment, $now));
    }

    public function test_appointment_under_24_hours_cannot_be_cancelled_or_rescheduled(): void
    {
        $now = CarbonImmutable::parse('2026-06-19 12:00:00', 'UTC');

        $appointment = Appointment::factory()->create([
            'starts_at' => $now->addHours(12),
            'ends_at' => $now->addHours(13),
            'status' => AppointmentStatus::CONFIRMED,
            'payment_status' => PaymentStatus::PAID,
            'reschedule_count' => 0,
        ]);

        $service = app(CancellationPolicyService::class);

        $this->assertFalse($service->canCancel($appointment, $now));
        $this->assertFalse($service->canRefund($appointment, $now));
        $this->assertFalse($service->canReschedule($appointment, $now));
    }

    public function test_reschedule_limit_is_two(): void
    {
        $now = CarbonImmutable::parse('2026-06-19 12:00:00', 'UTC');

        $appointment = Appointment::factory()->create([
            'starts_at' => $now->addHours(30),
            'ends_at' => $now->addHours(31),
            'status' => AppointmentStatus::CONFIRMED,
            'payment_status' => PaymentStatus::PAID,
            'reschedule_count' => 2,
        ]);

        $service = app(CancellationPolicyService::class);

        $this->assertFalse($service->canReschedule($appointment, $now));
    }
}