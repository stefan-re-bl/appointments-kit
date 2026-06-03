<?php

namespace App\Services;

use Carbon\Carbon;

class TimezoneService
{
    /**
     * Obtiene la zona horaria del usuario actual desde el Singleton
     */
    protected function getCurrentTimezone(): string
    {
        return app('user.timezone') ?? config('app.timezone');
    }

    /**
     * Convierte una fecha/hora local (ej. la que manda un form) a UTC para guardar en DB
     */
    public function toUtc(string|Carbon $datetime, ?string $fromTimezone = null): Carbon
    {
        $fromTimezone = $fromTimezone ?? $this->getCurrentTimezone();
        
        return Carbon::parse($datetime, $fromTimezone)->setTimezone('UTC');
    }

    /**
     * Convierte una fecha UTC (de la DB) a la zona horaria local del usuario
     */
    public function toLocal(string|Carbon $datetime, ?string $toTimezone = null): Carbon
    {
        $toTimezone = $toTimezone ?? $this->getCurrentTimezone();
        
        return Carbon::parse($datetime, 'UTC')->setTimezone($toTimezone);
    }

    /**
     * Formatea una fecha UTC de la DB para mostrarse en la vista Blade
     * según la zona horaria del usuario actual.
     */
    public function formatForDisplay(string|Carbon $datetime, string $format = 'd/m/Y H:i', ?string $toTimezone = null): string
    {
        return $this->toLocal($datetime, $toTimezone)->format($format);
    }
}