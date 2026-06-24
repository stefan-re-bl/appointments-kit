<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class QueueBusyNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly string $connectionName,
        public readonly string $queueName,
        public readonly int $size,
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
            ->subject(__('monitoring.queue_busy.subject'))
            ->line(__('monitoring.queue_busy.intro'))
            ->line(__('monitoring.connection', ['connection' => $this->connectionName]))
            ->line(__('monitoring.queue', ['queue' => $this->queueName]))
            ->line(__('monitoring.queue_busy.size', ['size' => $this->size]));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'connection' => $this->connectionName,
            'queue' => $this->queueName,
            'size' => $this->size,
        ];
    }
}