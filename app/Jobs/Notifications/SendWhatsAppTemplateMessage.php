<?php

declare(strict_types=1);

namespace App\Jobs\Notifications;

use App\Enums\AppointmentStatus;
use App\Enums\NotificationDeliveryStatus;
use App\Enums\NotificationEvent;
use App\Enums\NotificationRecipientType;
use App\Models\NotificationDelivery;
use App\Services\Notifications\RecipientLocaleResolver;
use App\Services\Notifications\WhatsAppGateway;
use App\Services\Notifications\WhatsAppTemplateRegistry;
use App\Services\Notifications\WhatsAppVariableBuilder;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use RuntimeException;
use Throwable;

final class SendWhatsAppTemplateMessage implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 60;

    public function __construct(
        private readonly int $notificationDeliveryId,
    ) {}

    /**
     * @return array<int, int>
     */
    public function backoff(): array
    {
        return [60, 300, 900];
    }

    public function handle(
        RecipientLocaleResolver $localeResolver,
        WhatsAppVariableBuilder $variableBuilder,
        WhatsAppTemplateRegistry $templateRegistry,
        WhatsAppGateway $gateway,
    ): void {
        $delivery = NotificationDelivery::query()
            ->with(['appointment.professional.user', 'appointment.service'])
            ->find($this->notificationDeliveryId);

        if (! $delivery instanceof NotificationDelivery) {
            return;
        }

        if (! in_array($delivery->status, [NotificationDeliveryStatus::QUEUED, NotificationDeliveryStatus::PENDING], true)) {
            return;
        }

        $appointment = $delivery->appointment;

        if (! $appointment || $appointment->status !== AppointmentStatus::CONFIRMED) {
            $this->markSkipped($delivery, 'appointment_not_confirmed', 'Appointment is no longer confirmed.');

            return;
        }

        if ((int) $appointment->reschedule_count !== (int) $delivery->event_version) {
            $this->markSkipped($delivery, 'stale_event_version', 'Appointment was rescheduled before delivery.');

            return;
        }

        $recipientType = $delivery->recipient_type;
        $event = $delivery->event;

        if (! $recipientType instanceof NotificationRecipientType || ! $event instanceof NotificationEvent) {
            $this->markFailed($delivery, 'invalid_delivery', 'Notification delivery has invalid enum values.');

            return;
        }

        if (! $this->recipientStillAllowsDelivery($delivery)) {
            $this->markSkipped($delivery, 'recipient_disabled', 'Recipient no longer allows this WhatsApp delivery.');

            return;
        }

        $locale = $localeResolver->resolve($appointment, $recipientType);

        if ($locale !== $delivery->recipient_locale) {
            $this->markSkipped($delivery, 'locale_snapshot_changed', 'Recipient locale no longer matches the delivery snapshot.');

            return;
        }

        $delivery->forceFill([
            'attempts' => ((int) $delivery->attempts) + 1,
        ])->save();

        try {
            $variables = $variableBuilder->build($appointment, $event, $recipientType, $locale);
            $template = $templateRegistry->resolve($event, $recipientType, $locale, $variables);
            $result = $gateway->sendTemplate((string) $delivery->recipient_address, $template);
        } catch (Throwable $exception) {
            $this->markFailed($delivery, 'template_build_failed', $exception->getMessage());

            return;
        }

        if ($result->successful) {
            $delivery->forceFill([
                'provider_message_id' => $result->providerMessageId,
                'status' => NotificationDeliveryStatus::SUBMITTED,
                'submitted_at' => CarbonImmutable::now('UTC')->toDateTimeString(),
                'failed_at' => null,
                'last_error_code' => null,
                'last_error_message' => null,
            ])->save();

            return;
        }

        if ($result->retryable && $delivery->attempts < $this->tries) {
            $delivery->forceFill([
                'status' => NotificationDeliveryStatus::QUEUED,
                'last_error_code' => $result->errorCode ?? 'retryable_send_failed',
                'last_error_message' => mb_substr($result->errorMessage ?? 'Retryable WhatsApp send failed.', 0, 255),
            ])->save();

            throw new RuntimeException($result->errorMessage ?? 'Retryable WhatsApp send failed.');
        }

        $this->markFailed($delivery, $result->errorCode ?? 'send_failed', $result->errorMessage ?? 'WhatsApp send failed.');
    }

    public function failed(?Throwable $exception): void
    {
        $delivery = NotificationDelivery::query()->find($this->notificationDeliveryId);

        if (! $delivery instanceof NotificationDelivery || $delivery->status === NotificationDeliveryStatus::SUBMITTED) {
            return;
        }

        $this->markFailed($delivery, 'job_failed', $exception?->getMessage() ?? 'WhatsApp delivery job failed.');
    }

    private function recipientStillAllowsDelivery(NotificationDelivery $delivery): bool
    {
        $appointment = $delivery->appointment;

        if (! $appointment) {
            return false;
        }

        if ($delivery->recipient_type === NotificationRecipientType::CUSTOMER) {
            return $appointment->customer_whatsapp_opt_in_at !== null
                && $appointment->customer_whatsapp_opt_out_at === null
                && filled($appointment->customer_phone);
        }

        $professional = $appointment->professional;

        if (! $professional?->whatsapp_notifications_enabled || ! filled($professional->whatsapp_phone)) {
            return false;
        }

        if ($delivery->event === NotificationEvent::BOOKING_CONFIRMED) {
            return (bool) $professional->whatsapp_confirmations_enabled;
        }

        return (bool) $professional->whatsapp_reminders_enabled;
    }

    private function markSkipped(NotificationDelivery $delivery, string $code, string $message): void
    {
        $delivery->forceFill([
            'status' => NotificationDeliveryStatus::SKIPPED,
            'failed_at' => CarbonImmutable::now('UTC')->toDateTimeString(),
            'last_error_code' => $code,
            'last_error_message' => $message,
        ])->save();
    }

    private function markFailed(NotificationDelivery $delivery, string $code, string $message): void
    {
        $delivery->forceFill([
            'status' => NotificationDeliveryStatus::FAILED,
            'failed_at' => CarbonImmutable::now('UTC')->toDateTimeString(),
            'last_error_code' => $code,
            'last_error_message' => mb_substr($message, 0, 255),
        ])->save();
    }
}
