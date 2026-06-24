<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Therapist;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Therapist>
 */
final class TherapistFactory extends Factory
{
    protected $model = Therapist::class;

    public function definition(): array
    {
        $timezones = timezone_identifiers_list();

        return [
            'timezone' => $timezones[array_rand($timezones)],
            'google_meet_link' => 'https://meet.google.com/' . fake()->regexify('[a-z]{3}-[a-z]{4}-[a-z]{3}'),
            'bio' => fake()->paragraphs(3, true),
            'is_active' => fake()->boolean(80),
            'avatar_url' => fake()->imageUrl(200, 200, 'people'),
        ];
    }

    public function configure(): static
    {
        return $this->for(User::factory());
    }
}