<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\AppointmentStatus;
use App\Enums\PaymentStatus;
use App\Enums\Role;
use App\Jobs\SendAppointmentReminderEmail;
use App\Mail\AppointmentReminder;
use App\Models\Appointment;
use App\Models\Professional;
use App\Models\SessionType;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

final class SendAppointmentRemindersCommandTest extends TestCase
{
    use RefreshDatabase;

    private CarbonImmutable $now;

    protected function setUp(): void
    {
        parent::setUp();

        $this->now = CarbonImmutable::parse('2026-06-18 15:00:00', 'UTC');

        CarbonImmutable::setTestNow($this->now);
    }

    protected function tearDown(): void
    {
        CarbonImmutable::setTestNow();

        parent::tearDown();
    }

    public function test_it_queues_reminder_for_confirmed_appointment_starting_within_next_24_hours(): void
    {
        Mail::fake();
        Queue::fake();

        $appointment = $this->makeAppointment();

        $this->artisan('appointments:send-reminders')
            ->assertExitCode(Command::SUCCESS);

        Queue::assertPushed(
            SendAppointmentReminderEmail::class,
            1
        );
        Mail::assertNothingSent();

        $appointment->refresh();

        $this->assertNotNull($appointment->reminder_queued_at);
        $this->assertNull($appointment->reminder_sent_at);
    }

