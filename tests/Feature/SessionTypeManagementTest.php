<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\SessionType;
use App\Models\Therapist;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class SessionTypeManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_session_type_index_is_no_longer_available_to_therapists(): void
    {
        $user = User::factory()->create([
            'role' => Role::THERAPIST,
        ]);
        Therapist::factory()->for($user)->create();

        $this
            ->actingAs($user)
            ->get(route('session-types.index'))
            ->assertNotFound();
    }

    public function test_session_type_mutation_routes_redirect_to_dashboard(): void
    {
        $user = User::factory()->create([
            'role' => Role::THERAPIST,
        ]);
        $therapist = Therapist::factory()->for($user)->create();
        $sessionType = SessionType::factory()->for($therapist)->create();

        $this
            ->actingAs($user)
            ->get(route('session-types.create'))
            ->assertRedirect(route('dashboard'));

        $this
            ->actingAs($user)
            ->put(route('session-types.update', $sessionType), [
                'name' => 'No visible',
                'duration_minutes' => 60,
                'price' => 100,
                'currency' => 'ARS',
            ])
            ->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas('session_types', [
            'id' => $sessionType->id,
            'name' => $sessionType->name,
        ]);
    }
}
