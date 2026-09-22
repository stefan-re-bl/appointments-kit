<?php

declare(strict_types=1);

namespace App\Models;

use App\Rules\ValidTimezone;
use Database\Factories\ProfessionalFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use InvalidArgumentException;

#[Fillable([
    'slug',
    'timezone',
    'google_meet_link',
    'whatsapp_phone',
    'whatsapp_notifications_enabled',
    'whatsapp_confirmations_enabled',
    'whatsapp_reminders_enabled',
    'preferred_locale',
    'bio',
    'specialties',
    'professional_approach',
    'therapeutic_approach',
    'payment_instructions',
    'is_active',
    'is_approved',
    'avatar_url',
    'presentation_video_url',
])]
#[Hidden([])]
class Professional extends Model
{
    /** @use HasFactory<ProfessionalFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_approved' => 'boolean',
            'whatsapp_notifications_enabled' => 'boolean',
            'whatsapp_confirmations_enabled' => 'boolean',
            'whatsapp_reminders_enabled' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Professional $professional): void {
            if (filled($professional->slug)) {
                return;
            }

            $professional->slug = static::uniqueSlugForName($professional->userNameForSlug());
        });
    }

    public function scopePubliclyBookable(Builder $query): Builder
    {
        return $query
            ->where('is_active', true)
            ->where('is_approved', true)
            ->whereNotNull('google_meet_link')
            ->where('google_meet_link', '<>', '')
            ->whereHas('user', fn (Builder $query) => $query->where('role', 'professional'));
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function sessionTypes(): HasMany
    {
        return $this->services();
    }

    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }

    public function availabilities(): HasMany
    {
        return $this->hasMany(Availability::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    /**
     * @return Attribute<string|null, string|null>
     */
    protected function professionalApproach(): Attribute
    {
        return Attribute::make(
            get: fn (): ?string => $this->attributes['professional_approach']
                ?? $this->attributes['therapeutic_approach']
                ?? null,
            set: fn (?string $value): array => [
                'professional_approach' => $value,
                'therapeutic_approach' => $value,
            ],
        );
    }

    /**
     * @return Attribute<string|null, string|null>
     */
    protected function therapeuticApproach(): Attribute
    {
        return Attribute::make(
            get: fn (): ?string => $this->attributes['therapeutic_approach']
                ?? $this->attributes['professional_approach']
                ?? null,
            set: fn (?string $value): array => [
                'therapeutic_approach' => $value,
                'professional_approach' => $value,
            ],
        );
    }

    public function setTimezoneAttribute(string $value): void
    {
        $timezone = ValidTimezone::normalize($value);

        if ($timezone === null) {
            throw new InvalidArgumentException("La zona horaria '{$value}' no es válida en PHP.");
        }

        $this->attributes['timezone'] = $timezone;
    }

    private function userNameForSlug(): string
    {
        $loadedUser = $this->relationLoaded('user') ? $this->user : null;

        if ($loadedUser instanceof User) {
            return $loadedUser->name;
        }

        if ($this->user_id === null) {
            return 'professional';
        }

        return User::query()->whereKey($this->user_id)->value('name') ?? 'professional';
    }

    private static function uniqueSlugForName(string $name): string
    {
        $baseSlug = Str::slug($name) ?: 'professional';
        $slug = $baseSlug;
        $counter = 2;

        while (static::query()->where('slug', $slug)->exists()) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        return $slug;
    }
}
