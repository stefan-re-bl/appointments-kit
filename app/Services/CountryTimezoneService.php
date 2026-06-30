<?php

declare(strict_types=1);

namespace App\Services;

use Carbon\CarbonImmutable;
use DateTimeZone;

final class CountryTimezoneService
{
    /**
     * Default timezone per supported country. Region choices are not maintained
     * manually; they are computed from PHP/IANA for the selected booking date.
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
     * @return array<string, string>
     */
    public function countries(): array
    {
        return self::COUNTRY_TIMEZONES;
    }

    public function isSupportedCountry(mixed $countryCode): bool
    {
        return is_string($countryCode)
            && array_key_exists(strtoupper(trim($countryCode)), self::COUNTRY_TIMEZONES);
    }

    public function timezoneForCountry(mixed $countryCode): ?string
    {
        if (! is_string($countryCode)) {
            return null;
        }

        return self::COUNTRY_TIMEZONES[strtoupper(trim($countryCode))] ?? null;
    }

    /**
     * @return list<string>
     */
    public function timezonesForCountry(mixed $countryCode): array
    {
        if (! $this->isSupportedCountry($countryCode)) {
            return [];
        }

        $countryCode = strtoupper(trim((string) $countryCode));
        $timezones = DateTimeZone::listIdentifiers(DateTimeZone::PER_COUNTRY, $countryCode);

        if ($timezones === []) {
            $timezones = [self::COUNTRY_TIMEZONES[$countryCode]];
        }

        return array_values(array_unique($timezones));
    }

    /**
     * @return array<string, array{offset: int, label: string, timezones: list<string>}>
     */
    public function timezoneOptionsForCountryOnDate(mixed $countryCode, mixed $date): array
    {
        if (! $this->isSupportedCountry($countryCode)) {
            return [];
        }

        $countryCode = strtoupper(trim((string) $countryCode));
        $date = $this->bookingDate($date);
        $groups = [];

        foreach ($this->timezonesForCountry($countryCode) as $timezone) {
            $offset = $date->setTimezone(new DateTimeZone($timezone))->getOffset();
            $groups[$offset][] = $timezone;
        }

        ksort($groups);

        $options = [];

        foreach ($groups as $offset => $timezones) {
            sort($timezones);

            $timezone = in_array(self::COUNTRY_TIMEZONES[$countryCode], $timezones, true)
                ? self::COUNTRY_TIMEZONES[$countryCode]
                : $timezones[0];

            $options[$timezone] = [
                'offset' => (int) $offset,
                'label' => $this->formatTimezoneOptionLabel((int) $offset, $timezones),
                'timezones' => array_values($timezones),
            ];
        }

        return $options;
    }

    public function regionIsRequired(mixed $countryCode, mixed $date): bool
    {
        return count($this->timezoneOptionsForCountryOnDate($countryCode, $date)) > 1;
    }

    public function timezoneForLocation(mixed $countryCode, mixed $date, mixed $selectedTimezone): ?string
    {
        $options = $this->timezoneOptionsForCountryOnDate($countryCode, $date);

        if ($options === []) {
            return null;
        }

        if (count($options) === 1) {
            return array_key_first($options);
        }

        if (! is_string($selectedTimezone)) {
            return null;
        }

        $selectedTimezone = trim($selectedTimezone);

        return array_key_exists($selectedTimezone, $options) ? $selectedTimezone : null;
    }

    public function countryForTimezone(?string $timezone): ?string
    {
        if ($timezone === null) {
            return null;
        }

        foreach (array_keys(self::COUNTRY_TIMEZONES) as $countryCode) {
            if (in_array($timezone, $this->timezonesForCountry($countryCode), true)) {
                return $countryCode;
            }
        }

        return null;
    }

    /**
     * @return array<string, array{country: string}>
     */
    public function timezoneCountries(): array
    {
        $timezoneCountries = [];

        foreach (array_keys(self::COUNTRY_TIMEZONES) as $countryCode) {
            foreach ($this->timezonesForCountry($countryCode) as $timezone) {
                $timezoneCountries[$timezone] = ['country' => $countryCode];
            }
        }

        return $timezoneCountries;
    }

    /**
     * @return array<string, list<string>>
     */
    public function countryTimezones(): array
    {
        $countryTimezones = [];

        foreach (array_keys(self::COUNTRY_TIMEZONES) as $countryCode) {
            $countryTimezones[$countryCode] = $this->timezonesForCountry($countryCode);
        }

        return $countryTimezones;
    }

    private function bookingDate(mixed $date): CarbonImmutable
    {
        if (is_string($date) && $date !== '') {
            return CarbonImmutable::createFromFormat('Y-m-d H:i:s', "{$date} 12:00:00", 'UTC')
                ?: CarbonImmutable::now('UTC');
        }

        return CarbonImmutable::now('UTC')->setTime(12, 0);
    }

    /**
     * @param  list<string>  $timezones
     */
    private function formatTimezoneOptionLabel(int $offset, array $timezones): string
    {
        return $this->formatOffset($offset).' ('.$this->formatTimezoneSamples($timezones).')';
    }

    private function formatOffset(int $offset): string
    {
        $sign = $offset < 0 ? '-' : '+';
        $absoluteOffset = abs($offset);
        $hours = intdiv($absoluteOffset, 3600);
        $minutes = intdiv($absoluteOffset % 3600, 60);

        return sprintf('UTC%s%02d:%02d', $sign, $hours, $minutes);
    }

    /**
     * @param  list<string>  $timezones
     */
    private function formatTimezoneSamples(array $timezones): string
    {
        $samples = array_map(
            fn (string $timezone): string => str_replace('_', ' ', (string) str($timezone)->afterLast('/')),
            array_slice($timezones, 0, 3),
        );

        if (count($timezones) > 3) {
            $samples[] = '+'.(count($timezones) - 3);
        }

        return implode(', ', $samples);
    }
}
