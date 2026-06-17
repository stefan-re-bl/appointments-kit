<?php

namespace Tests\Feature;

use App\Enums\AppointmentStatus;
use App\Enums\Role;
use App\Models\Appointment;
use App\Models\SessionType;
use App\Models\Therapist;
use App\Models\User;
use App\Services\BookingService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class BookingRaceConditionTest extends TestCase
{
    use RefreshDatabase;

    private Therapist $therapist;

    private SessionType $sessionType;

    private array $baseData;

    protected function setUp(): void
    {
        parent::setUp();

        $user = User::factory()->create([
            'role' => Role::THERAPIST,
        ]);

        $this->therapist = Therapist::factory()->create([
            'user_id' => $user->id,
        ]);

        $this->sessionType = SessionType::factory()->create([
            'therapist_id' => $this->therapist->id,
        ]);

        $startsAt = Carbon::now('UTC')->addDay()->startOfHour();

        $this->baseData = [
            'therapist_id' => $this->therapist->id,
            'session_type_id' => $this->sessionType->id,
            'patient_name' => 'Paciente Test',
            'patient_email' => 'test@example.com',
            'patient_timezone' => 'America/Argentina/Buenos_Aires',
            'starts_at' => $startsAt,
            'ends_at' => $startsAt->copy()->addMinutes(60),
            'price' => 50.00,
            'currency' => 'USD',
        ];
    }

    public function test_two_simultaneous_transactions_only_create_one_appointment(): void
    {
        $bookingService = app(BookingService::class);

        $result1 = null;
        $result2 = null;

        DB::transaction(function () use ($bookingService, &$result1): void {
            $result1 = $bookingService->bookSlot($this->baseData);
        });

        DB::transaction(function () use ($bookingService, &$result2): void {
            $result2 = $bookingService->bookSlot($this->baseData);
        });

        $this->assertNotNull($result1, 'La primera reserva debería haberse creado');
        $this->assertInstanceOf(Appointment::class, $result1);
        $this->assertSame(AppointmentStatus::CONFIRMED, $result1->status);

        $this->assertFalse($result2, 'La segunda reserva debería haber fallado por solapamiento');

        $this->assertEquals(1, Appointment::count());
    }

    public function test_expired_pending_scope_works_correctly(): void
    {
        $expiredData = $this->baseData;
        $expiredData['starts_at'] = Carbon::now('UTC')->subHour();
        $expiredData['ends_at'] = Carbon::now('UTC')->subMinutes(30);
        $expiredData['created_at'] = Carbon::now('UTC')->subMinutes(20);
        $expiredData['status'] = AppointmentStatus::PENDING;

        Appointment::factory()->create($expiredData);

        $validData = $this->baseData;
        $validData['starts_at'] = Carbon::now('UTC')->addDays(2);
        $validData['ends_at'] = Carbon::now('UTC')->addDays(2)->addHour();
        $validData['created_at'] = Carbon::now('UTC')->subMinutes(5);
        $validData['status'] = AppointmentStatus::PENDING;

        Appointment::factory()->create($validData);

        $expiredAppointments = Appointment::expiredPending()->get();

        $this->assertCount(1, $expiredAppointments);

        $expiredAppointment = $expiredAppointments->first();

        $this->assertNotNull($expiredAppointment);
        $this->assertEquals('test@example.com', $expiredAppointment->patient_email);
    }
}