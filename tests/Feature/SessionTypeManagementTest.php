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

    public function test_therapist_can_view_only_their_session_types(): void
    {
        [$user, $therapist] = $this->makeTherapist();
        [, $otherTherapist] = $this->makeTherapist();

        SessionType::factory()->for($therapist)->create([
            'name' => 'Sesión Propia',
        ]);
        SessionType::factory()->for($otherTherapist)->create([
            'name' => 'Sesión Ajena',
        ]);

        $this
            ->actingAs($user)
            ->get(route('session-types.index'))
            ->assertOk()
            ->assertSeeText('Sesión Propia')
            ->assertDontSeeText('Sesión Ajena');
    }

    public function test_therapist_can_create_valid_session_type(): void
    {
        [$user, $therapist] = $this->makeTherapist();

        $this
            ->actingAs($user)
            ->post(route('session-types.store'), [
                'name' => 'Consulta Inicial',
                'duration_minutes' => 60,
                'price' => '120.00',
                'currency' => 'ARS',
                'is_active' => '1',
            ])
            ->assertRedirect(route('session-types.index'))
            ->assertSessionHas('success', __('app.session_type_management.created'));

        $this->assertDatabaseHas('session_types', [
            'therapist_id' => $therapist->id,
            'name' => 'Consulta Inicial',
            'duration_minutes' => 60,
            'price' => '120.00',
            'currency' => 'ARS',
            'is_active' => true,
        ]);
    }

    public function test_therapist_can_update_their_session_type(): void
    {
        [$user, $therapist] = $this->makeTherapist();
        $sessionType = SessionType::factory()->for($therapist)->create([
            'name' => 'Nombre Anterior',
            'duration_minutes' => 30,
            'price' => '80.00',
            'currency' => 'ARS',
            'is_active' => true,
        ]);

        $this
            ->actingAs($user)
            ->put(route('session-types.update', $sessionType), [
                'name' => 'Nombre Actualizado',
                'duration_minutes' => 90,
                'price' => '150.00',
                'currency' => 'USD',
                'is_active' => '1',
            ])
            ->assertRedirect(route('session-types.index'))
            ->assertSessionHas('success', __('app.session_type_management.updated'));

        $this->assertDatabaseHas('session_types', [
            'id' => $sessionType->id,
            'name' => 'Nombre Actualizado',
            'duration_minutes' => 90,
            'price' => '150.00',
            'currency' => 'USD',
            'is_active' => true,
        ]);
    }

    public function test_therapist_can_activate_and_deactivate_their_session_type_through_update(): void
    {
        [$user, $therapist] = $this->makeTherapist();
        $sessionType = SessionType::factory()->for($therapist)->create([
            'is_active' => true,
        ]);

        $this
            ->actingAs($user)
            ->put(route('session-types.update', $sessionType), [
                'name' => $sessionType->name,
                'duration_minutes' => $sessionType->duration_minutes,
                'price' => $sessionType->price,
                'currency' => $sessionType->currency,
                'is_active' => '0',
            ])
            ->assertRedirect(route('session-types.index'));

        $this->assertDatabaseHas('session_types', [
            'id' => $sessionType->id,
            'is_active' => false,
        ]);

        $sessionType->refresh();

        $this
            ->actingAs($user)
            ->put(route('session-types.update', $sessionType), [
                'name' => $sessionType->name,
                'duration_minutes' => $sessionType->duration_minutes,
                'price' => $sessionType->price,
                'currency' => $sessionType->currency,
                'is_active' => '1',
            ])
            ->assertRedirect(route('session-types.index'));

        $this->assertDatabaseHas('session_types', [
            'id' => $sessionType->id,
            'is_active' => true,
        ]);
    }

    public function test_therapist_can_delete_their_session_type(): void
    {
        [$user, $therapist] = $this->makeTherapist();
        $sessionType = SessionType::factory()->for($therapist)->create();

        $this
            ->actingAs($user)
            ->delete(route('session-types.destroy', $sessionType))
            ->assertRedirect(route('session-types.index'))
            ->assertSessionHas('success', __('app.session_type_management.deleted'));

        $this->assertDatabaseMissing('session_types', [
            'id' => $sessionType->id,
        ]);
    }

    public function test_therapist_cannot_edit_update_or_delete_another_therapists_session_type(): void
    {
        [$user] = $this->makeTherapist();
        [, $otherTherapist] = $this->makeTherapist();
        $sessionType = SessionType::factory()->for($otherTherapist)->create();

        $this
            ->actingAs($user)
            ->get(route('session-types.edit', $sessionType))
            ->assertForbidden();

        $this
            ->actingAs($user)
            ->put(route('session-types.update', $sessionType), [
                'name' => 'Intento Ajeno',
                'duration_minutes' => 60,
                'price' => '120.00',
                'currency' => 'ARS',
                'is_active' => '1',
            ])
            ->assertForbidden();

        $this
            ->actingAs($user)
            ->delete(route('session-types.destroy', $sessionType))
            ->assertForbidden();

        $this->assertDatabaseHas('session_types', [
            'id' => $sessionType->id,
            'name' => $sessionType->name,
        ]);
    }

    public function test_guest_is_redirected_from_session_type_management(): void
    {
        $this
            ->get(route('session-types.index'))
            ->assertRedirect(route('login'));
    }

    public function test_session_type_validation_rejects_invalid_payload(): void
    {
        [$user] = $this->makeTherapist();

        $this
            ->actingAs($user)
            ->from(route('session-types.create'))
            ->post(route('session-types.store'), [
                'name' => '',
                'duration_minutes' => 45,
                'price' => -1,
                'currency' => 'EUR',
                'is_active' => '1',
            ])
            ->assertRedirect(route('session-types.create'))
            ->assertSessionHasErrors([
                'name',
                'duration_minutes',
                'price',
                'currency',
            ]);
    }

    /**
     * @return array{0: User, 1: Therapist}
     */
    private function makeTherapist(): array
    {
        $user = User::factory()->create([
            'role' => Role::THERAPIST,
        ]);

        $therapist = Therapist::factory()->for($user)->create([
            'timezone' => 'America/Argentina/Buenos_Aires',
            'is_active' => true,
            'is_approved' => true,
        ]);

        return [$user, $therapist];
    }
}
