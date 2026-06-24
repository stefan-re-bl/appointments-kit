<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class QueueJobFailedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly string $connectionName,
        public readonly string $queueName,
        public readonly string $jobName,
        public readonly string $exceptionClass,
        public readonly string $exceptionMessage,
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
            ->subject(__('monitoring.queue_job_failed.subject'))
            ->line(__('monitoring.queue_job_failed.intro'))
            ->line(__('monitoring.connection', ['connection' => $this->connectionName]))
            ->line(__('monitoring.queue', ['queue' => $this->queueName]))
            ->line(__('monitoring.queue_job_failed.job', ['job' => $this->jobName]))
            ->line(__('monitoring.queue_job_failed.exception', ['exception' => $this->exceptionClass]))
            ->line(__('monitoring.queue_job_failed.message', ['message' => $this->exceptionMessage]));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'connection' => $this->connectionName,
            'queue' => $this->queueName,
            'job' => $this->jobName,
            'exception' => $this->exceptionClass,
            'message' => $this->exceptionMessage,
        ];
    }
}