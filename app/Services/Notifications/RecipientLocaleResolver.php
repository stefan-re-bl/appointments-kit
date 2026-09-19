<?php

declare(strict_types=1);

namespace App\Services\Notifications;

use App\Enums\NotificationRecipientType;
use App\Enums\SupportedLocale;
use App\Models\Appointment;

final class RecipientLocaleResolver
{
    public function resolve(Appointment $appointment, NotificationRecipientType $recipientType): string
    {
        return match ($recipientType) {
            NotificationRecipientType::PATIENT => SupportedLocale::normalize($appointment->patient_locale),
            NotificationRecipientType::PROFESSIONAL => SupportedLocale::normalize($appointment->professional?->preferred_locale),
        };
    }
}
