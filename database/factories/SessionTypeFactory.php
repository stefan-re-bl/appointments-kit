<?php

namespace Database\Factories;

use App\Models\Therapist;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SessionType>
 */
class SessionTypeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'therapist_id' => Therapist::factory(),
            'name' => fake()->randomElement(['Terapia Individual', 'Terapia de Pareja', 'Consulta Inicial']),
            'duration_minutes' => fake()->randomElement([30, 60, 90]),
            'price' => fake()->randomFloat(2, 20, 150),
            'currency' => fake()->randomElement(['ARS', 'USD']),
            'is_active' => true,
        ];
    }
}