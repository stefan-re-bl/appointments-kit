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
            'America/Argentina/Buenos_Aires',
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
            ->actingAs($therapist->user)
            ->withSession($this->bookingSession($therapist, $sessionType, '2026-07-06T12:00:00+00:00'))
            ->post(route('book.store'), [
                'patient_name' => 'Paciente Crítico',
                'patient_email' => 'patient@example.test',
            ]);

        $response->assertRedirect(route('book.success'));

        $appointment = Appointment::query()->sole();

        $this->assertSame(AppointmentStatus::CONFIRMED, $appointment->status);
        $this->assertSame(PaymentStatus::PENDING, $appointment->payment_status);
        $this->assertSame('2026-07-06 12:00:00', $appointment->starts_at->utc()->format('Y-m-d H:i:s'));
        $this->assertSame('2026-07-06 13:00:00', $appointment->ends_at->utc()->format('Y-m-d H:i:s'));
        $this->assertSame('100.00', $appointment->price);
        $this->assertSame('USD', $appointment->currency);
        $this->assertSame('America/Argentina/Buenos_Aires', $appointment->patient_timezone);
        $this->assertNotNull($appointment->terms_accepted_at);

        $response->assertSessionHas('booking.appointment_token', $appointment->token);
        $response->assertSessionMissing('booking.therapist_id');
        $response->assertSessionMissing('booking.session_type_id');
        $response->assertSessionMissing('booking.date');
        $response->assertSessionMissing('booking.starts_at_utc');
        $response->assertSessionMissing('booking.patient_timezone');

        Bus::assertDispatched(
            SendBookingConfirmedEmails::class,
            fn (SendBookingConfirmedEmails $job): bool => true,
        );
    }

    public function test_public_booking_without_timezone_cookie_uses_selected_country_timezone(): void
    {
        Bus::fake();

        [$therapist, $sessionType] = $this->makeBookableTherapist();

        $this
            ->actingAs($therapist->user)
            ->withSession([
                'booking.therapist_id' => $therapist->id,
                'booking.session_type_id' => $sessionType->id,
            ])
            ->post(route('book.store.date'), [
                'date' => '2026-07-06',
                'patient_country' => 'AR',
            ])
            ->assertRedirect(route('book.time'))
            ->assertSessionHas('booking.patient_country', 'AR')
            ->assertSessionHas('booking.patient_timezone', 'America/Argentina/Buenos_Aires');

        $response = $this
            ->actingAs($therapist->user)
            ->withSession([
                'booking.starts_at_utc' => '2026-07-06T12:00:00+00:00',
            ])
            ->post(route('book.store'), [
                'patient_name' => 'Paciente sin cookie',
                'patient_email' => 'no-cookie@example.test',
            ]);

        $response->assertRedirect(route('book.success'));

        $this->assertDatabaseHas('appointments', [
            'patient_email' => 'no-cookie@example.test',
            'patient_timezone' => 'America/Argentina/Buenos_Aires',
        ]);
    }

    public function test_public_booking_in_spanish_persists_patient_locale(): void
    {
        Bus::fake();

        [$therapist, $sessionType] = $this->makeBookableTherapist();

        $this
            ->actingAs($therapist->user)
            ->withSession($this->bookingSession($therapist, $sessionType, '2026-07-06T12:00:00+00:00') + [
                'locale' => 'es',
            ])
            ->post(route('book.store'), [
                'patient_name' => 'Paciente Español',
                'patient_email' => 'spanish@example.test',
            ])
            ->assertRedirect(route('book.success'));

        $this->assertDatabaseHas('appointments', [
            'patient_email' => 'spanish@example.test',
            'patient_locale' => 'es',
            'patient_timezone' => 'America/Argentina/Buenos_Aires',
        ]);
    }

    public function test_public_booking_in_english_persists_patient_locale_without_using_country_or_timezone(): void
    {
        Bus::fake();

        [$therapist, $sessionType] = $this->makeBookableTherapist();

        $this
            ->actingAs($therapist->user)
            ->withSession($this->bookingSession($therapist, $sessionType, '2026-07-06T12:00:00+00:00') + [
                'locale' => 'en',
            ])
            ->post(route('book.store'), [
                'patient_name' => 'English Patient',
                'patient_email' => 'english@example.test',
            ])
            ->assertRedirect(route('book.success'));

        $this->assertDatabaseHas('appointments', [
            'patient_email' => 'english@example.test',
            'patient_locale' => 'en',
            'patient_timezone' => 'America/Argentina/Buenos_Aires',
        ]);
    }

    public function test_date_step_requires_explicit_patient_country_selection(): void
    {
        [$therapist, $sessionType] = $this->makeBookableTherapist();

        $this
            ->actingAs($therapist->user)
            ->withSession([
                'booking.therapist_id' => $therapist->id,
                'booking.session_type_id' => $sessionType->id,
            ])
            ->get(route('book.date'))
            ->assertOk()
            ->assertSee('name="patient_country"', false)
            ->assertSee('name="patient_timezone"', false)
            ->assertSeeText(__('booking_timezone.country_placeholder'))
            ->assertSeeText(__('booking_timezone.countries.AR'))
            ->assertSeeText(__('booking_timezone.region_label'))
            ->assertSeeText(__('booking_timezone.region_placeholder'))
            ->assertSeeText(__('booking_timezone.patient_country_help'))
            ->assertDontSee('selectedCountry: \'AR\'', false)
            ->assertDontSeeText(__('booking_timezone.country_detected'))
            ->assertDontSeeText(__('booking_timezone.country_detection_failed'));
    }

    public function test_booking_date_accepts_country_without_region_when_country_has_single_timezone(): void
    {
        [$therapist, $sessionType] = $this->makeBookableTherapist();

        $this
            ->actingAs($therapist->user)
            ->withSession([
                'booking.therapist_id' => $therapist->id,
                'booking.session_type_id' => $sessionType->id,
            ])
            ->post(route('book.store.date'), [
                'date' => '2026-07-06',
                'patient_country' => 'BO',
            ])
            ->assertRedirect(route('book.time'))
            ->assertSessionHas('booking.patient_country', 'BO')
            ->assertSessionHas('booking.patient_timezone', 'America/La_Paz');
    }

    public function test_booking_date_accepts_country_with_multiple_effective_timezones_when_region_is_selected(): void
    {
        [$therapist, $sessionType] = $this->makeBookableTherapist();

        $this
            ->actingAs($therapist->user)
            ->withSession([
                'booking.therapist_id' => $therapist->id,
                'booking.session_type_id' => $sessionType->id,
            ])
            ->post(route('book.store.date'), [
                'date' => '2026-07-06',
                'patient_country' => 'US',
                'patient_timezone' => 'America/Los_Angeles',
            ])
            ->assertRedirect(route('book.time'))
            ->assertSessionHas('booking.patient_country', 'US')
            ->assertSessionHas('booking.patient_timezone', 'America/Los_Angeles');
    }

    public function test_booking_date_requires_time_region_when_country_has_multiple_effective_timezones(): void
    {
        [$therapist, $sessionType] = $this->makeBookableTherapist();

        $this
            ->actingAs($therapist->user)
            ->from(route('book.date'))
            ->withSession([
                'booking.therapist_id' => $therapist->id,
                'booking.session_type_id' => $sessionType->id,
            ])
            ->post(route('book.store.date'), [
                'date' => '2026-07-06',
                'patient_country' => 'US',
            ])
            ->assertRedirect(route('book.date'))
            ->assertSessionHasErrors('patient_timezone')
            ->assertSessionMissing('booking.patient_timezone');
    }

    public function test_booking_date_rejects_invalid_country(): void
    {
        [$therapist, $sessionType] = $this->makeBookableTherapist();

        $this
            ->actingAs($therapist->user)
            ->from(route('book.date'))
            ->withSession([
                'booking.therapist_id' => $therapist->id,
                'booking.session_type_id' => $sessionType->id,
            ])
            ->post(route('book.store.date'), [
                'date' => '2026-07-06',
                'patient_country' => 'ZZ',
            ])
            ->assertRedirect(route('book.date'))
            ->assertSessionHasErrors('patient_country')
            ->assertSessionMissing('booking.patient_timezone');
    }

    public function test_public_booking_rejects_a_forged_time_outside_generated_slots(): void
    {
        Bus::fake();

        [$therapist, $sessionType] = $this->makeBookableTherapist();

        $response = $this
            ->actingAs($therapist->user)
            ->from(route('book.confirm'))
            ->withSession($this->bookingSession($therapist, $sessionType, '2026-07-06T18:00:00+00:00'))
            ->post(route('book.store'), [
                'patient_name' => 'Paciente Crítico',
                'patient_email' => 'patient@example.test',
            ]);

        $response->assertRedirect(route('book.confirm'));
        $response->assertSessionHasErrors('general');

        $this->assertDatabaseCount('appointments', 0);
        Bus::assertNotDispatched(SendBookingConfirmedEmails::class);
    }

    public function test_internal_booking_requires_patient_name_and_email(): void
    {
        Bus::fake();

        [$therapist, $sessionType] = $this->makeBookableTherapist();

        $response = $this
            ->actingAs($therapist->user)
            ->from(route('book.confirm'))
            ->withSession($this->bookingSession($therapist, $sessionType, '2026-07-06T12:00:00+00:00'))
            ->post(route('book.store'), []);

        $response->assertRedirect(route('book.confirm'));
        $response->assertSessionHasErrors(['patient_name', 'patient_email']);

        $this->assertDatabaseCount('appointments', 0);
        Bus::assertNotDispatched(SendBookingConfirmedEmails::class);
    }

    public function test_guest_is_redirected_from_booking_to_home(): void
    {
        $this
            ->get(route('book.index'))
            ->assertRedirect(route('home'));
    }

    public function test_public_booking_cannot_create_appointment_after_approval_is_revoked(): void
    {
        Bus::fake();

        [$therapist, $sessionType] = $this->makeBookableTherapist();

        $therapist->forceFill([
            'is_approved' => false,
        ])->save();

        $response = $this
            ->actingAs($therapist->user)
            ->withSession($this->bookingSession($therapist, $sessionType, '2026-07-06T12:00:00+00:00'))
            ->post(route('book.store'), [
                'patient_name' => 'Paciente Pendiente',
                'patient_email' => 'pending@example.test',
            ]);

        $response->assertRedirect(route('dashboard'));

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
                'is_approved' => true,
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
            'booking.patient_timezone' => 'America/Argentina/Buenos_Aires',
        ];
    }
}
