<?php

namespace Database\Seeders;

use App\Enums\AppointmentStatus;
use App\Enums\PaymentStatus;
use App\Enums\Role;
use App\Models\Availability;
use App\Models\Appointment;
use App\Models\SessionType;
use App\Models\Therapist;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Crear Admin (si no existe)
        if (!User::where('role', Role::ADMIN)->exists()) {
            User::factory()->create([
                'name' => 'Admin Umbralia',
                'email' => 'admin@umbralia.com',
                'role' => Role::ADMIN,
                'password' => bcrypt('password'),
            ]);
        }

        // 2. Crear 3 Terapeutas en distintas zonas horarias
        $therapistsData = [
            ['name' => 'Dra. Lucia (Argentina)', 'email' => 'lucia@umbralia.com', 'timezone' => 'America/Argentina/Buenos_Aires'],
            ['name' => 'Dr. Carlos (España)', 'email' => 'carlos@umbralia.com', 'timezone' => 'Europe/Madrid'],
            ['name' => 'Dra. Ana (México)', 'email' => 'ana@umbralia.com', 'timezone' => 'America/Mexico_City'],
        ];

        foreach ($therapistsData as $data) {
            $user = User::factory()->create([
                'name' => $data['name'],
                'email' => $data['email'],
                'role' => Role::THERAPIST,
                'password' => bcrypt('password'),
            ]);

            $therapist = Therapist::create([
                'user_id' => $user->id,
                'timezone' => $data['timezone'],
                'bio' => "Terapeuta profesional ubicado en {$data['timezone']}.",
                'google_meet_link' => 'https://meet.google.com/fake-link-' . $user->id,
                'is_active' => true,
            ]);

            // 3. Tipos de sesión para cada uno
            $session30 = SessionType::create([
                'therapist_id' => $therapist->id,
                'name' => 'Sesión Introductoria 30m',
                'duration_minutes' => 30,
                'price' => $data['timezone'] === 'Europe/Madrid' ? 30.00 : 15000.00,
                'currency' => $data['timezone'] === 'Europe/Madrid' ? 'USD' : 'ARS',
                'is_active' => true,
            ]);

            $session60 = SessionType::create([
                'therapist_id' => $therapist->id,
                'name' => 'Terapia Regular 60m',
                'duration_minutes' => 60,
                'price' => $data['timezone'] === 'Europe/Madrid' ? 60.00 : 30000.00,
                'currency' => $data['timezone'] === 'Europe/Madrid' ? 'USD' : 'ARS',
                'is_active' => true,
            ]);

            // 4. Disponibilidades (Lunes, Miércoles y Viernes)
            foreach ([1, 3, 5] as $dayOfWeek) { // 1=Lunes, 3=Miercoles, 5=Viernes (ISO-8601)
                Availability::create([
                    'therapist_id' => $therapist->id,
                    'day_of_week' => $dayOfWeek,
                    'start_time' => '09:00:00',
                    'end_time' => '13:00:00',
                    'is_active' => true,
                ]);
                Availability::create([
                    'therapist_id' => $therapist->id,
                    'day_of_week' => $dayOfWeek,
                    'start_time' => '16:00:00',
                    'end_time' => '20:00:00',
                    'is_active' => true,
                ]);
            }

            // 5. Citas en distintos estados (Horarios en UTC estricto!)
            $this->createAppointments($therapist, $session30, $session60);
        }
    }

    private function createAppointments(Therapist $therapist, SessionType $session30, SessionType $session60): void
    {
        $now = Carbon::now('UTC');

        // Cita 1: Confirmada pero pendiente de pago manual (Flujo típico del nuevo backlog)
        Appointment::create([
            'therapist_id' => $therapist->id,
            'session_type_id' => $session30->id,
            'patient_name' => 'Juan Pérez',
            'patient_email' => 'juan@example.com',
            'patient_timezone' => 'America/Argentina/Buenos_Aires',
            'starts_at' => $now->copy()->addDays(2)->setHour(10)->setMinute(0),
            'ends_at' => $now->copy()->addDays(2)->setHour(10)->setMinute(30),
            'status' => AppointmentStatus::CONFIRMED,
            'price' => $session30->price,
            'currency' => $session30->currency,
            'payment_status' => PaymentStatus::PENDING,
            'paid_at' => null,
        ]);

        // Cita 2: Confirmada y ya pagada (La terapeuta marcó el pago manualmente)
        Appointment::create([
            'therapist_id' => $therapist->id,
            'session_type_id' => $session60->id,
            'patient_name' => 'María García',
            'patient_email' => 'maria@example.com',
            'patient_timezone' => 'Europe/Madrid',
            'starts_at' => $now->copy()->addDay()->setHour(16)->setMinute(0),
            'ends_at' => $now->copy()->addDay()->setHour(17)->setMinute(0),
            'status' => AppointmentStatus::CONFIRMED,
            'price' => $session60->price,
            'currency' => $session60->currency,
            'payment_status' => PaymentStatus::PAID,
            'paid_at' => $now->copy()->subHour(),
        ]);

        // Cita 3: Completada (Sesión ya pasada y pagada)
        Appointment::create([
            'therapist_id' => $therapist->id,
            'session_type_id' => $session60->id,
            'patient_name' => 'Pedro Sánchez',
            'patient_email' => 'pedro@example.com',
            'patient_timezone' => 'America/Mexico_City',
            'starts_at' => $now->copy()->subDays(3)->setHour(9)->setMinute(0),
            'ends_at' => $now->copy()->subDays(3)->setHour(10)->setMinute(0),
            'status' => AppointmentStatus::COMPLETED,
            'price' => $session60->price,
            'currency' => $session60->currency,
            'payment_status' => PaymentStatus::PAID,
            'paid_at' => $now->copy()->subDays(4),
        ]);

        // Cita 4: Cancelada (Nunca se pagó)
        Appointment::create([
            'therapist_id' => $therapist->id,
            'session_type_id' => $session30->id,
            'patient_name' => 'Ana Torres',
            'patient_email' => 'ana.t@example.com',
            'patient_timezone' => 'America/Argentina/Buenos_Aires',
            'starts_at' => $now->copy()->subDay()->setHour(11)->setMinute(0),
            'ends_at' => $now->copy()->subDay()->setHour(11)->setMinute(30),
            'status' => AppointmentStatus::CANCELLED,
            'price' => $session30->price,
            'currency' => $session30->currency,
            'payment_status' => PaymentStatus::PENDING,
            'paid_at' => null,
        ]);
    }
}