<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@clinicalapp.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('password'),
                'role' => Role::ADMIN->value,
            ]
        );
    }
}