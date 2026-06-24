<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Rules\ValidTimezone;
use PHPUnit\Framework\TestCase;

final class ValidTimezoneTest extends TestCase
{
    public function test_it_accepts_common_browser_timezones(): void
    {
        $timezones = [
            'America/Buenos_Aires',
            'America/Argentina/Buenos_Aires',
            'Asia/Taipei',
            'Europe/Prague',
            'Europe/Madrid',
            'America/New_York',
            'America/Sao_Paulo',
            'America/Mexico_City',
            'UTC',
        ];

        foreach ($timezones as $timezone) {
            $this->assertNotNull(
                ValidTimezone::normalize($timezone),
                "Failed asserting timezone {$timezone} is valid.",
            );
        }
    }

    public function test_it_normalizes_unsupported_browser_aliases(): void
    {
        $this->assertSame(
            'America/Argentina/Buenos_Aires',
            ValidTimezone::normalize('America/Buenos_Aires'),
        );
    }

    public function test_it_rejects_invalid_timezones(): void
    {
        $this->assertNull(ValidTimezone::normalize('Not/A_Timezone'));
        $this->assertNull(ValidTimezone::normalize(''));
        $this->assertNull(ValidTimezone::normalize(null));
    }
}