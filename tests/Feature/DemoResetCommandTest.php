<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class DemoResetCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_reset_rebuilds_database_and_loads_demo_data(): void
    {
        User::factory()->create([
            'email' => 'temporary@example.test',
        ]);

        $this->artisan('demo:reset')
            ->expectsOutput('Demo environment reset with Demo Salud data.')
            ->assertSuccessful();

        $this->assertDatabaseMissing('users', [
            'email' => 'temporary@example.test',
        ]);

        $this->assertDatabaseHas('users', [
            'name' => 'Admin Demo Salud',
            'email' => 'admin@demo.test',
        ]);
    }

    public function test_demo_reset_refuses_to_run_in_production_without_force(): void
    {
        $this->app['env'] = 'production';
        config(['app.env' => 'production']);

        $this->artisan('demo:reset')
            ->expectsOutput('Refusing to reset demo data in production without --force.')
            ->assertFailed();
    }
}
