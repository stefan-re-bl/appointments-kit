<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\User;
use App\Models\Therapist;
use Illuminate\Database\Seeder;

class TherapistSeeder extends Seeder
{
    public function run(): void
    {
        // Crear 3 usuarios terapeutas y, por callback, su perfil de terapeuta
        User::factory(3)
            ->create(['role' => Role::THERAPIST])
            ->each(function (User $user) {
                Therapist::factory()->create(['user_id' => $user->id]);
            });
    }
}