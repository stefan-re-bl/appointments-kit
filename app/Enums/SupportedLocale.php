<?php

declare(strict_types=1);

namespace App\Enums;

enum SupportedLocale: string
{
    case ES = 'es';
    case EN = 'en';

    public static function normalize(?string $locale): string
    {
        $locale = strtolower(trim((string) $locale));

        if (in_array($locale, self::values(), true)) {
            return $locale;
        }

        $fallback = strtolower((string) config('app.fallback_locale', self::ES->value));

        return in_array($fallback, self::values(), true)
            ? $fallback
            : self::ES->value;
    }

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_map(
            static fn (self $locale): string => $locale->value,
            self::cases(),
        );
    }
}
