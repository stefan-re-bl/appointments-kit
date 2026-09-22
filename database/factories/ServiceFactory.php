<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Professional;
use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Service>
 */
class ServiceFactory extends Factory
{
    protected $model = Service::class;

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
            'name' => fake()->randomElement(['Consulta inicial', 'Consulta estándar', 'Consulta extendida']),
            'duration_minutes' => fake()->randomElement([30, 60, 90]),
            'price' => fake()->randomFloat(2, 20, 150),
            'currency' => (string) config('booking.currencies.default', 'ARS'),
            'is_active' => true,
        ];
    }
}
