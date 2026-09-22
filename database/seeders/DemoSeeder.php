<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\AppointmentStatus;
use App\Enums\PaymentStatus;
use App\Enums\Role;
use App\Models\Appointment;
use App\Models\Professional;
use App\Models\Service;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

final class DemoSeeder extends Seeder
{
    /**
     * @var array<int, array{name: string, email: string, timezone: string, bio: string}>
     */
    private array $providers = [
        [
            'name' => 'Ana Martínez',
            'email' => 'ana.martinez@demo.test',
            'timezone' => 'America/Argentina/Buenos_Aires',
            'bio' => 'Profesional de Demo Salud con experiencia en atención remota.',
        ],
        [
            'name' => 'Lucas Fernández',
            'email' => 'lucas.fernandez@demo.test',
            'timezone' => 'Europe/Madrid',
            'bio' => 'Profesional de Demo Salud orientado a servicios programados.',
        ],
        [
            'name' => 'Sofía Gómez',
            'email' => 'sofia.gomez@demo.test',
            'timezone' => 'America/Mexico_City',
            'bio' => 'Profesional de Demo Salud con agenda online configurable.',
        ],
    ];

    /**
     * @var array<int, array{name: string, duration_minutes: int, price: float}>
     */
    private array $services = [
        ['name' => 'Consulta inicial', 'duration_minutes' => 30, 'price' => 12000.00],
        ['name' => 'Consulta estándar', 'duration_minutes' => 60, 'price' => 22000.00],
        ['name' => 'Consulta extendida', 'duration_minutes' => 90, 'price' => 32000.00],
    ];

    public function run(): void
    {
        config(['branding.name' => 'Demo Salud']);

        $this->admin();

        foreach ($this->providers as $providerData) {
            $provider = $this->provider($providerData);
            $services = $this->servicesFor($provider);

            $this->replaceAvailabilities($provider);
            $this->replaceAppointments($provider, $services);
        }
    }

    private function admin(): User
    {
        return $this->upsertUser(
            email: 'admin@demo.test',
            name: 'Admin Demo Salud',
            role: Role::ADMIN,
        );
    }

    /**
     * @param  array{name: string, email: string, timezone: string, bio: string}  $data
     */
    private function provider(array $data): Professional
    {
        $user = $this->upsertUser(
            email: $data['email'],
            name: $data['name'],
            role: Role::PROFESSIONAL,
        );

        $provider = $user->professional()->firstOrNew();

        $provider->fill([
            'timezone' => $data['timezone'],
            'bio' => $data['bio'],
            'specialties' => 'Servicios profesionales, atención programada, seguimiento operativo.',
            'professional_approach' => 'Atención estructurada con agenda, recordatorios y seguimiento administrativo.',
            'payment_instructions' => 'Demo: registrar pago como pendiente, pagado o bonificado según el caso.',
            'google_meet_link' => 'https://meet.google.com/demo-'.$user->id,
            'preferred_locale' => 'es',
            'whatsapp_phone' => '+54911000000'.$user->id,
            'whatsapp_notifications_enabled' => true,
            'whatsapp_confirmations_enabled' => true,
            'whatsapp_reminders_enabled' => true,
            'is_active' => true,
            'is_approved' => true,
        ]);

        $provider->save();

        return $provider;
    }

    /**
     * @return array<string, Service>
     */
    private function servicesFor(Professional $provider): array
    {
        $services = [];

        foreach ($this->services as $serviceData) {
            $service = $provider->services()->updateOrCreate(
                ['name' => $serviceData['name']],
                [
                    'duration_minutes' => $serviceData['duration_minutes'],
                    'price' => $serviceData['price'],
                    'currency' => (string) config('booking.currencies.default', 'ARS'),
                    'is_active' => true,
                ],
            );

            $services[$serviceData['name']] = $service;
        }

        return $services;
    }

    private function replaceAvailabilities(Professional $provider): void
    {
        $provider->availabilities()->delete();

        foreach ([1, 2, 3, 4, 5] as $dayOfWeek) {
            $provider->availabilities()->create([
                'day_of_week' => $dayOfWeek,
                'start_time' => '09:00:00',
                'end_time' => '13:00:00',
                'is_active' => true,
            ]);

            $provider->availabilities()->create([
                'day_of_week' => $dayOfWeek,
                'start_time' => '15:00:00',
                'end_time' => '18:00:00',
                'is_active' => true,
            ]);
        }
    }

