<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ContactInquiryType;
use App\Enums\ContactMessageStatus;
use App\Models\ContactMessage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ContactMessage>
 */
final class ContactMessageFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->safeEmail(),
            'inquiry_type' => fake()->randomElement(ContactInquiryType::cases()),
            'message' => fake()->paragraph(),
            'status' => ContactMessageStatus::OPEN,
        ];
    }
}
