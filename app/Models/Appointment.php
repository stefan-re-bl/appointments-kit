<?php

namespace App\Models;

use App\Enums\AppointmentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

#[Fillable([
    'therapist_id',
    'session_type_id',
    'patient_name',
    'patient_email',
    'patient_timezone',
    'starts_at',
    'ends_at',
    'status',
    'price',
    'currency',
    'payment_transaction_id',
    'payment_processed_at',
    'token',
    'reschedule_count',
])]
#[Hidden([
    'payment_transaction_id',
])]
class Appointment extends Model
{
    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'payment_processed_at' => 'datetime',
        'status' => AppointmentStatus::class,
    ];

    protected static function booted(): void
    {
        static::creating(function (Appointment $appointment) {
            if (empty($appointment->token)) {
                $appointment->token = Str::uuid()->toString();
            }
        });
    }

    public function therapist(): BelongsTo
    {
        return $this->belongsTo(Therapist::class);
    }

    public function sessionType(): BelongsTo
    {
        return $this->belongsTo(SessionType::class);
    }
}