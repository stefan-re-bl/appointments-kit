<?php

declare(strict_types=1);

namespace App\Services\Notifications;

use App\Enums\NotificationEvent;
use App\Enums\NotificationRecipientType;
use App\Models\Appointment;
use App\Services\TimezoneService;
use Illuminate\Support\Facades\App;

final readonly class WhatsAppVariableBuilder
{
    public function __construct(
        private TimezoneService $timezoneService,
    ) {}

    /**
     * @return array<int, string>
     */
    public function build(Appointment $appointment, NotificationEvent $event, NotificationRecipientType $recipientType, string $locale): array
    {
        $previousLocale = App::getLocale();
        App::setLocale($locale);

        try {
            $timezone = $recipientType === NotificationRecipientType::PATIENT
                ? ($appointment->patient_timezone ?: 'UTC')
                : ($appointment->therapist?->timezone ?: 'UTC');
            $date = $this->timezoneService->toLocal($appointment->starts_at, $timezone)->translatedFormat('d/m/Y');
            $time = $this->timezoneService->formatForDisplay($appointment->starts_at, 'H:i', $timezone);

            if ($event === NotificationEvent::BOOKING_CONFIRMED && $recipientType === NotificationRecipientType::PATIENT) {
                return [
                    $appointment->patient_name,
                    $date,
                    $time,
                    route('appointments.public.show', $appointment->token),
                ];
            }

            if ($event === NotificationEvent::BOOKING_CONFIRMED && $recipientType === NotificationRecipientType::THERAPIST) {
                return [
                    $appointment->patient_name,
                    (string) $appointment->sessionType?->name,
                    $date,
                    $time,
                    route('therapist.appointments.index'),
                ];
            }

            if ($event === NotificationEvent::APPOINTMENT_REMINDER && $recipientType === NotificationRecipientType::PATIENT) {
                return [
                    $appointment->patient_name,
                    $time,
                    route('appointments.public.show', $appointment->token),
                ];
            }

            return [
                $appointment->patient_name,
                $time,
                route('therapist.appointments.index'),
            ];
        } finally {
            App::setLocale($previousLocale);
        }
    }
}
