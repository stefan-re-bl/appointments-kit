<?php

declare(strict_types=1);

namespace App\Services\Notifications;

use App\Enums\AppointmentStatus;
use App\Enums\NotificationChannel;
use App\Enums\NotificationDeliveryStatus;
use App\Enums\NotificationEvent;
use App\Enums\NotificationProvider;
use App\Enums\NotificationRecipientType;
use App\Jobs\Notifications\SendWhatsAppTemplateMessage;
use App\Models\Appointment;
use App\Models\NotificationDelivery;
use Carbon\CarbonImmutable;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;

final readonly class WhatsAppDeliveryDispatcher
{
    public function __construct(
        private RecipientLocaleResolver $localeResolver,
    ) {}

    public function dispatchBookingConfirmed(Appointment $appointment): void
    {
        $appointment->loadMissing(['professional.user', 'sessionType']);

        $this->createAndDispatch($appointment, NotificationEvent::BOOKING_CONFIRMED, NotificationRecipientType::PATIENT);
        $this->createAndDispatch($appointment, NotificationEvent::BOOKING_CONFIRMED, NotificationRecipientType::PROFESSIONAL);
    }

    public function dispatchReminder(Appointment $appointment): void
    {
        $appointment->loadMissing(['professional.user', 'sessionType']);

        $this->createAndDispatch($appointment, NotificationEvent::APPOINTMENT_REMINDER, NotificationRecipientType::PATIENT);
        $this->createAndDispatch($appointment, NotificationEvent::APPOINTMENT_REMINDER, NotificationRecipientType::PROFESSIONAL);
    }

    public function skipPendingForAppointment(Appointment $appointment, NotificationEvent $event): void
    {
        NotificationDelivery::query()
            ->where('appointment_id', $appointment->getKey())
            ->where('event', $event->value)
            ->where('channel', NotificationChannel::WHATSAPP->value)
            ->whereIn('status', [
                NotificationDeliveryStatus::PENDING->value,
                NotificationDeliveryStatus::QUEUED->value,
            ])
            ->update([
                'status' => NotificationDeliveryStatus::SKIPPED->value,
                'failed_at' => CarbonImmutable::now('UTC')->toDateTimeString(),
                'last_error_code' => 'appointment_changed',
                'last_error_message' => 'Appointment status or schedule changed before delivery.',
            ]);
    }

    private function createAndDispatch(
        Appointment $appointment,
        NotificationEvent $event,
        NotificationRecipientType $recipientType,
    ): void {
        if (
            ! (bool) config('features.whatsapp', false)
            || ! (bool) config('services.meta_whatsapp.enabled', false)
        ) {
            return;
        }

        if ($appointment->status !== AppointmentStatus::CONFIRMED) {
            return;
        }

        $address = $this->recipientAddress($appointment, $event, $recipientType);

        if ($address === null) {
            return;
        }

        $locale = $this->localeResolver->resolve($appointment, $recipientType);

        try {
            $delivery = NotificationDelivery::query()->firstOrCreate(
                [
                    'appointment_id' => $appointment->getKey(),
                    'event' => $event->value,
                    'channel' => NotificationChannel::WHATSAPP->value,
                    'recipient_type' => $recipientType->value,
                    'event_version' => (int) $appointment->reschedule_count,
                ],
                [
                    'recipient_address' => $address,
                    'recipient_locale' => $locale,
                    'provider' => NotificationProvider::META->value,
                    'status' => NotificationDeliveryStatus::PENDING->value,
                    'metadata' => [
                        'starts_at_utc' => $appointment->starts_at?->utc()?->toDateTimeString(),
                    ],
                ]
            );
        } catch (QueryException $exception) {
            Log::warning('WhatsApp delivery idempotency collision.', [
                'appointment_id' => $appointment->getKey(),
                'event' => $event->value,
                'recipient_type' => $recipientType->value,
            ]);

            report($exception);

            return;
        }

        if ($delivery->status !== NotificationDeliveryStatus::PENDING) {
            return;
        }

        $claimed = NotificationDelivery::query()
            ->whereKey($delivery->getKey())
            ->where('status', NotificationDeliveryStatus::PENDING->value)
            ->update([
                'status' => NotificationDeliveryStatus::QUEUED->value,
                'queued_at' => CarbonImmutable::now('UTC')->toDateTimeString(),
            ]);

        if ($claimed !== 1) {
            return;
        }

        SendWhatsAppTemplateMessage::dispatch($delivery->getKey())->afterCommit();
    }

    private function recipientAddress(
        Appointment $appointment,
        NotificationEvent $event,
        NotificationRecipientType $recipientType,
    ): ?string {
        if ($recipientType === NotificationRecipientType::PATIENT) {
            if ($appointment->patient_whatsapp_opt_in_at === null || $appointment->patient_whatsapp_opt_out_at !== null) {
                return null;
            }

            return filled($appointment->patient_phone) ? (string) $appointment->patient_phone : null;
        }

        $professional = $appointment->professional;

        if (! $professional?->whatsapp_notifications_enabled) {
            return null;
        }

        if ($event === NotificationEvent::BOOKING_CONFIRMED && ! $professional->whatsapp_confirmations_enabled) {
            return null;
        }

        if ($event === NotificationEvent::APPOINTMENT_REMINDER && ! $professional->whatsapp_reminders_enabled) {
            return null;
        }

        return filled($professional->whatsapp_phone) ? (string) $professional->whatsapp_phone : null;
    }
}
