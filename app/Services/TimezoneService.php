<?php

declare(strict_types=1);

namespace App\Services;

use Carbon\Carbon;
use Carbon\CarbonInterface;

final class TimezoneService
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
    public function toUtc(string|CarbonInterface $datetime, ?string $fromTimezone = null): Carbon
    {
        $fromTimezone = $fromTimezone ?? $this->getCurrentTimezone();

        return Carbon::parse($datetime, $fromTimezone)->setTimezone('UTC');
    }

    /**
     * Convierte una fecha UTC (de la DB) a la zona horaria local del usuario
     */
    public function toLocal(string|CarbonInterface $datetime, ?string $toTimezone = null): Carbon
    {
        $toTimezone = $toTimezone ?? $this->getCurrentTimezone();

        return Carbon::parse($datetime, 'UTC')->setTimezone($toTimezone);
    }

    /**
     * Formatea una fecha UTC de la DB para mostrarse en la vista Blade
     * según la zona horaria del usuario actual.
     */
    public function formatForDisplay(
        string|CarbonInterface $datetime,
        string $format = 'd/m/Y H:i',
        ?string $toTimezone = null,
    ): string {
        return $this->toLocal($datetime, $toTimezone)->format($format);
    }

    /**
     * Convierte una hora local (HH:mm) a hora UTC (HH:mm:ss) para un día específico.
     *
     * La semana de referencia conserva la hora local recurrente. Al generar slots,
     * esa hora se aplica sobre la fecha real para que Carbon calcule el DST vigente.
     */
    public function timeToUtc(string|CarbonInterface $localTime, string $timezone, int $dayOfWeek): string
    {
        $date = $this->getReferenceDateForDay($dayOfWeek);
        $timeString = $localTime instanceof CarbonInterface ? $localTime->format('H:i') : $localTime;

        $datetime = Carbon::createFromFormat('Y-m-d H:i', $date.' '.$timeString, $timezone);
        $datetime->setTimezone('UTC');

        return $datetime->format('H:i:s');
    }

    /**
     * Convierte una hora UTC (HH:mm:ss) a hora local (HH:mm) para un día específico.
     *
     * Este método revierte la conversión hecha por timeToUtc() y recupera la hora
     * de pared configurada por la terapeuta, no el offset de una fecha futura.
     */
    public function timeToLocal(string|CarbonInterface $utcTime, string $timezone, int $dayOfWeek): string
    {
        $date = $this->getReferenceDateForDay($dayOfWeek);
        $timeString = $utcTime instanceof CarbonInterface ? $utcTime->format('H:i:s') : $utcTime;

        $datetime = Carbon::createFromFormat('Y-m-d H:i:s', $date.' '.$timeString, 'UTC');
        $datetime->setTimezone($timezone);

        return $datetime->format('H:i');
    }

    /**
     * Helper para obtener una fecha estándar asociada al día de la semana.
     */
    private function getReferenceDateForDay(int $dayOfWeek): string
    {
        return match ($dayOfWeek) {
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
