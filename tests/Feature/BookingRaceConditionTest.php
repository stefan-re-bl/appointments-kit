<?php

namespace Tests\Feature;

use App\Enums\AppointmentStatus;
use App\Enums\PaymentStatus;
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

        // Crear terapeuta y tipo de sesión
        $user = User::factory()->create(['role' => Role::THERAPIST]);
        $this->therapist = Therapist::factory()->create(['user_id' => $user->id]);
        $this->sessionType = SessionType::factory()->create(['therapist_id' => $this->therapist->id]);

        $now = Carbon::now('UTC');
        $this->baseData = [
            'therapist_id' => $this->therapist->id,
            'session_type_id' => $this->sessionType->id,
            'patient_name' => 'Paciente Test',
            'patient_email' => 'test@example.com',
            'patient_timezone' => 'America/Argentina/Buenos_Aires',
            'starts_at' => $now->copy()->addDay()->startOfHour(),
            'ends_at' => $now->copy()->addDay()->startOfHour()->addMinutes(60),
            'price' => 50.00,       // Aseguramos datos completos
            'currency' => 'USD',    // Aseguramos datos completos
        ];
    }

    public function test_two_simultaneous_transactions_only_create_one_appointment(): void
    {
        $bookingService = app(BookingService::class);
        
        // Usamos variables separadas para evitar la advertencia de Intelephense sobre arrays nulos
        $result1 = null;
        $result2 = null;

        // Simular dos transacciones intentando reservar el mismo slot concurrentemente
        DB::transaction(function () use ($bookingService, &$result1) {
            $result1 = $bookingService->bookSlot($this->baseData);
        });

        DB::transaction(function () use ($bookingService, &$result2) {
            $result2 = $bookingService->bookSlot($this->baseData);
        });

        // La primera reserva debe ser exitosa (retorna el modelo Appointment)
        $this->assertNotNull($result1, 'La primera reserva debería haberse creado');
        $this->assertInstanceOf(Appointment::class, $result1);
        $this->assertTrue($result1->status === AppointmentStatus::PENDING);

        // La segunda reserva debe fallar por overlapping (retorna false)
        $this->assertFalse($result2, 'La segunda reserva debería haber fallado por solapamiento');

        // Asegurarnos de que solo existe 1 cita en la base de datos para ese slot
        $this->assertEquals(1, Appointment::count());
    }

    public function test_expired_pending_scope_works_correctly(): void
    {
        // Crear una cita pendiente de hace 20 minutos (expirada)
        $expiredData = $this->baseData;
        $expiredData['starts_at'] = Carbon::now()->subHour();
        $expiredData['ends_at'] = Carbon::now()->subMinutes(30);
        $expiredData['created_at'] = Carbon::now()->subMinutes(20);
        Appointment::factory()->create($expiredData);

        // Crear una cita pendiente de hace 5 minutos (no expirada)
        $validData = $this->baseData;
        $validData['starts_at'] = Carbon::now()->addDays(2);
        $validData['ends_at'] = Carbon::now()->addDays(2)->addHour();
        $validData['created_at'] = Carbon::now()->subMinutes(5);
        Appointment::factory()->create($validData);

        // El scope debe traer solo la de hace 20 minutos
        $expiredAppointments = Appointment::expiredPending()->get();

        $this->assertCount(1, $expiredAppointments);
        
        // Evitamos advertencia de Intelephense comprobando primero que no es nulo
        $expiredAppointment = $expiredAppointments->first();
        $this->assertNotNull($expiredAppointment);
        $this->assertEquals('test@example.com', $expiredAppointment->patient_email);
    }
}