    public function test_it_does_not_send_duplicate_reminders(): void
    {
        Mail::fake();
        Queue::fake();

        $sentAt = $this->now->subMinutes(10)->toDateTimeString();

        $appointment = $this->makeAppointment([
            'reminder_sent_at' => $sentAt,
        ]);

        $this->artisan('appointments:send-reminders')
            ->assertExitCode(Command::SUCCESS);

        Mail::assertNothingSent();
        Queue::assertNothingPushed();

        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'reminder_sent_at' => $sentAt,
        ]);
    }

    public function test_it_does_not_send_reminder_for_cancelled_appointment(): void
    {
        Mail::fake();
        Queue::fake();

        $appointment = $this->makeAppointment([
            'status' => AppointmentStatus::CANCELLED->value,
        ]);

        $this->artisan('appointments:send-reminders')
            ->assertExitCode(Command::SUCCESS);

        Mail::assertNothingSent();
        Queue::assertNothingPushed();

        $this->assertNull($appointment->refresh()->reminder_sent_at);
    }

    public function test_it_does_not_send_reminder_for_appointment_outside_24_hour_window(): void
    {
        Mail::fake();
        Queue::fake();

        $appointment = $this->makeAppointment([
            'starts_at' => $this->now->addDay()->addMinute()->toDateTimeString(),
            'ends_at' => $this->now->addDay()->addMinutes(61)->toDateTimeString(),
        ]);

        $this->artisan('appointments:send-reminders')
            ->assertExitCode(Command::SUCCESS);

        Mail::assertNothingSent();
        Queue::assertNothingPushed();

        $this->assertNull($appointment->refresh()->reminder_sent_at);
    }

    public function test_it_uses_configured_reminder_window(): void
    {
        config([
            'booking.reminders.lead_hours' => 12,
        ]);

        Mail::fake();
        Queue::fake();

        $appointment = $this->makeAppointment([
            'starts_at' => $this->now->addHours(12)->subMinute()->toDateTimeString(),
            'ends_at' => $this->now->addHours(13)->subMinute()->toDateTimeString(),
        ]);

        $this->artisan('appointments:send-reminders')
            ->assertExitCode(Command::SUCCESS);

        Queue::assertPushed(SendAppointmentReminderEmail::class, 1);
        Mail::assertNothingSent();

        $this->assertNotNull($appointment->refresh()->reminder_queued_at);
    }

    public function test_it_respects_configured_reminder_window_limit(): void
    {
        config([
            'booking.reminders.lead_hours' => 12,
        ]);

        Mail::fake();
        Queue::fake();

        $appointment = $this->makeAppointment([
            'starts_at' => $this->now->addHours(12)->addMinute()->toDateTimeString(),
            'ends_at' => $this->now->addHours(13)->addMinute()->toDateTimeString(),
        ]);

        $this->artisan('appointments:send-reminders')
            ->assertExitCode(Command::SUCCESS);

        Mail::assertNothingSent();
        Queue::assertNothingPushed();

        $this->assertNull($appointment->refresh()->reminder_sent_at);
    }

    public function test_dry_run_does_not_send_or_mark_reminders(): void
    {
        Mail::fake();

        $appointment = $this->makeAppointment();

        $this->artisan('appointments:send-reminders --dry-run')
            ->expectsOutput('Due appointment reminders to queue: 1')
            ->assertExitCode(Command::SUCCESS);

        Mail::assertNothingSent();

        $this->assertNull($appointment->refresh()->reminder_sent_at);
    }

    public function test_reminder_queue_window_is_evaluated_in_utc_when_php_timezone_differs(): void
    {
        Mail::fake();
        Queue::fake();

        $originalTimezone = date_default_timezone_get();
        date_default_timezone_set('Asia/Tokyo');

        try {
            $appointment = $this->makeAppointment([
                'starts_at' => $this->now->addDay()->subMinute()->toDateTimeString(),
                'ends_at' => $this->now->addDay()->addMinutes(59)->toDateTimeString(),
            ]);

            $this->artisan('appointments:send-reminders')
                ->assertExitCode(Command::SUCCESS);

            Queue::assertPushed(SendAppointmentReminderEmail::class, 1);
            Mail::assertNothingSent();

            $this->assertNotNull($appointment->refresh()->reminder_queued_at);
            $this->assertSame(
                $this->now->format('Y-m-d H:i'),
                $appointment->reminder_queued_at->utc()->format('Y-m-d H:i'),
            );
        } finally {
            date_default_timezone_set($originalTimezone);
        }
    }

    public function test_reminder_job_sends_email_and_marks_reminder_as_sent(): void
    {
        Mail::fake();

        $appointment = $this->makeAppointment([
            'reminder_queued_at' => $this->now->toDateTimeString(),
        ]);

        (new SendAppointmentReminderEmail($appointment->id))->handle();

        Mail::assertSent(
            AppointmentReminder::class,
            fn (AppointmentReminder $mail): bool => $mail->hasTo('patient@example.test')
                && $mail->appointment->patient_timezone === 'America/Argentina/Buenos_Aires'
        );

        $appointment->refresh();

        $this->assertNotNull($appointment->reminder_sent_at);
        $this->assertNull($appointment->reminder_queued_at);
        $this->assertNull($appointment->reminder_failed_at);
        $this->assertSame(1, $appointment->reminder_attempts);
    }

    public function test_it_does_not_queue_duplicate_reminder_while_existing_queue_claim_is_fresh(): void
    {
        Mail::fake();
        Queue::fake();

        $appointment = $this->makeAppointment([
            'reminder_queued_at' => $this->now->subMinutes(5)->toDateTimeString(),
        ]);

        $this->artisan('appointments:send-reminders')
            ->assertExitCode(Command::SUCCESS);

        Mail::assertNothingSent();
        Queue::assertNothingPushed();

        $this->assertNull($appointment->refresh()->reminder_sent_at);
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function makeAppointment(array $overrides = []): Appointment
    {
        $professionalUser = User::factory()->create([
            'role' => Role::PROFESSIONAL->value,
        ]);

        $professional = Professional::factory()
            ->for($professionalUser)
            ->create([
                'timezone' => 'America/Argentina/Buenos_Aires',
                'google_meet_link' => 'https://meet.google.com/test-link',
                'is_active' => true,
            ]);

        $sessionType = SessionType::factory()
            ->for($professional)
            ->create([
                'name' => 'Servicio individual',
                'duration_minutes' => 60,
                'price' => 10000,
                'currency' => 'ARS',
                'is_active' => true,
            ]);

        return Appointment::factory()
            ->for($professional)
            ->for($sessionType, 'sessionType')
            ->create(array_merge([
                'patient_name' => 'Customer Test',
                'patient_email' => 'patient@example.test',
                'patient_timezone' => 'America/Argentina/Buenos_Aires',
                'starts_at' => $this->now->addDay()->subMinute()->toDateTimeString(),
                'ends_at' => $this->now->addDay()->addMinutes(59)->toDateTimeString(),
                'status' => AppointmentStatus::CONFIRMED->value,
                'payment_status' => PaymentStatus::PENDING->value,
                'paid_at' => null,
                'reminder_sent_at' => null,
            ], $overrides));
    }
}
