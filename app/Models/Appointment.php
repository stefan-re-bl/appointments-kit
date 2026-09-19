<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\AppointmentStatus;
use App\Enums\PaymentStatus;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Database\Factories\AppointmentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[Fillable([
    'professional_id',
    'session_type_id',
    'patient_name',
    'patient_email',
    'patient_phone',
    'patient_timezone',
    'patient_locale',
    'terms_accepted_at',
    'patient_whatsapp_opt_in_at',
    'patient_whatsapp_opt_out_at',
    'starts_at',
    'ends_at',
    'price',
    'currency',
])]
#[Hidden([
    'token',
])]
class Appointment extends Model
{
    /** @use HasFactory<AppointmentFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'terms_accepted_at' => 'datetime',
            'patient_whatsapp_opt_in_at' => 'datetime',
            'patient_whatsapp_opt_out_at' => 'datetime',
            'paid_at' => 'datetime',
            'reminder_sent_at' => 'datetime',
            'reminder_queued_at' => 'datetime',
            'reminder_failed_at' => 'datetime',
            'reminder_attempts' => 'integer',
            'status' => AppointmentStatus::class,
            'payment_status' => PaymentStatus::class,
            'price' => 'decimal:2',
            'reschedule_count' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Appointment $appointment): void {
            if (empty($appointment->token)) {
                $appointment->token = Str::uuid()->toString();
            }
        });
    }

    public function professional(): BelongsTo
    {
        return $this->belongsTo(Professional::class);
    }

    public function sessionType(): BelongsTo
    {
        return $this->belongsTo(SessionType::class);
    }

    public function notificationDeliveries(): HasMany
    {
        return $this->hasMany(NotificationDelivery::class);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', AppointmentStatus::PENDING->value);
    }

    public function scopeConfirmed(Builder $query): Builder
    {
        return $query->where('status', AppointmentStatus::CONFIRMED->value);
    }

    public function scopeCancelled(Builder $query): Builder
    {
        return $query->where('status', AppointmentStatus::CANCELLED->value);
    }

    public function scopeExpiredPending(Builder $query): Builder
    {
        return $query
            ->where('status', AppointmentStatus::PENDING->value)
            ->where('created_at', '<', CarbonImmutable::now('UTC')->subMinutes(
                max(1, (int) config('booking.pending_expiration_minutes', 15))
            ));
    }

    /**
     * Obtiene las citas que se solapan con un rango de tiempo dado.
     *
     * Fórmula:
     * existing_start < new_end AND existing_end > new_start
     *
     * Excluye citas canceladas porque no bloquean disponibilidad.
     * Puede excluir la misma cita al reprogramar para evitar falsos positivos.
     */
    public function scopeOverlappingSlot(
        Builder $query,
        int $professionalId,
        CarbonInterface|string $startsAt,
        CarbonInterface|string $endsAt,
        ?int $excludeAppointmentId = null,
    ): Builder {
        return $query
            ->where('professional_id', $professionalId)
            ->where('status', '!=', AppointmentStatus::CANCELLED->value)
            ->where('starts_at', '<', $endsAt)
            ->where('ends_at', '>', $startsAt)
            ->when(
                $excludeAppointmentId !== null,
                fn (Builder $query): Builder => $query->whereKeyNot($excludeAppointmentId)
            );
    }
}
