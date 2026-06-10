<?php

namespace App\Models;

use App\Enums\AppointmentStatus;
use App\Enums\PaymentStatus;
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
    'payment_status', // Nuevo campo
    'paid_at',        // Nuevo campo
    'token',
    'reschedule_count',
])]
#[Hidden([
    'token', // Movido aquí por seguridad (evita que se filtre en arrays/JSON masivos)
])]
class Appointment extends Model
{
    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'paid_at' => 'datetime',              // Nuevo cast
        'status' => AppointmentStatus::class,
        'payment_status' => PaymentStatus::class, // Nuevo cast con Enum
        'price' => 'decimal:2',               // Añadido para precisión monetaria
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