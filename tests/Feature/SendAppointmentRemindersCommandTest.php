<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\AppointmentStatus;
use App\Enums\PaymentStatus;
use App\Enums\Role;
use App\Mail\AppointmentReminder;
use App\Models\Appointment;
use App\Models\SessionType;
use App\Models\Therapist;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
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

    public function test_it_sends_reminder_for_confirmed_appointment_starting_within_next_hour(): void
    {
        Mail::fake();

        $appointment = $this->makeAppointment();

        $this->artisan('appointments:send-reminders')
            ->assertExitCode(Command::SUCCESS);

        Mail::assertSent(
            AppointmentReminder::class,
            fn (AppointmentReminder $mail): bool => $mail->hasTo('patient@example.test')
        );

        $this->assertNotNull($appointment->refresh()->reminder_sent_at);
    }

    public function test_it_does_not_send_duplicate_reminders(): void
    {
        Mail::fake();

        $sentAt = $this->now->subMinutes(10)->toDateTimeString();

        $appointment = $this->makeAppointment([
            'reminder_sent_at' => $sentAt,
        ]);

        $this->artisan('appointments:send-reminders')
            ->assertExitCode(Command::SUCCESS);

        Mail::assertNothingSent();

        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'reminder_sent_at' => $sentAt,
        ]);
    }

    public function test_it_does_not_send_reminder_for_cancelled_appointment(): void
    {
        Mail::fake();

        $appointment = $this->makeAppointment([
            'status' => AppointmentStatus::CANCELLED->value,
        ]);

        $this->artisan('appointments:send-reminders')
            ->assertExitCode(Command::SUCCESS);

        Mail::assertNothingSent();

        $this->assertNull($appointment->refresh()->reminder_sent_at);
    }

    public function test_it_does_not_send_reminder_for_appointment_outside_one_hour_window(): void
    {
        Mail::fake();

        $appointment = $this->makeAppointment([
            'starts_at' => $this->now->addMinutes(61)->toDateTimeString(),
            'ends_at' => $this->now->addMinutes(121)->toDateTimeString(),
        ]);

        $this->artisan('appointments:send-reminders')
            ->assertExitCode(Command::SUCCESS);

        Mail::assertNothingSent();

        $this->assertNull($appointment->refresh()->reminder_sent_at);
    }

    public function test_dry_run_does_not_send_or_mark_reminders(): void
    {
        Mail::fake();

        $appointment = $this->makeAppointment();

        $this->artisan('appointments:send-reminders --dry-run')
            ->expectsOutput('Due appointment reminders: 1')
            ->assertExitCode(Command::SUCCESS);

        Mail::assertNothingSent();

        $this->assertNull($appointment->refresh()->reminder_sent_at);
    }

    /**
     * @param array<string, mixed> $overrides
     */
    private function makeAppointment(array $overrides = []): Appointment
    {
        $therapistUser = User::factory()->create([
            'role' => Role::THERAPIST->value,
        ]);

        $therapist = Therapist::factory()
            ->for($therapistUser)
            ->create([
                'timezone' => 'America/Argentina/Buenos_Aires',
                'google_meet_link' => 'https://meet.google.com/test-link',
                'is_active' => true,
            ]);

        $sessionType = SessionType::factory()
            ->for($therapist)
            ->create([
                'name' => 'Sesión individual',
                'duration_minutes' => 60,
                'price' => 10000,
                'currency' => 'ARS',
                'is_active' => true,
            ]);

        return Appointment::factory()
            ->for($therapist)
            ->for($sessionType, 'sessionType')
            ->create(array_merge([
                'patient_name' => 'Patient Test',
                'patient_email' => 'patient@example.test',
                'patient_timezone' => 'America/Argentina/Buenos_Aires',
                'starts_at' => $this->now->addMinutes(59)->toDateTimeString(),
                'ends_at' => $this->now->addMinutes(119)->toDateTimeString(),
                'status' => AppointmentStatus::CONFIRMED->value,
                'payment_status' => PaymentStatus::PENDING->value,
                'paid_at' => null,
                'reminder_sent_at' => null,
            ], $overrides));
    }
}