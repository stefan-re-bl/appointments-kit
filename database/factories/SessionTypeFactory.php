<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Professional;
use App\Models\SessionType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SessionType>
 */
final class SessionTypeFactory extends Factory
{
    protected $model = SessionType::class;

    public function configure(): static
    {
        return $this->for(Professional::factory());
    }

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(['Terapia Individual', 'Terapia de Pareja', 'Consulta Inicial']),
            'duration_minutes' => fake()->randomElement([30, 60, 90]),
            'price' => fake()->randomFloat(2, 20, 150),
            'currency' => fake()->randomElement(['ARS', 'USD']),
            'is_active' => true,
        ];
    }
}
