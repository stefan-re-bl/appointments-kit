<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\Therapist;
use App\Models\User;
use Illuminate\Database\Seeder;

final class TherapistSeeder extends Seeder
{
    public function run(): void
    {
        // Crear 3 usuarios terapeutas y su perfil de terapeuta.
        User::factory(3)
            ->therapist()
            ->create()
            ->each(function (User $user): void {
                Therapist::factory()
                    ->for($user)
                    ->create();
            });
    }
}