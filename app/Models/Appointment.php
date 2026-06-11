<?php

namespace App\Models;

use App\Enums\AppointmentStatus;
use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
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
    'payment_status',
    'paid_at',
    'token',
    'reschedule_count',
])]
#[Hidden([
    'token',
])]
class Appointment extends Model
{
    use HasFactory;

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'paid_at' => 'datetime',
        'status' => AppointmentStatus::class,
        'payment_status' => PaymentStatus::class,
        'price' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::creating(function (Appointment $appointment) {
            if (empty($appointment->token)) {
                $appointment->token = Str::uuid()->toString();
            }
        });
    }

    // --- RELACIONES ---

    public function therapist(): BelongsTo
    {
        return $this->belongsTo(Therapist::class);
    }

    public function sessionType(): BelongsTo
    {
        return $this->belongsTo(SessionType::class);
    }

    // --- SCOPES (TICKET #9) ---

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', AppointmentStatus::PENDING);
    }

    public function scopeConfirmed(Builder $query): Builder
    {
        return $query->where('status', AppointmentStatus::CONFIRMED);
    }

    public function scopeCancelled(Builder $query): Builder
    {
        return $query->where('status', AppointmentStatus::CANCELLED);
    }

    public function scopeExpiredPending(Builder $query): Builder
    {
        // Las citas pendientes que llevan más de 15 minutos sin confirmar
        return $query->where('status', AppointmentStatus::PENDING)
                     ->where('created_at', '<', now()->subMinutes(15));
    }

    /**
     * Scope clave: Obtiene las citas que se solapan con un rango de tiempo dado.
     * Excluye las canceladas ya que no ocupan lugar físico en la agenda.
     * Es utilizado por el BookingService junto con lockForUpdate() para prevenir race conditions.
     */
    public function scopeOverlappingSlot(Builder $query, int $therapistId, string $startsAt, string $endsAt): Builder
    {
        return $query->where('therapist_id', $therapistId)
                     ->where('status', '!=', AppointmentStatus::CANCELLED)
                     ->where('starts_at', '<', $endsAt)
                     ->where('ends_at', '>', $startsAt);
    }
}