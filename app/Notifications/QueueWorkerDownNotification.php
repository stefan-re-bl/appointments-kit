<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class QueueWorkerDownNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly string $connectionName,
        public readonly string $queueName,
        public readonly ?string $lastHeartbeat,
        public readonly int $staleAfterMinutes,
        public readonly string $reason,
    ) {
        //
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('monitoring.queue_worker_down.subject'))
            ->line(__('monitoring.queue_worker_down.intro'))
            ->line(__('monitoring.connection', ['connection' => $this->connectionName]))
            ->line(__('monitoring.queue', ['queue' => $this->queueName]))
            ->line(__('monitoring.queue_worker_down.reason', ['reason' => $this->reason]))
            ->line(__('monitoring.queue_worker_down.last_heartbeat', [
                'last_heartbeat' => $this->lastHeartbeat ?? __('monitoring.none'),
            ]))
            ->line(__('monitoring.queue_worker_down.stale_after', [
                'minutes' => $this->staleAfterMinutes,
            ]));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'connection' => $this->connectionName,
            'queue' => $this->queueName,
            'last_heartbeat' => $this->lastHeartbeat,
            'stale_after_minutes' => $this->staleAfterMinutes,
            'reason' => $this->reason,
        ];
    }
}