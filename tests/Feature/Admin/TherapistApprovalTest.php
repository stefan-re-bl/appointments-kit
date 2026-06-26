<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Enums\Role;
use App\Models\Therapist;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class TherapistApprovalTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_filter_pending_therapists(): void
    {
        $admin = User::factory()->create([
            'role' => Role::ADMIN,
        ]);
        $pendingUser = User::factory()->create([
            'name' => 'Pendiente Admin',
            'role' => Role::THERAPIST,
        ]);
        $approvedUser = User::factory()->create([
            'name' => 'Aprobada Admin',
            'role' => Role::THERAPIST,
        ]);

        Therapist::factory()->for($pendingUser)->create([
            'is_active' => true,
            'is_approved' => false,
        ]);
        Therapist::factory()->for($approvedUser)->create([
            'is_active' => true,
            'is_approved' => true,
        ]);

        $this
            ->actingAs($admin)
            ->get(route('admin.therapists.index', ['approval_status' => 'pending']))
            ->assertOk()
            ->assertSeeText('Pendiente Admin')
            ->assertDontSeeText('Aprobada Admin');
    }

    public function test_admin_can_approve_and_revoke_therapist_approval(): void
    {
        $admin = User::factory()->create([
            'role' => Role::ADMIN,
        ]);
        $therapist = Therapist::factory()->create([
            'is_active' => true,
            'is_approved' => false,
        ]);

        $this
            ->actingAs($admin)
            ->patch(route('admin.therapists.approve', $therapist))
            ->assertRedirect();

        $this->assertTrue($therapist->refresh()->is_approved);

        $this
            ->actingAs($admin)
            ->patch(route('admin.therapists.revoke-approval', $therapist))
            ->assertRedirect();

        $this->assertFalse($therapist->refresh()->is_approved);
    }
}
