<?php

declare(strict_types=1);

namespace App\Services\Notifications;

use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;

final class PhoneNumberNormalizer
{
    public function normalize(?string $phone): ?string
    {
        $phone = trim((string) $phone);

        if ($phone === '') {
            return null;
        }

        $util = PhoneNumberUtil::getInstance();

        try {
            $number = $util->parse($phone, null);
        } catch (NumberParseException) {
            return null;
        }

        if (! $util->isValidNumber($number)) {
            return null;
        }

        return $util->format($number, PhoneNumberFormat::E164);
    }
}
