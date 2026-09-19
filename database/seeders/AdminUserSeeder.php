<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

final class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::query()->firstOrNew([
            'email' => 'admin@example.test',
        ]);

        $user->forceFill([
            'name' => 'Admin',
            'email' => 'admin@example.test',
            'role' => Role::ADMIN,
            'email_verified_at' => $user->email_verified_at ?? now('UTC'),
        ]);

        if (! $user->exists) {
            $user->forceFill([
                'password' => Hash::make('password'),
            ]);
        }

        $user->save();
    }
}
