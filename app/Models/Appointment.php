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
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

#[Fillable([
    'professional_id',
    'session_type_id',
    'service_id',
    'patient_name',
    'customer_name',
    'patient_email',
    'customer_email',
    'patient_phone',
    'customer_phone',
    'patient_timezone',
    'customer_timezone',
    'patient_locale',
    'customer_locale',
    'terms_accepted_at',
    'patient_whatsapp_opt_in_at',
    'customer_whatsapp_opt_in_at',
    'patient_whatsapp_opt_out_at',
    'customer_whatsapp_opt_out_at',
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
            'customer_whatsapp_opt_in_at' => 'datetime',
            'patient_whatsapp_opt_out_at' => 'datetime',
            'customer_whatsapp_opt_out_at' => 'datetime',
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

        static::saving(function (Appointment $appointment): void {
            if ($appointment->supportsCustomerColumns()) {
                $appointment->syncCustomerPair('name');
                $appointment->syncCustomerPair('email');
                $appointment->syncCustomerPair('phone');
                $appointment->syncCustomerPair('timezone');
                $appointment->syncCustomerPair('locale');
                $appointment->syncCustomerPair('whatsapp_opt_in_at');
                $appointment->syncCustomerPair('whatsapp_opt_out_at');
            }

            $appointment->syncServiceAlias();
        });
    }

    public function professional(): BelongsTo
    {
        return $this->belongsTo(Professional::class);
    }

    public function sessionType(): BelongsTo
    {
        return $this->service();
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class, 'service_id');
    }

    /**
     * @return Attribute<int|null, int|null>
     */
    protected function serviceId(): Attribute
    {
        return Attribute::make(
            get: fn (): ?int => isset($this->attributes['service_id'])
                ? (int) $this->attributes['service_id']
                : (isset($this->attributes['session_type_id']) ? (int) $this->attributes['session_type_id'] : null),
            set: fn (?int $value): array => $this->serviceIdAttributeSet($value),
        );
    }

    public function notificationDeliveries(): HasMany
    {
        return $this->hasMany(NotificationDelivery::class);
    }

    /**
     * @return Attribute<string|null, string|null>
     */
    protected function customerName(): Attribute
    {
        return Attribute::make(
            get: fn (): ?string => $this->attributes['customer_name'] ?? $this->attributes['patient_name'] ?? null,
            set: fn (?string $value): array => $this->customerAttributeSet('name', $value),
        );
    }

    /**
     * @return Attribute<string|null, string|null>
     */
    protected function customerEmail(): Attribute
    {
        return Attribute::make(
            get: fn (): ?string => $this->attributes['customer_email'] ?? $this->attributes['patient_email'] ?? null,
            set: fn (?string $value): array => $this->customerAttributeSet('email', $value),
        );
    }

    /**
     * @return Attribute<string|null, string|null>
     */
    protected function customerPhone(): Attribute
    {
        return Attribute::make(
            get: fn (): ?string => $this->attributes['customer_phone'] ?? $this->attributes['patient_phone'] ?? null,
            set: fn (?string $value): array => $this->customerAttributeSet('phone', $value),
        );
    }

    /**
     * @return Attribute<string|null, string|null>
     */
    protected function customerTimezone(): Attribute
    {
        return Attribute::make(
            get: fn (): ?string => $this->attributes['customer_timezone'] ?? $this->attributes['patient_timezone'] ?? null,
            set: fn (?string $value): array => $this->customerAttributeSet('timezone', $value),
        );
    }

    /**
     * @return Attribute<string|null, string|null>
     */
    protected function customerLocale(): Attribute
    {
        return Attribute::make(
            get: fn (): ?string => $this->attributes['customer_locale'] ?? $this->attributes['patient_locale'] ?? null,
            set: fn (?string $value): array => $this->customerAttributeSet('locale', $value),
        );
    }

    /**
     * @return Attribute<mixed, mixed>
     */
    protected function customerWhatsappOptInAt(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value): mixed => $value ?? $this->attributes['patient_whatsapp_opt_in_at'] ?? null,
            set: fn (mixed $value): array => $this->customerAttributeSet('whatsapp_opt_in_at', $value),
        );
    }

    /**
     * @return Attribute<mixed, mixed>
     */
    protected function customerWhatsappOptOutAt(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value): mixed => $value ?? $this->attributes['patient_whatsapp_opt_out_at'] ?? null,
            set: fn (mixed $value): array => $this->customerAttributeSet('whatsapp_opt_out_at', $value),
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function customerAttributeSet(string $field, mixed $value): array
    {
        $attributes = ["patient_{$field}" => $value];

        if ($this->supportsCustomerColumns()) {
            $attributes["customer_{$field}"] = $value;
        }

        return $attributes;
    }

    private function syncCustomerPair(string $field): void
    {
        $patientKey = "patient_{$field}";
        $customerKey = "customer_{$field}";

        $patientValue = $this->attributes[$patientKey] ?? null;
        $customerValue = $this->attributes[$customerKey] ?? null;

        if ($this->isDirty($patientKey) && $patientValue !== null) {
            $this->attributes[$customerKey] = $patientValue;

            return;
        }

        if ($this->isDirty($customerKey) && $customerValue !== null) {
            $this->attributes[$patientKey] = $customerValue;

            return;
        }

        if ($customerValue === null) {
            $this->attributes[$customerKey] = $patientValue;
        }
    }

    /**
     * @return array<string, int|null>
     */
    private function serviceIdAttributeSet(?int $value): array
    {
        $attributes = ['session_type_id' => $value];

        if ($this->supportsServiceAliasColumn()) {
            $attributes['service_id'] = $value;
        }

        return $attributes;
    }

    private function syncServiceAlias(): void
    {
        if (! $this->supportsServiceAliasColumn()) {
            return;
        }

        $sessionTypeId = $this->attributes['session_type_id'] ?? null;
        $serviceId = $this->attributes['service_id'] ?? null;

        if ($this->isDirty('session_type_id') && $sessionTypeId !== null) {
            $this->attributes['service_id'] = $sessionTypeId;

            return;
        }

        if ($this->isDirty('service_id') && $serviceId !== null) {
            $this->attributes['session_type_id'] = $serviceId;

            return;
        }

        if ($serviceId === null) {
            $this->attributes['service_id'] = $sessionTypeId;
        }
    }

    private function supportsCustomerColumns(): bool
    {
        static $supportsCustomerColumns = null;

        return $supportsCustomerColumns ??= Schema::hasColumn($this->getTable(), 'customer_name');
    }

    private function supportsServiceAliasColumn(): bool
    {
        static $supportsServiceAliasColumn = null;

        return $supportsServiceAliasColumn ??= Schema::hasColumn($this->getTable(), 'service_id');
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
