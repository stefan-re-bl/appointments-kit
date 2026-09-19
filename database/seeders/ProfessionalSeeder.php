<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Professional;
use App\Models\User;
use Illuminate\Database\Seeder;

final class ProfessionalSeeder extends Seeder
{
    public function run(): void
    {
        // Create three professional users with their profile.
        User::factory(3)
            ->professional()
            ->create()
            ->each(function (User $user): void {
                Professional::factory()
                    ->for($user)
                    ->create();
            });
    }
}
