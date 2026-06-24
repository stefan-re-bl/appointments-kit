<?php

declare(strict_types=1);

namespace App\Models;

use App\Rules\ValidTimezone;
use Database\Factories\TherapistFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use InvalidArgumentException;

#[Fillable(['timezone', 'google_meet_link', 'bio', 'is_active', 'avatar_url'])]
#[Hidden([])]
class Therapist extends Model
{
    /** @use HasFactory<TherapistFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function sessionTypes(): HasMany
    {
        return $this->hasMany(SessionType::class);
    }

    public function availabilities(): HasMany
    {
        return $this->hasMany(Availability::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function setTimezoneAttribute(string $value): void
    {
        $timezone = ValidTimezone::normalize($value);

        if ($timezone === null) {
            throw new InvalidArgumentException("La zona horaria '{$value}' no es válida en PHP.");
        }

        $this->attributes['timezone'] = $timezone;
    }
}