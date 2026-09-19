<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Actions\Appointments\CancelAppointment;
use App\Actions\Appointments\RescheduleAction;
use App\Actions\Appointments\RescheduleAppointment;
use App\Enums\AppointmentStatus;
use App\Enums\PaymentStatus;
use App\Enums\Role;
use App\Mail\AppointmentCancelled;
use App\Mail\AppointmentRescheduled;
use App\Models\Appointment;
use App\Models\Professional;
use App\Models\SessionType;
use App\Models\User;
use App\Services\TimezoneService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AppointmentActionsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-08-01 12:00:00', 'UTC'));
    }

    protected function tearDown(): void
    {
        CarbonImmutable::setTestNow();

        parent::tearDown();
    }

    public function test_it_reschedules_an_appointment_and_queues_emails(): void
    {
        Mail::fake();

        $appointment = $this->createAppointment([
            'starts_at' => CarbonImmutable::parse('2026-08-10 16:00:00', 'UTC'),
            'ends_at' => CarbonImmutable::parse('2026-08-10 17:00:00', 'UTC'),
            'reschedule_count' => 0,
        ]);

        $result = app(RescheduleAppointment::class)->execute(
            appointment: $appointment,
            newStartsAtUtc: CarbonImmutable::parse('2026-08-11 18:00:00', 'UTC'),
            newEndsAtUtc: CarbonImmutable::parse('2026-08-11 19:00:00', 'UTC'),
        );

        $this->assertInstanceOf(Appointment::class, $result);
        $this->assertSame(1, $result->reschedule_count);
        $this->assertTrue($result->starts_at->equalTo(CarbonImmutable::parse('2026-08-11 18:00:00', 'UTC')));
        $this->assertTrue($result->ends_at->equalTo(CarbonImmutable::parse('2026-08-11 19:00:00', 'UTC')));

        Mail::assertQueued(AppointmentRescheduled::class, 2);
    }

    public function test_it_does_not_reschedule_when_new_slot_overlaps_another_active_appointment(): void
    {
        Mail::fake();

        $appointment = $this->createAppointment([
            'starts_at' => CarbonImmutable::parse('2026-08-10 16:00:00', 'UTC'),
            'ends_at' => CarbonImmutable::parse('2026-08-10 17:00:00', 'UTC'),
        ]);

        $this->createAppointment([
            'professional_id' => $appointment->professional_id,
            'session_type_id' => $appointment->session_type_id,
            'starts_at' => CarbonImmutable::parse('2026-08-11 18:00:00', 'UTC'),
            'ends_at' => CarbonImmutable::parse('2026-08-11 19:00:00', 'UTC'),
            'status' => AppointmentStatus::CONFIRMED,
        ]);

        $result = app(RescheduleAppointment::class)->execute(
            appointment: $appointment,
            newStartsAtUtc: CarbonImmutable::parse('2026-08-11 18:30:00', 'UTC'),
            newEndsAtUtc: CarbonImmutable::parse('2026-08-11 19:30:00', 'UTC'),
        );

        $this->assertFalse($result);

        $appointment->refresh();

        $this->assertTrue($appointment->starts_at->equalTo(CarbonImmutable::parse('2026-08-10 16:00:00', 'UTC')));
        $this->assertTrue($appointment->ends_at->equalTo(CarbonImmutable::parse('2026-08-10 17:00:00', 'UTC')));

        Mail::assertNothingQueued();
    }

    public function test_reschedule_action_uses_an_available_generated_slot(): void
    {
        Mail::fake();
        app()->instance('user.timezone', 'America/Argentina/Buenos_Aires');

        $appointment = $this->createAppointment([
            'starts_at' => CarbonImmutable::parse('2026-08-10 16:00:00', 'UTC'),
            'ends_at' => CarbonImmutable::parse('2026-08-10 17:00:00', 'UTC'),
            'reschedule_count' => 0,
        ]);

        $timezoneService = app(TimezoneService::class);

        $appointment->professional->availabilities()->create([
            'day_of_week' => 2,
            'start_time' => $timezoneService->timeToUtc(
                '15:00',
                'America/Argentina/Buenos_Aires',
                2,
            ),
            'end_time' => $timezoneService->timeToUtc(
                '16:00',
                'America/Argentina/Buenos_Aires',
                2,
            ),
            'is_active' => true,
        ]);

        $result = app(RescheduleAction::class)->execute(
            appointment: $appointment,
            date: '2026-08-11',
            startUtc: '2026-08-11T18:00:00+00:00',
        );

        $this->assertInstanceOf(Appointment::class, $result);
        $this->assertSame(1, $result->reschedule_count);
        $this->assertTrue($result->starts_at->equalTo(CarbonImmutable::parse('2026-08-11 18:00:00', 'UTC')));
        $this->assertTrue($result->ends_at->equalTo(CarbonImmutable::parse('2026-08-11 19:00:00', 'UTC')));

        Mail::assertQueued(AppointmentRescheduled::class, 2);
    }

    public function test_it_cancels_an_appointment_and_queues_emails(): void
    {
        Mail::fake();

        $appointment = $this->createAppointment([
            'status' => AppointmentStatus::CONFIRMED,
        ]);

        $result = app(CancelAppointment::class)->execute($appointment);

        $this->assertInstanceOf(Appointment::class, $result);
        $this->assertSame(AppointmentStatus::CANCELLED, $result->status);

        Mail::assertQueued(AppointmentCancelled::class, 2);
    }

    public function test_it_does_not_queue_duplicate_cancelled_emails_when_appointment_is_already_cancelled(): void
    {
        Mail::fake();

        $appointment = $this->createAppointment([
            'status' => AppointmentStatus::CANCELLED,
        ]);

        $result = app(CancelAppointment::class)->execute($appointment);

        $this->assertInstanceOf(Appointment::class, $result);
        $this->assertSame(AppointmentStatus::CANCELLED, $result->status);

        Mail::assertNothingQueued();
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function createAppointment(array $overrides = []): Appointment
    {
        $user = User::factory()->create([
            'role' => Role::PROFESSIONAL->value,
        ]);

        $professional = Professional::factory()
            ->for($user)
            ->create([
                'timezone' => 'America/Argentina/Buenos_Aires',
                'is_active' => true,
                'is_approved' => true,
            ]);

        $sessionType = SessionType::factory()
            ->for($professional)
            ->create([
                'duration_minutes' => 60,
            ]);

        return Appointment::factory()
            ->for($professional)
            ->for($sessionType, 'sessionType')
            ->create(array_merge([
                'patient_name' => 'Cliente Demo',
                'patient_email' => 'paciente@example.com',
                'patient_timezone' => 'America/Argentina/Buenos_Aires',
                'starts_at' => CarbonImmutable::parse('2026-08-10 16:00:00', 'UTC'),
                'ends_at' => CarbonImmutable::parse('2026-08-10 17:00:00', 'UTC'),
                'status' => AppointmentStatus::CONFIRMED,
                'payment_status' => PaymentStatus::PENDING,
                'reschedule_count' => 0,
            ], $overrides));
    }
}
