<?php

declare(strict_types=1);

namespace App\Services\Notifications;

use App\Enums\NotificationEvent;
use App\Enums\NotificationRecipientType;
use App\Enums\SupportedLocale;
use InvalidArgumentException;

final class WhatsAppTemplateRegistry
{
    /**
     * @param  array<int, string>  $variables
     */
    public function resolve(
        NotificationEvent $event,
        NotificationRecipientType $recipientType,
        string $locale,
        array $variables,
    ): WhatsAppTemplate {
        $locale = SupportedLocale::normalize($locale);
        $key = $this->templateKey($event, $recipientType, $locale);
        $templateName = trim((string) config("services.meta_whatsapp.templates.{$key}"));
        $languageCode = trim((string) config("services.meta_whatsapp.language_codes.{$locale}"));

        if ($templateName === '') {
            throw new InvalidArgumentException("Missing Meta WhatsApp template configuration for {$key}.");
        }

        if ($languageCode === '') {
            throw new InvalidArgumentException("Missing Meta WhatsApp language code configuration for {$locale}.");
        }

        return new WhatsAppTemplate($templateName, $languageCode, $variables);
    }

    public function templateKey(NotificationEvent $event, NotificationRecipientType $recipientType, string $locale): string
    {
        return match ([$event, $recipientType, SupportedLocale::normalize($locale)]) {
            [NotificationEvent::BOOKING_CONFIRMED, NotificationRecipientType::PATIENT, 'es'] => 'patient_confirmation_es',
            [NotificationEvent::BOOKING_CONFIRMED, NotificationRecipientType::PATIENT, 'en'] => 'patient_confirmation_en',
            [NotificationEvent::BOOKING_CONFIRMED, NotificationRecipientType::PROFESSIONAL, 'es'] => 'professional_confirmation_es',
            [NotificationEvent::BOOKING_CONFIRMED, NotificationRecipientType::PROFESSIONAL, 'en'] => 'professional_confirmation_en',
            [NotificationEvent::APPOINTMENT_REMINDER, NotificationRecipientType::PATIENT, 'es'] => 'patient_reminder_es',
            [NotificationEvent::APPOINTMENT_REMINDER, NotificationRecipientType::PATIENT, 'en'] => 'patient_reminder_en',
            [NotificationEvent::APPOINTMENT_REMINDER, NotificationRecipientType::PROFESSIONAL, 'es'] => 'professional_reminder_es',
            [NotificationEvent::APPOINTMENT_REMINDER, NotificationRecipientType::PROFESSIONAL, 'en'] => 'professional_reminder_en',
            default => throw new InvalidArgumentException('Invalid WhatsApp template combination.'),
        };
    }
}
