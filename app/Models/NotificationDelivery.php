<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\NotificationChannel;
use App\Enums\NotificationDeliveryStatus;
use App\Enums\NotificationEvent;
use App\Enums\NotificationProvider;
use App\Enums\NotificationRecipientType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'appointment_id',
    'event',
    'channel',
    'recipient_type',
    'recipient_address',
    'recipient_locale',
    'provider',
    'provider_message_id',
    'status',
    'event_version',
    'attempts',
    'queued_at',
    'submitted_at',
    'delivered_at',
    'read_at',
    'failed_at',
    'last_error_code',
    'last_error_message',
    'metadata',
])]
#[Hidden([
    'recipient_address',
])]
final class NotificationDelivery extends Model
{
    protected function casts(): array
    {
        return [
            'event' => NotificationEvent::class,
            'channel' => NotificationChannel::class,
            'recipient_type' => NotificationRecipientType::class,
            'provider' => NotificationProvider::class,
            'status' => NotificationDeliveryStatus::class,
            'event_version' => 'integer',
            'attempts' => 'integer',
            'queued_at' => 'datetime',
            'submitted_at' => 'datetime',
            'delivered_at' => 'datetime',
            'read_at' => 'datetime',
            'failed_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }
}
