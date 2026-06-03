<?php

namespace Tests\Unit;

use App\Services\TimezoneService;
use PHPUnit\Framework\TestCase;

class TimezoneServiceTest extends TestCase
{
    protected TimezoneService $service;

    protected function setUp(): void
    {
        parent::setUp();
        // Simulamos que el middleware SetTimezone estableció Argentina como zona horaria
        app()->instance('user.timezone', 'America/Argentina/Buenos_Aires');
        $this->service = new TimezoneService();
    }

    public function test_to_utc_converts_local_time_to_utc_correctly(): void
    {
        // 15:00 en Argentina (UTC-3) debe ser 18:00 en UTC
        $utcDate = $this->service->toUtc('2023-10-15 15:00');

        $this->assertEquals('2023-10-15 18:00', $utcDate->format('Y-m-d H:i'));
        $this->assertEquals('UTC', $utcDate->timezoneName);
    }

    public function test_to_local_converts_utc_to_local_time_correctly(): void
    {
        // 18:00 UTC debe ser 15:00 en Argentina
        $localDate = $this->service->toLocal('2023-10-15 18:00:00');

        $this->assertEquals('2023-10-15 15:00', $localDate->format('Y-m-d H:i'));
        $this->assertEquals('America/Argentina/Buenos_Aires', $localDate->timezoneName);
    }

    public function test_format_for_display_returns_formatted_string(): void
    {
        // 18:00 UTC debe mostrarse como "15/10/2023 15:00"
        $formatted = $this->service->formatForDisplay('2023-10-15 18:00:00', 'd/m/Y H:i');

        $this->assertEquals('15/10/2023 15:00', $formatted);
    }
}