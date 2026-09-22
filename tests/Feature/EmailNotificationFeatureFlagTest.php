<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Actions\Appointments\QueueAppointmentCancelledEmails;
use App\Actions\Appointments\QueueAppointmentRescheduledEmails;
use App\Enums\AppointmentStatus;
use App\Jobs\SendAppointmentReminderEmail;
use App\Models\Appointment;
use App\Models\Professional;
use App\Models\SessionType;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

final class EmailNotificationFeatureFlagTest extends TestCase
{
    use RefreshDatabase;

    public function test_cancelled_and_rescheduled_emails_are_skipped_when_email_feature_is_disabled(): void
    {
        config(['features.email_notifications' => false]);

        Mail::fake();

        $appointment = $this->makeAppointment();

        app(QueueAppointmentCancelledEmails::class)->execute($appointment);
        app(QueueAppointmentRescheduledEmails::class)->execute(
            $appointment,
            CarbonImmutable::now('UTC')->addDays(2),
            CarbonImmutable::now('UTC')->addDays(2)->addHour(),
        );

        Mail::assertNothingQueued();
        Mail::assertNothingSent();
    }

    public function test_reminder_email_job_is_not_queued_when_email_feature_is_disabled(): void
    {
        config([
            'features.email_notifications' => false,
            'features.whatsapp' => false,
        ]);

        Queue::fake();
        Mail::fake();

        $appointment = $this->makeAppointment([
            'starts_at' => CarbonImmutable::now('UTC')->addHours(12),
            'ends_at' => CarbonImmutable::now('UTC')->addHours(13),
            'status' => AppointmentStatus::CONFIRMED,
        ]);

        $this->artisan('appointments:send-reminders')->assertSuccessful();

        Queue::assertNotPushed(SendAppointmentReminderEmail::class);
        Mail::assertNothingSent();

        $appointment->refresh();

        $this->assertNotNull($appointment->reminder_sent_at);
        $this->assertNull($appointment->reminder_queued_at);
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function makeAppointment(array $overrides = []): Appointment
    {
        $user = User::factory()->professional()->create([
            'email' => 'professional@example.test',
        ]);

        $professional = Professional::factory()
            ->for($user)
            ->create([
                'is_active' => true,
                'is_approved' => true,
            ]);

        $sessionType = SessionType::factory()
            ->for($professional)
            ->create();

        return Appointment::factory()
            ->for($professional)
            ->for($sessionType)
            ->create($overrides);
    }
}
