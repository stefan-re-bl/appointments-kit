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

    /**
     * Convierte una hora local (HH:mm) a hora UTC (HH:mm:ss) para un día específico.
     */
    public function timeToUtc(string|Carbon $localTime, string $timezone, int $dayOfWeek): string
    {
        $date = $this->getReferenceDateForDay($dayOfWeek);
        $timeString = $localTime instanceof Carbon ? $localTime->format('H:i') : $localTime;
        
        $datetime = Carbon::createFromFormat('Y-m-d H:i', $date . ' ' . $timeString, $timezone);
        $datetime->setTimezone('UTC');
        
        return $datetime->format('H:i:s');
    }

    /**
     * Convierte una hora UTC (HH:mm:ss) a hora local (HH:mm) para un día específico.
     */
    public function timeToLocal(string|Carbon $utcTime, string $timezone, int $dayOfWeek): string
    {
        $date = $this->getReferenceDateForDay($dayOfWeek);
        $timeString = $utcTime instanceof Carbon ? $utcTime->format('H:i:s') : $utcTime;
        
        $datetime = Carbon::createFromFormat('Y-m-d H:i:s', $date . ' ' . $timeString, 'UTC');
        $datetime->setTimezone($timezone);
        
        return $datetime->format('H:i');
    }

    /**
     * Helper para obtener una fecha estándar asociada al día de la semana.
     */
    private function getReferenceDateForDay(int $dayOfWeek): string
    {
        return match($dayOfWeek) {
            1 => '2024-01-01',
            2 => '2024-01-02',
            3 => '2024-01-03',
            4 => '2024-01-04',
            5 => '2024-01-05',
            6 => '2024-01-06',
            7 => '2024-01-07',
            default => '2024-01-01',
        };
    }
}