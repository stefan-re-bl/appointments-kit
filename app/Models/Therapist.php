<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory; // <-- Import añadido
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['user_id', 'timezone', 'google_meet_link', 'bio', 'is_active', 'avatar_url'])]
#[Hidden([])]
class Therapist extends Model
{
    use HasFactory; // <-- Trait añadido

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    // Relación inversa: Un terapeuta pertenece a un usuario
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
        public function sessionTypes(): HasMany
    {
        return $this->hasMany(SessionType::class);
    }
    // Mutador para garantizar a nivel de modelo que el timezone sea válido
    // (Cumple el criterio de aceptación de rechazar valores inválidos)
    public function setTimezoneAttribute($value)
    {
        if (!in_array($value, timezone_identifiers_list())) {
            throw new \InvalidArgumentException("La zona horaria '{$value}' no es válida en PHP.");
        }
        $this->attributes['timezone'] = $value;
    }
    public function availabilities(): HasMany
    {
        return $this->hasMany(Availability::class);
    }
}