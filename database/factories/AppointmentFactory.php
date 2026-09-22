<?php

namespace Database\Factories;

use App\Enums\AppointmentStatus;
use App\Enums\PaymentStatus;
use App\Models\Appointment;
use App\Models\Professional;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Appointment>
 */
class AppointmentFactory extends Factory
{
    public function definition(): array
    {
        $startsAt = Carbon::now('UTC')->addDays(fake()->numberBetween(1, 10));

        return [
            'professional_id' => Professional::factory(),
            'service_id' => Service::factory(),
            'customer_name' => fake()->name(),
            'customer_email' => fake()->unique()->safeEmail(),
            'customer_phone' => null,
            'customer_timezone' => fake()->timezone,
            'customer_locale' => 'es',
            'terms_accepted_at' => Carbon::now('UTC'),
            'customer_whatsapp_opt_in_at' => null,
            'customer_whatsapp_opt_out_at' => null,
            'starts_at' => $startsAt,
            'ends_at' => $startsAt->copy()->addHour(), // Duración de 1 hora por defecto
            'status' => AppointmentStatus::PENDING,
            'price' => fake()->randomFloat(2, 20, 150),
            'currency' => (string) config('booking.currencies.default', 'ARS'),
            'payment_status' => PaymentStatus::PENDING,
            'paid_at' => null,
            'reschedule_count' => 0,
        ];
    }
}
