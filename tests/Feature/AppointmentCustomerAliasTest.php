<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Appointment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class AppointmentCustomerAliasTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_aliases_map_to_existing_patient_columns(): void
    {
        $appointment = Appointment::factory()->create([
            'customer_name' => 'Cliente Alias',
            'customer_email' => 'alias@example.test',
            'customer_phone' => '+5491112345678',
            'customer_timezone' => 'America/Argentina/Buenos_Aires',
            'customer_locale' => 'es',
        ]);

        $appointment->refresh();

        $this->assertSame('Cliente Alias', $appointment->patient_name);
        $this->assertSame('Cliente Alias', $appointment->customer_name);
        $this->assertSame('alias@example.test', $appointment->patient_email);
        $this->assertSame('alias@example.test', $appointment->customer_email);
        $this->assertSame('+5491112345678', $appointment->customer_phone);
        $this->assertSame('America/Argentina/Buenos_Aires', $appointment->customer_timezone);
        $this->assertSame('es', $appointment->customer_locale);

        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'patient_name' => 'Cliente Alias',
            'customer_name' => 'Cliente Alias',
            'patient_email' => 'alias@example.test',
            'customer_email' => 'alias@example.test',
            'patient_phone' => '+5491112345678',
            'customer_phone' => '+5491112345678',
            'patient_timezone' => 'America/Argentina/Buenos_Aires',
            'customer_timezone' => 'America/Argentina/Buenos_Aires',
            'patient_locale' => 'es',
            'customer_locale' => 'es',
        ]);
    }

    public function test_existing_patient_columns_sync_to_customer_columns(): void
    {
        $appointment = Appointment::factory()->create([
            'patient_name' => 'Cliente Existente',
            'patient_email' => 'existente@example.test',
            'patient_phone' => '+5491199988877',
            'patient_timezone' => 'America/Santiago',
            'patient_locale' => 'en',
            'customer_name' => null,
            'customer_email' => null,
            'customer_phone' => null,
            'customer_timezone' => null,
            'customer_locale' => null,
        ]);

        $appointment->refresh();

        $this->assertSame('Cliente Existente', $appointment->customer_name);
        $this->assertSame('existente@example.test', $appointment->customer_email);
        $this->assertSame('+5491199988877', $appointment->customer_phone);
        $this->assertSame('America/Santiago', $appointment->customer_timezone);
        $this->assertSame('en', $appointment->customer_locale);

        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'patient_name' => 'Cliente Existente',
            'customer_name' => 'Cliente Existente',
            'patient_email' => 'existente@example.test',
            'customer_email' => 'existente@example.test',
            'patient_phone' => '+5491199988877',
            'customer_phone' => '+5491199988877',
            'patient_timezone' => 'America/Santiago',
            'customer_timezone' => 'America/Santiago',
            'patient_locale' => 'en',
            'customer_locale' => 'en',
        ]);
    }
}
