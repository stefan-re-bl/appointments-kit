<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\Professional;
use App\Models\Service;
use App\Models\SessionType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class SessionTypeManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_session_type_index_is_no_longer_available_to_professionals(): void
    {
        $user = User::factory()->create([
            'role' => Role::PROFESSIONAL,
        ]);
        Professional::factory()->for($user)->create();

        $this
            ->actingAs($user)
            ->get(route('session-types.index'))
            ->assertNotFound();
    }

    public function test_session_type_mutation_routes_redirect_to_dashboard(): void
    {
        $user = User::factory()->create([
            'role' => Role::PROFESSIONAL,
        ]);
        $professional = Professional::factory()->for($user)->create();
        $sessionType = SessionType::factory()->for($professional)->create();

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

    public function test_session_type_currency_uses_configured_supported_currencies(): void
    {
        config([
            'booking.currencies.default' => 'MXN',
            'booking.currencies.supported' => ['MXN'],
        ]);

        $user = User::factory()->create([
            'role' => Role::PROFESSIONAL,
        ]);
        $professional = Professional::factory()->for($user)->create();
        $sessionType = SessionType::factory()->for($professional)->create([
            'currency' => 'MXN',
        ]);

        $this
            ->actingAs($user)
            ->put(route('session-types.update', $sessionType), [
                'name' => 'Consulta configurable',
                'duration_minutes' => 60,
                'price' => 100,
                'currency' => 'MXN',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('dashboard'));

        $this
            ->actingAs($user)
            ->put(route('session-types.update', $sessionType), [
                'name' => 'Consulta configurable',
                'duration_minutes' => 60,
                'price' => 100,
                'currency' => 'ARS',
            ])
            ->assertSessionHasErrors('currency');
    }

    public function test_service_routes_are_available_as_the_new_domain_name(): void
    {
        $user = User::factory()->create([
            'role' => Role::PROFESSIONAL,
        ]);
        $professional = Professional::factory()->for($user)->create();
        $service = Service::factory()->for($professional)->create();

        $this
            ->actingAs($user)
            ->get(route('services.index'))
            ->assertNotFound();

        $this
            ->actingAs($user)
            ->get(route('services.create'))
            ->assertRedirect(route('dashboard'));

        $this
            ->actingAs($user)
            ->put(route('services.update', $service), [
                'name' => 'Consulta nueva',
                'duration_minutes' => 60,
                'price' => 100,
                'currency' => config('booking.currencies.default', 'ARS'),
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('dashboard'));
    }
}
