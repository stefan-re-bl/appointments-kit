<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Professional;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Professional>
 */
final class ProfessionalFactory extends Factory
{
    protected $model = Professional::class;

    public function definition(): array
    {
        $timezones = timezone_identifiers_list();

        return [
            'timezone' => $timezones[array_rand($timezones)],
            'google_meet_link' => 'https://meet.google.com/'.fake()->regexify('[a-z]{3}-[a-z]{4}-[a-z]{3}'),
            'whatsapp_phone' => null,
            'whatsapp_notifications_enabled' => false,
            'whatsapp_confirmations_enabled' => true,
            'whatsapp_reminders_enabled' => true,
            'preferred_locale' => 'es',
            'bio' => fake()->paragraphs(3, true),
            'specialties' => fake()->sentence(),
            'professional_approach' => fake()->paragraph(),
            'payment_instructions' => fake()->sentence(),
            'is_active' => fake()->boolean(80),
            'is_approved' => true,
            'avatar_url' => fake()->imageUrl(200, 200, 'people'),
            'presentation_video_url' => null,
        ];
    }

    public function configure(): static
    {
        return $this->for(User::factory());
    }
}
