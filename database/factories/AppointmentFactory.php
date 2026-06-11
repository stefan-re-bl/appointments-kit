<?php

namespace Database\Factories;

use App\Enums\AppointmentStatus;
use App\Enums\PaymentStatus;
use App\Models\SessionType;
use App\Models\Therapist;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Appointment>
 */
class AppointmentFactory extends Factory
{
    public function definition(): array
    {
        $startsAt = Carbon::now('UTC')->addDays(fake()->numberBetween(1, 10));
        
        return [
            'therapist_id' => Therapist::factory(),
            'session_type_id' => SessionType::factory(),
            'patient_name' => fake()->name(),
            'patient_email' => fake()->unique()->safeEmail(),
            'patient_timezone' => fake()->timezone,
            'starts_at' => $startsAt,
            'ends_at' => $startsAt->copy()->addHour(), // Duración de 1 hora por defecto
            'status' => AppointmentStatus::PENDING,
            'price' => fake()->randomFloat(2, 20, 150),
            'currency' => fake()->randomElement(['ARS', 'USD']),
            'payment_status' => PaymentStatus::PENDING,
            'paid_at' => null,
            'reschedule_count' => 0,
        ];
    }
}