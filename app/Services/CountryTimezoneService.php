<?php

declare(strict_types=1);

namespace App\Services;

final class CountryTimezoneService
{
    /**
     * Default booking timezone per country.
     *
     * @var array<string, string>
     */
    private const COUNTRY_TIMEZONES = [
        'AR' => 'America/Argentina/Buenos_Aires',
        'BO' => 'America/La_Paz',
        'BR' => 'America/Sao_Paulo',
        'CA' => 'America/Toronto',
        'CL' => 'America/Santiago',
        'CO' => 'America/Bogota',
        'CR' => 'America/Costa_Rica',
        'CU' => 'America/Havana',
        'DO' => 'America/Santo_Domingo',
        'EC' => 'America/Guayaquil',
        'ES' => 'Europe/Madrid',
        'GT' => 'America/Guatemala',
        'HN' => 'America/Tegucigalpa',
        'MX' => 'America/Mexico_City',
        'NI' => 'America/Managua',
        'PA' => 'America/Panama',
        'PE' => 'America/Lima',
        'PR' => 'America/Puerto_Rico',
        'PY' => 'America/Asuncion',
        'SV' => 'America/El_Salvador',
        'US' => 'America/New_York',
        'UY' => 'America/Montevideo',
        'VE' => 'America/Caracas',
    ];

    /**
     * @var array<string, array<string, string>>
     */
    private const REGION_TIMEZONES = [
        'AR' => [
            'AR-BUE' => 'America/Argentina/Buenos_Aires',
            'AR-CAT' => 'America/Argentina/Catamarca',
            'AR-CBA' => 'America/Argentina/Cordoba',
            'AR-JUJ' => 'America/Argentina/Jujuy',
            'AR-MDZ' => 'America/Argentina/Mendoza',
            'AR-SLA' => 'America/Argentina/Salta',
            'AR-SJN' => 'America/Argentina/San_Juan',
            'AR-SLU' => 'America/Argentina/San_Luis',
            'AR-TUC' => 'America/Argentina/Tucuman',
            'AR-USH' => 'America/Argentina/Ushuaia',
        ],
        'BR' => [
            'BR-AC' => 'America/Rio_Branco',
            'BR-AM' => 'America/Manaus',
            'BR-PE' => 'America/Recife',
            'BR-SP' => 'America/Sao_Paulo',
        ],
        'CA' => [
            'CA-BC' => 'America/Vancouver',
            'CA-AB' => 'America/Edmonton',
            'CA-MB' => 'America/Winnipeg',
            'CA-ON' => 'America/Toronto',
            'CA-QC' => 'America/Toronto',
            'CA-NS' => 'America/Halifax',
            'CA-NL' => 'America/St_Johns',
        ],
        'CL' => [
            'CL-CL' => 'America/Santiago',
            'CL-EI' => 'Pacific/Easter',
        ],
        'EC' => [
            'EC-EC' => 'America/Guayaquil',
            'EC-GAL' => 'Pacific/Galapagos',
        ],
        'ES' => [
            'ES-ES' => 'Europe/Madrid',
            'ES-CN' => 'Atlantic/Canary',
        ],
        'MX' => [
            'MX-CMX' => 'America/Mexico_City',
            'MX-ROO' => 'America/Cancun',
            'MX-CHH' => 'America/Chihuahua',
            'MX-BCN' => 'America/Tijuana',
            'MX-BCS' => 'America/Mazatlan',
        ],
        'US' => [
            'US-ET' => 'America/New_York',
            'US-CT' => 'America/Chicago',
            'US-MT' => 'America/Denver',
            'US-PT' => 'America/Los_Angeles',
            'US-AK' => 'America/Anchorage',
            'US-HI' => 'Pacific/Honolulu',
        ],
    ];

    /**
     * @return array<string, string>
     */
    public function countries(): array
    {
        return self::COUNTRY_TIMEZONES;
    }

    public function timezoneForCountry(mixed $countryCode): ?string
    {
        if (! is_string($countryCode)) {
            return null;
        }

        return self::COUNTRY_TIMEZONES[strtoupper(trim($countryCode))] ?? null;
    }

    public function timezoneForLocation(mixed $countryCode, mixed $regionCode): ?string
    {
        if (! is_string($countryCode)) {
            return null;
        }

        $countryCode = strtoupper(trim($countryCode));
        $regions = $this->regionsForCountry($countryCode);

        if ($regions === []) {
            return $this->timezoneForCountry($countryCode);
        }

        if (! is_string($regionCode)) {
            return null;
        }

        return $regions[strtoupper(trim($regionCode))] ?? null;
    }

    public function countryForTimezone(?string $timezone): ?string
    {
        return $this->locationForTimezone($timezone)['country'] ?? null;
    }

    /**
     * @return array<string, string>
     */
    public function timezoneCountries(): array
    {
        $timezoneCountries = [];

        foreach (self::COUNTRY_TIMEZONES as $countryCode => $timezone) {
            $timezoneCountries[$timezone] = $countryCode;
        }

        foreach (self::REGION_TIMEZONES as $countryCode => $regions) {
            foreach ($regions as $timezone) {
                $timezoneCountries[$timezone] = $countryCode;
            }
        }

        return $timezoneCountries;
    }

    /**
     * @return array<string, array<string, string|null>>
     */
    public function timezoneLocations(): array
    {
        $timezoneLocations = [];

        foreach (self::COUNTRY_TIMEZONES as $countryCode => $timezone) {
            $timezoneLocations[$timezone] = [
                'country' => $countryCode,
                'region' => null,
            ];
        }

        foreach (self::REGION_TIMEZONES as $countryCode => $regions) {
            foreach ($regions as $regionCode => $timezone) {
                if (($timezoneLocations[$timezone]['region'] ?? null) !== null) {
                    continue;
                }

                $timezoneLocations[$timezone] = [
                    'country' => $countryCode,
                    'region' => $regionCode,
                ];
            }
        }

        return $timezoneLocations;
    }

    /**
     * @return array{country: string, region: string|null}|null
     */
    public function locationForTimezone(?string $timezone): ?array
    {
        if ($timezone === null) {
            return null;
        }

        return $this->timezoneLocations()[$timezone] ?? null;
    }

    /**
     * @return array<string, array<string, string>>
     */
    public function countryRegions(): array
    {
        return self::REGION_TIMEZONES;
    }

    /**
     * @return array<string, string>
     */
    public function regionsForCountry(mixed $countryCode): array
    {
        if (! is_string($countryCode)) {
            return [];
        }

        return self::REGION_TIMEZONES[strtoupper(trim($countryCode))] ?? [];
    }

    public function defaultRegionForCountry(mixed $countryCode): ?string
    {
        $regions = $this->regionsForCountry($countryCode);

        if ($regions === []) {
            return null;
        }

        return array_key_first($regions);
    }

    public function isSupportedRegion(mixed $countryCode, mixed $regionCode): bool
    {
        if (! is_string($regionCode)) {
            return false;
        }

        return array_key_exists(strtoupper(trim($regionCode)), $this->regionsForCountry($countryCode));
    }

    public function regionIsRequired(mixed $countryCode): bool
    {
        return $this->regionsForCountry($countryCode) !== [];
    }

    public function isSupportedCountry(mixed $countryCode): bool
    {
        return $this->timezoneForCountry($countryCode) !== null;
    }
}