    /**
     * @param  array<string, Service>  $services
     */
    private function replaceAppointments(Professional $provider, array $services): void
    {
        $provider->appointments()->delete();

        $now = CarbonImmutable::now('UTC')->startOfHour();

        $appointments = [
            [
                'service' => 'Consulta inicial',
                'customer_name' => 'María Pérez',
                'customer_email' => 'maria.perez@example.test',
                'starts_at' => $now->addDays(2)->setTime(13, 0),
                'status' => AppointmentStatus::CONFIRMED,
                'payment_status' => PaymentStatus::PENDING,
                'paid_at' => null,
                'reschedule_count' => 0,
            ],
            [
                'service' => 'Consulta estándar',
                'customer_name' => 'Julián Torres',
                'customer_email' => 'julian.torres@example.test',
                'starts_at' => $now->addDays(4)->setTime(15, 0),
                'status' => AppointmentStatus::CONFIRMED,
                'payment_status' => PaymentStatus::PAID,
                'paid_at' => $now->subHours(2),
                'reschedule_count' => 1,
            ],
            [
                'service' => 'Consulta extendida',
                'customer_name' => 'Carla Ruiz',
                'customer_email' => 'carla.ruiz@example.test',
                'starts_at' => $now->subDays(3)->setTime(14, 0),
                'status' => AppointmentStatus::COMPLETED,
                'payment_status' => PaymentStatus::PAID,
                'paid_at' => $now->subDays(4),
                'reschedule_count' => 0,
            ],
            [
                'service' => 'Consulta estándar',
                'customer_name' => 'Diego Molina',
                'customer_email' => 'diego.molina@example.test',
                'starts_at' => $now->subDay()->setTime(16, 0),
                'status' => AppointmentStatus::CANCELLED,
                'payment_status' => PaymentStatus::WAIVED,
                'paid_at' => null,
                'reschedule_count' => 2,
            ],
        ];

        foreach ($appointments as $appointmentData) {
            $service = $services[$appointmentData['service']];
            $startsAt = $appointmentData['starts_at'];

            $this->createAppointment($provider, $service, [
                'customer_name' => $appointmentData['customer_name'],
                'customer_email' => $appointmentData['customer_email'],
                'customer_phone' => '+5491112345678',
                'customer_timezone' => $provider->timezone,
                'customer_locale' => 'es',
                'terms_accepted_at' => $now,
                'customer_whatsapp_opt_in_at' => $now,
                'starts_at' => $startsAt,
                'ends_at' => $startsAt->addMinutes((int) $service->duration_minutes),
                'price' => $service->price,
                'currency' => $service->currency,
                'status' => $appointmentData['status'],
                'payment_status' => $appointmentData['payment_status'],
                'paid_at' => $appointmentData['paid_at'],
                'reschedule_count' => $appointmentData['reschedule_count'],
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function createAppointment(Professional $provider, Service $service, array $data): Appointment
    {
        $appointment = Appointment::query()->create([
            'professional_id' => $provider->id,
            'service_id' => $service->id,
            'customer_name' => $data['customer_name'],
            'customer_email' => $data['customer_email'],
            'customer_phone' => $data['customer_phone'],
            'customer_timezone' => $data['customer_timezone'],
            'customer_locale' => $data['customer_locale'],
            'terms_accepted_at' => $data['terms_accepted_at'],
            'customer_whatsapp_opt_in_at' => $data['customer_whatsapp_opt_in_at'],
            'starts_at' => $data['starts_at'],
            'ends_at' => $data['ends_at'],
            'price' => $data['price'],
            'currency' => $data['currency'],
        ]);

        $appointment->forceFill([
            'status' => $data['status'],
            'payment_status' => $data['payment_status'],
            'paid_at' => $data['paid_at'],
            'reschedule_count' => $data['reschedule_count'],
        ])->save();

        return $appointment;
    }

    private function upsertUser(string $email, string $name, Role $role): User
    {
        $user = User::query()->firstOrNew(['email' => $email]);

        $user->forceFill([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make('password'),
            'role' => $role,
            'email_verified_at' => now('UTC'),
        ])->save();

        return $user;
    }
}
