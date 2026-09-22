<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class AppointmentServiceAliasTest extends TestCase
{
    use RefreshDatabase;

    public function test_service_id_alias_maps_to_existing_session_type_column(): void
    {
        $service = Service::factory()->create();

        $appointment = Appointment::factory()->create([
            'professional_id' => $service->professional_id,
            'service_id' => $service->id,
        ]);

        $appointment->refresh();

        $this->assertSame($service->id, $appointment->session_type_id);
        $this->assertSame($service->id, $appointment->service_id);

        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'session_type_id' => $service->id,
            'service_id' => $service->id,
        ]);
    }

    public function test_existing_session_type_column_syncs_to_service_id(): void
    {
        $service = Service::factory()->create();

        $appointment = Appointment::factory()->create([
            'professional_id' => $service->professional_id,
            'session_type_id' => $service->id,
            'service_id' => null,
        ]);

        $appointment->refresh();

        $this->assertSame($service->id, $appointment->service_id);

        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'session_type_id' => $service->id,
            'service_id' => $service->id,
        ]);
    }
}
