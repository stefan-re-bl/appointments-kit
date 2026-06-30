<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\AppointmentStatus;
use App\Enums\PaymentStatus;
use App\Enums\Role;
use App\Models\Appointment;
use App\Models\Availability;
use App\Models\SessionType;
use App\Models\Therapist;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

final class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Crear/Actualizar Admin
        $this->upsertUser(
            email: 'admin@umbralia.com',
            name: 'Admin Umbralia',
            role: Role::ADMIN,
        );

        // 2. Crear/Actualizar 3 Terapeutas
        $therapistsData = [
            ['name' => 'Dra. Lucia (Argentina)', 'email' => 'lucia@umbralia.com', 'timezone' => 'America/Argentina/Buenos_Aires'],
            ['name' => 'Dr. Carlos (España)', 'email' => 'carlos@umbralia.com', 'timezone' => 'Europe/Madrid'],
            ['name' => 'Dra. Ana (México)', 'email' => 'ana@umbralia.com', 'timezone' => 'America/Mexico_City'],
        ];

        foreach ($therapistsData as $data) {
            // Crear o actualizar usuario (resetea password a 'password')
            $user = $this->upsertUser(
                email: $data['email'],
                name: $data['name'],
                role: Role::THERAPIST,
            );

            // Crear o actualizar perfil de terapeuta sin mass assignment de user_id.
            $therapist = $user->therapist()->firstOrNew();

            $therapist->fill([
                'timezone' => $data['timezone'],
                'bio' => "Terapeuta profesional ubicado en {$data['timezone']}.",
                'google_meet_link' => 'https://meet.google.com/fake-link-'.$user->id,
                'is_active' => true,
                'is_approved' => true,
            ]);

            $therapist->save();

            // 3. Crear Tipos de Sesión sin mass assignment de therapist_id.
            $session30 = $therapist->sessionTypes()->firstOrCreate(
                [
                    'name' => 'Sesión Introductoria 30m',
                ],
                [
                    'duration_minutes' => 30,
                    'price' => $data['timezone'] === 'Europe/Madrid' ? 30.00 : 15000.00,
                    'currency' => $data['timezone'] === 'Europe/Madrid' ? 'USD' : 'ARS',
                    'is_active' => true,
                ]
            );

            $session60 = $therapist->sessionTypes()->firstOrCreate(
                [
                    'name' => 'Terapia Regular 60m',
                ],
                [
                    'duration_minutes' => 60,
                    'price' => $data['timezone'] === 'Europe/Madrid' ? 60.00 : 30000.00,
                    'currency' => $data['timezone'] === 'Europe/Madrid' ? 'USD' : 'ARS',
                    'is_active' => true,
                ]
            );

            // 4. Disponibilidades
            // Estrategia: Borramos las antiguas de este terapeuta y creamos las nuevas
            // así el código del seeder refleja la "verdad" actual.
            Availability::where('therapist_id', $therapist->id)->delete();

            foreach ([1, 3, 5] as $dayOfWeek) {
                $therapist->availabilities()->create([
                    'day_of_week' => $dayOfWeek,
                    'start_time' => '09:00:00',
                    'end_time' => '13:00:00',
                    'is_active' => true,
                ]);

                $therapist->availabilities()->create([
                    'day_of_week' => $dayOfWeek,
                    'start_time' => '16:00:00',
                    'end_time' => '20:00:00',
                    'is_active' => true,
                ]);
            }

            // 5. Citas
            // Estrategia: Solo crear si el terapeuta tiene 0 citas (para no duplicar histórico)
            if ($therapist->appointments()->count() === 0) {
                $this->createAppointments($therapist, $session30, $session60);
            }
        }
    }

    private function upsertUser(string $email, string $name, Role $role): User
    {
        $user = User::query()->firstOrNew([
            'email' => $email,
        ]);

        $user->forceFill([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make('password'),
            'role' => $role,
            'email_verified_at' => now('UTC'),
        ])->save();

        return $user;
    }

    private function createAppointments(Therapist $therapist, SessionType $session30, SessionType $session60): void
    {
        $now = Carbon::now('UTC');

        // Cita 1: Confirmada pendiente de pago
        $this->createDemoAppointment([
            'therapist_id' => $therapist->id,
            'session_type_id' => $session30->id,
            'patient_name' => 'Juan Pérez',
            'patient_email' => 'juan@example.com',
            'patient_timezone' => 'America/Argentina/Buenos_Aires',
            'starts_at' => $now->copy()->addDays(2)->setHour(10)->setMinute(0),
            'ends_at' => $now->copy()->addDays(2)->setHour(10)->setMinute(30),
            'price' => $session30->price,
            'currency' => $session30->currency,
            'status' => AppointmentStatus::CONFIRMED,
            'payment_status' => PaymentStatus::PENDING,
            'paid_at' => null,
        ]);

        // Cita 2: Confirmada y pagada
        $this->createDemoAppointment([
            'therapist_id' => $therapist->id,
            'session_type_id' => $session60->id,
            'patient_name' => 'María García',
            'patient_email' => 'maria@example.com',
            'patient_timezone' => 'Europe/Madrid',
            'starts_at' => $now->copy()->addDay()->setHour(16)->setMinute(0),
            'ends_at' => $now->copy()->addDay()->setHour(17)->setMinute(0),
            'price' => $session60->price,
            'currency' => $session60->currency,
            'status' => AppointmentStatus::CONFIRMED,
            'payment_status' => PaymentStatus::PAID,
            'paid_at' => $now->copy()->subHour(),
        ]);

        // Cita 3: Completada
        $this->createDemoAppointment([
            'therapist_id' => $therapist->id,
            'session_type_id' => $session60->id,
            'patient_name' => 'Pedro Sánchez',
            'patient_email' => 'pedro@example.com',
            'patient_timezone' => 'America/Mexico_City',
            'starts_at' => $now->copy()->subDays(3)->setHour(9)->setMinute(0),
            'ends_at' => $now->copy()->subDays(3)->setHour(10)->setMinute(0),
            'price' => $session60->price,
            'currency' => $session60->currency,
            'status' => AppointmentStatus::COMPLETED,
            'payment_status' => PaymentStatus::PAID,
            'paid_at' => $now->copy()->subDays(4),
        ]);

        // Cita 4: Cancelada
        $this->createDemoAppointment([
            'therapist_id' => $therapist->id,
            'session_type_id' => $session30->id,
            'patient_name' => 'Ana Torres',
            'patient_email' => 'ana.t@example.com',
            'patient_timezone' => 'America/Argentina/Buenos_Aires',
            'starts_at' => $now->copy()->subDay()->setHour(11)->setMinute(0),
            'ends_at' => $now->copy()->subDay()->setHour(11)->setMinute(30),
            'price' => $session30->price,
            'currency' => $session30->currency,
            'status' => AppointmentStatus::CANCELLED,
            'payment_status' => PaymentStatus::PENDING,
            'paid_at' => null,
        ]);
    }

    /**
     * @param array{
     *     therapist_id: int,
     *     session_type_id: int,
     *     patient_name: string,
     *     patient_email: string,
     *     patient_timezone: string,
     *     starts_at: mixed,
     *     ends_at: mixed,
     *     price: mixed,
     *     currency: string,
     *     status: AppointmentStatus,
     *     payment_status: PaymentStatus,
     *     paid_at: mixed
     * } $data
     */
    private function createDemoAppointment(array $data): Appointment
    {
        $appointment = Appointment::query()->create([
            'therapist_id' => $data['therapist_id'],
            'session_type_id' => $data['session_type_id'],
            'patient_name' => $data['patient_name'],
            'patient_email' => $data['patient_email'],
            'patient_timezone' => $data['patient_timezone'],
            'starts_at' => $data['starts_at'],
            'ends_at' => $data['ends_at'],
            'price' => $data['price'],
            'currency' => $data['currency'],
        ]);

        $appointment->forceFill([
            'status' => $data['status'],
            'payment_status' => $data['payment_status'],
            'paid_at' => $data['paid_at'],
            'reschedule_count' => 0,
        ])->save();

        return $appointment;
    }
}
