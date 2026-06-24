<?php

declare(strict_types=1);

namespace App\Rules;

use Closure;
use DateTimeZone;
use Illuminate\Contracts\Validation\ValidationRule;
use IntlTimeZone;

final class ValidTimezone implements ValidationRule
{
    /**
     * Compatibility aliases for timezone IDs that browsers may return
     * but the PHP runtime may not support directly.
     *
     * @var array<string, string>
     */
    private const COMPATIBILITY_ALIASES = [
        'America/Buenos_Aires' => 'America/Argentina/Buenos_Aires',
        'America/Catamarca' => 'America/Argentina/Catamarca',
        'America/Cordoba' => 'America/Argentina/Cordoba',
        'America/Jujuy' => 'America/Argentina/Jujuy',
        'America/Mendoza' => 'America/Argentina/Mendoza',
        'America/Rosario' => 'America/Argentina/Cordoba',
    ];

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (self::normalize($value) === null) {
            $fail('La zona horaria :attribute no es válida.');
        }
    }

    public static function isValid(mixed $value): bool
    {
        return self::normalize($value) !== null;
    }

    public static function normalize(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $timezone = trim($value);

        if ($timezone === '') {
            return null;
        }

        if (self::isSupportedByPhp($timezone)) {
            return $timezone;
        }

        $canonicalTimezone = self::canonicalizeWithIntl($timezone);

        if ($canonicalTimezone !== null && self::isSupportedByPhp($canonicalTimezone)) {
            return $canonicalTimezone;
        }

        $compatibilityAlias = self::COMPATIBILITY_ALIASES[$timezone] ?? null;

        if ($compatibilityAlias !== null && self::isSupportedByPhp($compatibilityAlias)) {
            return $compatibilityAlias;
        }

        return null;
    }

    private static function isSupportedByPhp(string $timezone): bool
    {
        return in_array($timezone, DateTimeZone::listIdentifiers(), true);
    }

    private static function canonicalizeWithIntl(string $timezone): ?string
    {
        if (! class_exists(IntlTimeZone::class)) {
            return null;
        }

        $isSystemId = false;
        $canonicalTimezone = IntlTimeZone::getCanonicalID($timezone, $isSystemId);

        if (! is_string($canonicalTimezone) || $canonicalTimezone === '' || ! $isSystemId) {
            return null;
        }

        return $canonicalTimezone;
    }
}