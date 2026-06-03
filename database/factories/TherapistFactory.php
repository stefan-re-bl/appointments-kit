<?php

namespace Database\Factories;

use App\Models\Therapist;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TherapistFactory extends Factory
{
    protected $model = Therapist::class;

    public function definition(): array
    {
        $timezones = timezone_identifiers_list();
        
        return [
            'user_id' => User::factory(),
            'timezone' => $timezones[array_rand($timezones)],
            'google_meet_link' => 'https://meet.google.com/' . fake()->regexify('[a-z]{3}-[a-z]{4}-[a-z]{3}'),
            'bio' => fake()->paragraphs(3, true),
            'is_active' => fake()->boolean(80), // 80% de probabilidad de ser true
            'avatar_url' => fake()->imageUrl(200, 200, 'people'),
        ];
    }
}