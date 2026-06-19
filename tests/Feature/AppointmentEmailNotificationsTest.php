<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Actions\Appointments\QueueAppointmentCancelledEmails;
use App\Actions\Appointments\QueueAppointmentRescheduledEmails;
use App\Enums\AppointmentStatus;
use App\Enums\PaymentStatus;
use App\Enums\Role;
use App\Mail\AppointmentCancelled;
use App\Mail\AppointmentRescheduled;
use App\Models\Appointment;
use App\Models\SessionType;
use App\Models\Therapist;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AppointmentEmailNotificationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_rescheduled_appointment_emails_are_queued_for_patient_and_therapist(): void
    {
        Mail::fake();

        $appointment = $this->createAppointment();

        $previousStartsAt = CarbonImmutable::parse('2026-08-10 16:00:00', 'UTC');
        $previousEndsAt = CarbonImmutable::parse('2026-08-10 17:00:00', 'UTC');

        app(QueueAppointmentRescheduledEmails::class)->execute(
            appointment: $appointment,
            previousStartsAt: $previousStartsAt,
            previousEndsAt: $previousEndsAt,
        );

        Mail::assertQueued(AppointmentRescheduled::class, 2);

        Mail::assertQueued(
            AppointmentRescheduled::class,
            fn (AppointmentRescheduled $mail): bool => $mail->hasTo($appointment->patient_email)
                && $mail->recipientTimezone === 'America/Argentina/Buenos_Aires'
                && $mail->recipientType === 'patient'
        );

        Mail::assertQueued(
            AppointmentRescheduled::class,
            fn (AppointmentRescheduled $mail): bool => $mail->hasTo($appointment->therapist->user->email)
                && $mail->recipientTimezone === 'Europe/Madrid'
                && $mail->recipientType === 'therapist'
        );
    }

    public function test_cancelled_appointment_emails_are_queued_for_patient_and_therapist(): void
    {
        Mail::fake();

        $appointment = $this->createAppointment([
            'status' => AppointmentStatus::CANCELLED,
        ]);

        app(QueueAppointmentCancelledEmails::class)->execute($appointment);

        Mail::assertQueued(AppointmentCancelled::class, 2);

        Mail::assertQueued(
            AppointmentCancelled::class,
            fn (AppointmentCancelled $mail): bool => $mail->hasTo($appointment->patient_email)
                && $mail->recipientTimezone === 'America/Argentina/Buenos_Aires'
                && $mail->recipientType === 'patient'
        );

        Mail::assertQueued(
            AppointmentCancelled::class,
            fn (AppointmentCancelled $mail): bool => $mail->hasTo($appointment->therapist->user->email)
                && $mail->recipientTimezone === 'Europe/Madrid'
                && $mail->recipientType === 'therapist'
        );
    }

    public function test_rescheduled_email_view_data_formats_times_for_recipient_timezone(): void
    {
        $appointment = $this->createAppointment([
            'starts_at' => CarbonImmutable::parse('2026-08-11 18:00:00', 'UTC'),
            'ends_at' => CarbonImmutable::parse('2026-08-11 19:00:00', 'UTC'),
        ]);

        $mail = new AppointmentRescheduled(
            appointment: $appointment,
            previousStartsAt: CarbonImmutable::parse('2026-08-10 16:00:00', 'UTC'),
            previousEndsAt: CarbonImmutable::parse('2026-08-10 17:00:00', 'UTC'),
            recipientTimezone: 'America/Argentina/Buenos_Aires',
            recipientType: 'patient',
        );

        $data = $mail->viewData();

        $this->assertSame('10/08/2026 13:00 - 10/08/2026 14:00', $data['previousRange']);
        $this->assertSame('11/08/2026 15:00 - 11/08/2026 16:00', $data['newRange']);
        $this->assertSame('America/Argentina/Buenos_Aires', $data['recipientTimezone']);
    }

    public function test_cancelled_email_view_data_formats_times_for_recipient_timezone(): void
    {
        $appointment = $this->createAppointment([
            'starts_at' => CarbonImmutable::parse('2026-08-11 18:00:00', 'UTC'),
            'ends_at' => CarbonImmutable::parse('2026-08-11 19:00:00', 'UTC'),
        ]);

        $mail = new AppointmentCancelled(
            appointment: $appointment,
            recipientTimezone: 'America/Argentina/Buenos_Aires',
            recipientType: 'patient',
        );

        $data = $mail->viewData();

        $this->assertSame('11/08/2026 15:00 - 11/08/2026 16:00', $data['appointmentRange']);
        $this->assertSame('America/Argentina/Buenos_Aires', $data['recipientTimezone']);
    }

    /**
     * @param array<string, mixed> $overrides
     */
    private function createAppointment(array $overrides = []): Appointment
    {
        $user = User::factory()->create([
            'role' => Role::THERAPIST->value,
        ]);

        $therapist = Therapist::factory()
            ->for($user)
            ->create([
                'timezone' => 'Europe/Madrid',
            ]);

        $sessionType = SessionType::factory()
            ->for($therapist)
            ->create();

        return Appointment::factory()
            ->for($therapist)
            ->for($sessionType, 'sessionType')
            ->create(array_merge([
                'patient_name' => 'Paciente Demo',
                'patient_email' => 'paciente@example.com',
                'patient_timezone' => 'America/Argentina/Buenos_Aires',
                'starts_at' => CarbonImmutable::parse('2026-08-11 18:00:00', 'UTC'),
                'ends_at' => CarbonImmutable::parse('2026-08-11 19:00:00', 'UTC'),
                'status' => AppointmentStatus::CONFIRMED,
                'payment_status' => PaymentStatus::PENDING,
            ], $overrides));
    }
}