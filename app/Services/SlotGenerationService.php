<?php

namespace App\Services;

use App\Enums\AppointmentStatus;
use App\Models\Professional;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class SlotGenerationService
{
    public function __construct(
        private TimezoneService $timezoneService
    ) {}

    /**
     * Genera los slots disponibles para un profesional en una fecha específica.
     *
     * @param  string  $date  Fecha en formato Y-m-d
     * @param  int  $durationMinutes  Duración de la sesión
     * @param  string|null  $displayTimezone  Zona horaria para formatear labels; usa la del profesional si no se indica.
     */
    public function generate(
        Professional $professional,
        string $date,
        int $durationMinutes,
        ?string $displayTimezone = null,
    ): array {
        if (! $professional->is_active || ! $professional->is_approved) {
            return [];
        }

        $professionalTimezone = $professional->timezone;
        $displayTimezone ??= $professionalTimezone;

        // 1. Crear la fecha solicitada en la zona horaria del profesional.
        $dateCarbon = Carbon::parse($date, $professionalTimezone);
        $dayOfWeek = $dateCarbon->isoWeekday(); // 1=Lunes, 7=Domingo (ISO-8601)

        // 2. Obtener disponibilidades para ese día
        $availabilities = $professional->availabilities()
            ->where('day_of_week', $dayOfWeek)
            ->where('is_active', true)
            ->get();

        if ($availabilities->isEmpty()) {
            return [];
        }

        // 3. Obtener citas existentes para ese día (en UTC) que no estén canceladas
        $dayStartUtc = $dateCarbon->copy()->startOfDay()->setTimezone('UTC');
        $dayEndUtc = $dateCarbon->copy()->endOfDay()->setTimezone('UTC');

        $existingAppointments = $professional->appointments()
            ->whereIn('status', [AppointmentStatus::PENDING, AppointmentStatus::CONFIRMED, AppointmentStatus::COMPLETED])
            ->where('starts_at', '<', $dayEndUtc)
            ->where('ends_at', '>', $dayStartUtc)
            ->get();

        // 4. Generar slots
        $slots = [];

        foreach ($availabilities as $availability) {
            // Convertir la hora UTC de la BD a la hora local del profesional para esa fecha.
            $localStartTime = $this->timezoneService->timeToLocal($availability->start_time, $professionalTimezone, (int) $dayOfWeek);
            $localEndTime = $this->timezoneService->timeToLocal($availability->end_time, $professionalTimezone, (int) $dayOfWeek);

            // Crear ventanas de tiempo en UTC
            $workingStartUtc = Carbon::parse("{$date} {$localStartTime}", $professionalTimezone)->setTimezone('UTC');
            $workingEndUtc = Carbon::parse("{$date} {$localEndTime}", $professionalTimezone)->setTimezone('UTC');

            // Iterar en intervalos de la duración de la sesión
            $currentStartUtc = $workingStartUtc->copy();

            while ($currentStartUtc->copy()->addMinutes($durationMinutes)->lte($workingEndUtc)) {
                $slotStartUtc = $currentStartUtc->copy();
                $slotEndUtc = $currentStartUtc->copy()->addMinutes($durationMinutes);

                // Regla 1: Excluir el pasado (comparado con UTC actual)
                if ($slotStartUtc->lt(Carbon::now('UTC'))) {
                    $currentStartUtc->addMinutes($durationMinutes);

                    continue;
                }

                // Regla 2: Excluir si hay solapamiento con citas existentes
                if ($this->isOverlapping($slotStartUtc, $slotEndUtc, $existingAppointments)) {
                    $currentStartUtc->addMinutes($durationMinutes);

                    continue;
                }

                // Formatear para la respuesta (devolvemos UTC para lógica y Local para UI)
                $slots[] = [
                    'start_utc' => $slotStartUtc->toIso8601String(),
                    'end_utc' => $slotEndUtc->toIso8601String(),
                    // Formateado para el paciente usando su propia zona horaria
                    'label' => $slotStartUtc->copy()->setTimezone($displayTimezone)->format('H:i'),
                ];

                $currentStartUtc->addMinutes($durationMinutes);
            }
        }

        // Ordenar slots cronológicamente
        usort($slots, fn ($a, $b) => $a['start_utc'] <=> $b['start_utc']);

        return $slots;
    }

    /**
     * Verifica si un slot se solapa con alguna cita existente.
     */
    private function isOverlapping(Carbon $slotStart, Carbon $slotEnd, Collection $appointments): bool
    {
        foreach ($appointments as $appointment) {
            // Condición de solapamiento: (Inicio1 < Fin2) y (Fin1 > Inicio2)
            if ($slotStart < $appointment->ends_at && $slotEnd > $appointment->starts_at) {
                return true;
            }
        }

        return false;
    }
}
