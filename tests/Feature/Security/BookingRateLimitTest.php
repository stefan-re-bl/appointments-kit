<?php

declare(strict_types=1);

namespace Tests\Feature\Security;

use App\Models\Professional;
use App\Models\SessionType;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class BookingRateLimitTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function slots_endpoint_is_rate_limited(): void
    {
        $date = CarbonImmutable::now('UTC')->addWeek();

        $professional = Professional::factory()->create([
            'is_active' => true,
            'timezone' => 'UTC',
        ]);

        SessionType::factory()
            ->for($professional)
            ->create([
                'duration_minutes' => 30,
                'is_active' => true,
            ]);

        DB::table('availabilities')->insert([
            'professional_id' => $professional->id,
            'day_of_week' => $date->dayOfWeekIso,
            'start_time' => '09:00',
            'end_time' => '12:00',
            'is_active' => true,
            'created_at' => now('UTC'),
            'updated_at' => now('UTC'),
        ]);

        $query = [
            'date' => $date->toDateString(),
            'timezone' => 'UTC',
        ];

        for ($i = 0; $i < 30; $i++) {
            $this
                ->actingAs($professional->user)
                ->get(route('api.slots.index', $query))
                ->assertStatus(200);
        }

        $this
            ->actingAs($professional->user)
            ->get(route('api.slots.index', $query))
            ->assertStatus(429);
    }
}
