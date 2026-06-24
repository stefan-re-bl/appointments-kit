<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use RuntimeException;

#[Fillable([
    'appointment_id',
    'causer_type',
    'causer_id',
    'event',
    'old_values',
    'new_values',
    'metadata',
    'ip_address',
    'user_agent',
    'created_at',
])]
class ActivityLog extends Model
{
    public const string EVENT_APPOINTMENT_STATUS_CHANGED = 'appointment.status_changed';

    public const string EVENT_APPOINTMENT_PAYMENT_UPDATED = 'appointment.payment_updated';

    public $timestamps = false;

    protected static function booted(): void
    {
        static::updating(function (): never {
            throw new RuntimeException('Activity logs are immutable and cannot be updated.');
        });

        static::deleting(function (): never {
            throw new RuntimeException('Activity logs are immutable and cannot be deleted.');
        });
    }

    protected function casts(): array
    {
        return [
            'old_values' => 'array',
            'new_values' => 'array',
            'metadata' => 'array',
            'created_at' => 'immutable_datetime',
        ];
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function causer(): MorphTo
    {
        return $this->morphTo();
    }
}