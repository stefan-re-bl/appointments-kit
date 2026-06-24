<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\AppointmentStatus;
use App\Enums\PaymentStatus;
use App\Models\ActivityLog;
use App\Models\Appointment;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

final class ActivityLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_status_changes_generate_an_immutable_activity_log(): void
    {
        $appointment = Appointment::factory()->create([
            'status' => AppointmentStatus::CONFIRMED,
            'payment_status' => PaymentStatus::PENDING,
        ]);

        $this->actingAs($appointment->therapist->user);

        $appointment->forceFill([
            'status' => AppointmentStatus::CANCELLED,
        ])->save();

        $log = ActivityLog::query()
            ->where('appointment_id', $appointment->id)
            ->where('event', ActivityLog::EVENT_APPOINTMENT_STATUS_CHANGED)
            ->firstOrFail();

        $this->assertSame('confirmed', $log->old_values['status']);
        $this->assertSame('cancelled', $log->new_values['status']);
        $this->assertSame($appointment->therapist->user->id, $log->causer_id);
        $this->assertSame($appointment->therapist->user::class, $log->causer_type);

        $this->expectException(RuntimeException::class);

        $log->update([
            'event' => 'tampered',
        ]);
    }

    public function test_manual_payment_updates_generate_an_activity_log(): void
    {
        $appointment = Appointment::factory()->create([
            'status' => AppointmentStatus::CONFIRMED,
            'payment_status' => PaymentStatus::PENDING,
            'paid_at' => null,
        ]);

        $this->actingAs($appointment->therapist->user);

        $paidAt = CarbonImmutable::now('UTC');

        $appointment->forceFill([
            'payment_status' => PaymentStatus::PAID,
            'paid_at' => $paidAt,
        ])->save();

        $log = ActivityLog::query()
            ->where('appointment_id', $appointment->id)
            ->where('event', ActivityLog::EVENT_APPOINTMENT_PAYMENT_UPDATED)
            ->firstOrFail();

        $this->assertSame('pending', $log->old_values['payment_status']);
        $this->assertSame('paid', $log->new_values['payment_status']);
        $this->assertNull($log->old_values['paid_at']);
        $this->assertNotNull($log->new_values['paid_at']);
    }

    public function test_activity_logs_cannot_be_deleted_through_the_model(): void
    {
        $appointment = Appointment::factory()->create([
            'status' => AppointmentStatus::CONFIRMED,
        ]);

        $appointment->forceFill([
            'status' => AppointmentStatus::CANCELLED,
        ])->save();

        $log = ActivityLog::query()->firstOrFail();

        $this->expectException(RuntimeException::class);

        $log->delete();
    }
}