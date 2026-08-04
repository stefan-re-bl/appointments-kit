<?php

declare(strict_types=1);

namespace App\Jobs\Notifications;

use App\Enums\NotificationDeliveryStatus;
use App\Models\NotificationDelivery;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

final class ProcessMetaWhatsAppWebhook implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly array $payload,
    ) {}

    public function handle(): void
    {
        foreach ($this->statuses() as $statusPayload) {
            $messageId = $statusPayload['id'] ?? null;
            $status = $statusPayload['status'] ?? null;

            if (! is_string($messageId) || ! is_string($status)) {
                continue;
            }

            $delivery = NotificationDelivery::query()
                ->where('provider_message_id', $messageId)
                ->first();

            if (! $delivery instanceof NotificationDelivery) {
                continue;
            }

            $this->applyStatus($delivery, $status, $statusPayload);
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function statuses(): array
    {
        $statuses = [];

        foreach (($this->payload['entry'] ?? []) as $entry) {
            foreach (($entry['changes'] ?? []) as $change) {
                foreach (($change['value']['statuses'] ?? []) as $status) {
                    if (is_array($status)) {
                        $statuses[] = $status;
                    }
                }
            }
        }

        return $statuses;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function applyStatus(NotificationDelivery $delivery, string $status, array $payload): void
    {
        $now = CarbonImmutable::now('UTC')->toDateTimeString();

        if ($status === 'read') {
            $delivery->forceFill([
                'status' => NotificationDeliveryStatus::READ,
                'read_at' => $delivery->read_at ?? $now,
                'delivered_at' => $delivery->delivered_at ?? $now,
            ])->save();

            return;
        }

        if ($status === 'delivered' && ! in_array($delivery->status, [NotificationDeliveryStatus::READ], true)) {
            $delivery->forceFill([
                'status' => NotificationDeliveryStatus::DELIVERED,
                'delivered_at' => $delivery->delivered_at ?? $now,
            ])->save();

            return;
        }

        if (in_array($status, ['sent', 'submitted'], true)
            && in_array($delivery->status, [NotificationDeliveryStatus::PENDING, NotificationDeliveryStatus::QUEUED], true)) {
            $delivery->forceFill([
                'status' => NotificationDeliveryStatus::SUBMITTED,
                'submitted_at' => $delivery->submitted_at ?? $now,
            ])->save();

            return;
        }

        if ($status === 'failed' && ! in_array($delivery->status, [
            NotificationDeliveryStatus::DELIVERED,
            NotificationDeliveryStatus::READ,
        ], true)) {
            $error = $payload['errors'][0] ?? [];

            $delivery->forceFill([
                'status' => NotificationDeliveryStatus::FAILED,
                'failed_at' => $now,
                'last_error_code' => is_array($error) && isset($error['code']) ? (string) $error['code'] : 'webhook_failed',
                'last_error_message' => is_array($error) && isset($error['title']) ? mb_substr((string) $error['title'], 0, 255) : 'Meta reported delivery failure.',
            ])->save();
        }
    }
}
