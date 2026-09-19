<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\Role;
use App\Jobs\SendBookingConfirmedEmails;
use App\Models\Professional;
use App\Models\SessionType;
use App\Models\User;
use App\Services\TimezoneService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

final class PublicProfessionalApprovalBookingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-07-01 12:00:00', 'UTC'));
        app()->instance('user.timezone', 'America/Argentina/Buenos_Aires');
    }

    protected function tearDown(): void
    {
        CarbonImmutable::setTestNow();

        parent::tearDown();
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

        [$professional, $sessionType] = $this->makeBookableProfessional();

        $professional->forceFill([
            'is_approved' => false,
        ])->save();

        $response = $this
            ->actingAs($professional->user)
            ->withSession([
                'booking.professional_id' => $professional->id,
                'booking.session_type_id' => $sessionType->id,
                'booking.date' => '2026-07-06',
                'booking.starts_at_utc' => '2026-07-06T12:00:00+00:00',
                'booking.patient_timezone' => 'America/Argentina/Buenos_Aires',
            ])
            ->post(route('book.store'), [
                'patient_name' => 'Cliente Pendiente',
                'patient_email' => 'pending@example.test',
            ]);

        $response->assertRedirect(route('dashboard'));

        $this->assertDatabaseCount('appointments', 0);
        Bus::assertNotDispatched(SendBookingConfirmedEmails::class);
    }

    /**
     * @return array{0: Professional, 1: SessionType}
     */
    private function makeBookableProfessional(): array
    {
        $user = User::factory()->create([
            'name' => 'Profesional Test',
            'email' => 'professional@example.test',
            'role' => Role::PROFESSIONAL,
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
                'price' => 100,
                'currency' => 'USD',
                'is_active' => true,
            ]);

        $timezoneService = app(TimezoneService::class);

        $professional->availabilities()->create([
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

        return [$professional, $sessionType];
    }
}
