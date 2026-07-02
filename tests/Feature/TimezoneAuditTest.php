<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\AppointmentStatus;
use App\Enums\PaymentStatus;
use App\Mail\BookingConfirmed;
use App\Models\Appointment;
use App\Models\SessionType;
use App\Models\Therapist;
use App\Services\Reports\AppointmentReportService;
use App\Services\SlotGenerationService;
use App\Services\TimezoneService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class TimezoneAuditTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        CarbonImmutable::setTestNow();

        parent::tearDown();
    }

    public function test_cross_timezone_conversion_preserves_the_instant_across_month_boundary(): void
    {
        $service = app(TimezoneService::class);
        $localDate = CarbonImmutable::parse('2026-03-01 00:30:00', 'Pacific/Auckland');

        $utcDate = $service->toUtc($localDate, 'Pacific/Auckland');

        $this->assertSame('2026-02-28 11:30:00', $utcDate->format('Y-m-d H:i:s'));
        $this->assertSame('UTC', $utcDate->timezoneName);
        $this->assertSame(
            '2026-02-28 03:30:00',
            $service->toLocal($utcDate, 'America/Los_Angeles')->format('Y-m-d H:i:s'),
        );
    }

    public function test_recurring_madrid_availability_keeps_local_wall_time_during_dst(): void
    {
        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-06-01 00:00:00', 'UTC'));

        $timezoneService = app(TimezoneService::class);
        $therapist = Therapist::factory()->create([
            'timezone' => 'Europe/Madrid',
            'is_active' => true,
        ]);

        $therapist->availabilities()->create([
            'day_of_week' => 1,
            'start_time' => $timezoneService->timeToUtc('09:00', 'Europe/Madrid', 1),
            'end_time' => $timezoneService->timeToUtc('10:00', 'Europe/Madrid', 1),
            'is_active' => true,
        ]);

        app()->instance('user.timezone', 'UTC');

        $slots = app(SlotGenerationService::class)->generate(
            $therapist,
            '2026-07-06',
            60,
            'America/New_York',
        );

        $this->assertCount(1, $slots);
        $this->assertSame('2026-07-06T07:00:00+00:00', $slots[0]['start_utc']);
        $this->assertSame('2026-07-06T08:00:00+00:00', $slots[0]['end_utc']);
        $this->assertSame('03:00', $slots[0]['label']);
    }

    public function test_recurring_new_york_availability_uses_post_transition_dst_offset(): void
    {
        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-03-01 00:00:00', 'UTC'));

        $timezoneService = app(TimezoneService::class);
        $therapist = Therapist::factory()->create([
            'timezone' => 'America/New_York',
            'is_active' => true,
        ]);

        $therapist->availabilities()->create([
            'day_of_week' => 7,
            'start_time' => $timezoneService->timeToUtc('09:00', 'America/New_York', 7),
            'end_time' => $timezoneService->timeToUtc('10:00', 'America/New_York', 7),
            'is_active' => true,
        ]);

        $slots = app(SlotGenerationService::class)->generate(
            $therapist,
            '2026-03-08',
            60,
            'UTC',
        );

        $this->assertCount(1, $slots);
        $this->assertSame('2026-03-08T13:00:00+00:00', $slots[0]['start_utc']);
        $this->assertSame('13:00', $slots[0]['label']);
    }

    public function test_report_date_range_uses_local_month_boundaries_before_querying_utc(): void
    {
        app()->instance('user.timezone', 'America/Argentina/Buenos_Aires');

        [$therapist, $sessionType] = $this->makeTherapistAndSessionType();

        $included = $this->makeAppointment(
            $therapist,
            $sessionType,
            CarbonImmutable::parse('2026-04-01 02:30:00', 'UTC'),
            'included@example.test',
        );

        $excluded = $this->makeAppointment(
            $therapist,
            $sessionType,
            CarbonImmutable::parse('2026-04-01 03:30:00', 'UTC'),
            'excluded@example.test',
        );

        $report = app(AppointmentReportService::class)->build([
            'date_from' => '2026-03-31',
            'date_to' => '2026-03-31',
        ]);

        $appointmentIds = $report['rows']->pluck('appointment_id')->all();

        $this->assertContains($included->id, $appointmentIds);
        $this->assertNotContains($excluded->id, $appointmentIds);
        $this->assertSame('31/03/2026 23:30', $report['rows']->first()['starts_at_display']);
    }

    public function test_booking_email_formats_same_utc_instant_for_each_recipient_timezone(): void
    {
        [$therapist, $sessionType] = $this->makeTherapistAndSessionType('America/Los_Angeles');

        $appointment = $this->makeAppointment(
            $therapist,
            $sessionType,
            CarbonImmutable::parse('2026-07-01 06:30:00', 'UTC'),
            'patient@example.test',
            'Asia/Tokyo',
        );

        $patientData = (new BookingConfirmed(
            $appointment,
            BookingConfirmed::RECIPIENT_PATIENT,
        ))->content()->with;

        $therapistData = (new BookingConfirmed(
            $appointment,
            BookingConfirmed::RECIPIENT_THERAPIST,
        ))->content()->with;

        $this->assertSame('01/07/2026 15:30', $patientData['startsAt']);
        $this->assertSame('30/06/2026 23:30', $therapistData['startsAt']);
        $this->assertSame('Asia/Tokyo', $patientData['displayTimezone']);
        $this->assertSame('America/Los_Angeles', $therapistData['displayTimezone']);
    }

    /**
     * @return array{0: Therapist, 1: SessionType}
     */
    private function makeTherapistAndSessionType(string $timezone = 'UTC'): array
    {
        $therapist = Therapist::factory()->create([
            'timezone' => $timezone,
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

        return [$therapist, $sessionType];
    }

    private function makeAppointment(
        Therapist $therapist,
        SessionType $sessionType,
        CarbonImmutable $startsAt,
        string $email,
        string $patientTimezone = 'UTC',
    ): Appointment {
        return Appointment::factory()
            ->for($therapist)
            ->for($sessionType, 'sessionType')
            ->create([
                'patient_email' => $email,
                'patient_timezone' => $patientTimezone,
                'starts_at' => $startsAt,
                'ends_at' => $startsAt->addHour(),
                'status' => AppointmentStatus::CONFIRMED,
                'payment_status' => PaymentStatus::PENDING,
                'paid_at' => null,
                'price' => 100,
                'currency' => 'USD',
            ]);
    }
}
