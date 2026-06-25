<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\AppointmentStatus;
use App\Enums\PaymentStatus;
use App\Enums\Role;
use App\Jobs\SendBookingConfirmedEmails;
use App\Mail\BookingConfirmed;
use App\Models\Appointment;
use App\Models\SessionType;
use App\Models\Therapist;
use App\Models\User;
use App\Services\SlotGenerationService;
use App\Services\TimezoneService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

final class CriticalBookingFlowTest extends TestCase
{
    use RefreshDatabase;

    private CarbonImmutable $now;

    protected function setUp(): void
    {
        parent::setUp();

        $this->now = CarbonImmutable::parse('2026-07-01 12:00:00', 'UTC');
        CarbonImmutable::setTestNow($this->now);
        app()->instance('user.timezone', 'America/Argentina/Buenos_Aires');
    }

    protected function tearDown(): void
    {
        CarbonImmutable::setTestNow();

        parent::tearDown();
    }

    public function test_slot_generation_excludes_an_occupied_slot(): void
    {
        [$therapist, $sessionType] = $this->makeBookableTherapist();

        Appointment::factory()
            ->for($therapist)
            ->for($sessionType, 'sessionType')
            ->create([
                'starts_at' => '2026-07-06 13:00:00',
                'ends_at' => '2026-07-06 14:00:00',
                'status' => AppointmentStatus::CONFIRMED,
                'payment_status' => PaymentStatus::PENDING,
            ]);

        $slots = app(SlotGenerationService::class)->generate(
            $therapist,
            '2026-07-06',
            60,
        );

        $this->assertSame(
            ['2026-07-06T12:00:00+00:00', '2026-07-06T14:00:00+00:00'],
            array_column($slots, 'start_utc'),
        );
        $this->assertSame(['09:00', '11:00'], array_column($slots, 'label'));
    }

    public function test_public_booking_creates_confirmed_appointment_and_dispatches_notification_job(): void
    {
        Bus::fake();

        [$therapist, $sessionType] = $this->makeBookableTherapist();

        $response = $this
            ->withSession($this->bookingSession($therapist, $sessionType, '2026-07-06T12:00:00+00:00'))
            ->post(route('book.store'), [
                'patient_name' => 'Paciente Crítico',
                'patient_email' => 'patient@example.test',
                'patient_timezone' => 'America/Argentina/Buenos_Aires',
            ]);

        $response->assertRedirect(route('book.success'));

        $appointment = Appointment::query()->sole();

        $this->assertSame(AppointmentStatus::CONFIRMED, $appointment->status);
        $this->assertSame(PaymentStatus::PENDING, $appointment->payment_status);
        $this->assertSame('2026-07-06 12:00:00', $appointment->starts_at->utc()->format('Y-m-d H:i:s'));
        $this->assertSame('2026-07-06 13:00:00', $appointment->ends_at->utc()->format('Y-m-d H:i:s'));
        $this->assertSame('100.00', $appointment->price);
        $this->assertSame('USD', $appointment->currency);

        $response->assertSessionHas('booking.appointment_token', $appointment->token);
        $response->assertSessionMissing('booking.therapist_id');
        $response->assertSessionMissing('booking.session_type_id');
        $response->assertSessionMissing('booking.date');
        $response->assertSessionMissing('booking.starts_at_utc');

        Bus::assertDispatched(
            SendBookingConfirmedEmails::class,
            fn (SendBookingConfirmedEmails $job): bool => true,
        );
    }

    public function test_public_booking_rejects_a_forged_time_outside_generated_slots(): void
    {
        Bus::fake();

        [$therapist, $sessionType] = $this->makeBookableTherapist();

        $response = $this
            ->from(route('book.confirm'))
            ->withSession($this->bookingSession($therapist, $sessionType, '2026-07-06T18:00:00+00:00'))
            ->post(route('book.store'), [
                'patient_name' => 'Paciente Crítico',
                'patient_email' => 'patient@example.test',
                'patient_timezone' => 'America/Argentina/Buenos_Aires',
            ]);

        $response->assertRedirect(route('book.confirm'));
        $response->assertSessionHasErrors('general');

        $this->assertDatabaseCount('appointments', 0);
        Bus::assertNotDispatched(SendBookingConfirmedEmails::class);
    }

    public function test_booking_notification_job_sends_patient_and_therapist_emails(): void
    {
        Mail::fake();

        [$therapist, $sessionType] = $this->makeBookableTherapist();

        $appointment = Appointment::factory()
            ->for($therapist)
            ->for($sessionType, 'sessionType')
            ->create([
                'patient_email' => 'patient@example.test',
                'patient_timezone' => 'America/Argentina/Buenos_Aires',
                'starts_at' => '2026-07-06 12:00:00',
                'ends_at' => '2026-07-06 13:00:00',
                'status' => AppointmentStatus::CONFIRMED,
                'payment_status' => PaymentStatus::PENDING,
            ]);

        (new SendBookingConfirmedEmails($appointment->id))->handle();

        Mail::assertSent(
            BookingConfirmed::class,
            fn (BookingConfirmed $mail): bool => $mail->recipientType === BookingConfirmed::RECIPIENT_PATIENT
                && $mail->hasTo('patient@example.test'),
        );

        Mail::assertSent(
            BookingConfirmed::class,
            fn (BookingConfirmed $mail): bool => $mail->recipientType === BookingConfirmed::RECIPIENT_THERAPIST
                && $mail->hasTo('therapist@example.test'),
        );

        Mail::assertSentCount(2);
    }

    /**
     * @return array{0: Therapist, 1: SessionType}
     */
    private function makeBookableTherapist(): array
    {
        $user = User::factory()->create([
            'name' => 'Terapeuta Test',
            'email' => 'therapist@example.test',
            'role' => Role::THERAPIST,
        ]);

        $therapist = Therapist::factory()
            ->for($user)
            ->create([
                'timezone' => 'America/Argentina/Buenos_Aires',
                'is_active' => true,
            ]);

        $sessionType = SessionType::factory()
            ->for($therapist)
            ->create([
                'duration_minutes' => 60,
                'price' => 100,
                'currency' => 'USD',
                'is_active' => true,
            ]);

        $timezoneService = app(TimezoneService::class);

        $therapist->availabilities()->create([
            'day_of_week' => 1,
            'start_time' => $timezoneService->timeToUtc(
                '09:00',
                'America/Argentina/Buenos_Aires',
                1,
            ),
            'end_time' => $timezoneService->timeToUtc(
                '12:00',
                'America/Argentina/Buenos_Aires',
                1,
            ),
            'is_active' => true,
        ]);

        return [$therapist, $sessionType];
    }

    /**
     * @return array<string, mixed>
     */
    private function bookingSession(
        Therapist $therapist,
        SessionType $sessionType,
        string $startsAtUtc,
    ): array {
        return [
            'booking.therapist_id' => $therapist->id,
            'booking.session_type_id' => $sessionType->id,
            'booking.date' => '2026-07-06',
            'booking.starts_at_utc' => $startsAtUtc,
        ];
    }
}
