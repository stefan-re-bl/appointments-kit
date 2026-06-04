<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAvailabilityRequest;
use App\Models\Availability;
use App\Models\Therapist;
use App\Services\TimezoneService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AvailabilityController implements HasMiddleware
{
    public function __construct(private TimezoneService $timezoneService) {}

    public static function middleware(): array
    {
        return [
            new Middleware('auth'),
            new Middleware(function ($request, $next) {
                if (!Auth::user()->therapist) {
                    abort(403, 'Acceso no autorizado.');
                }
                return $next($request);
            }),
        ];
    }

    public function index()
    {
        $therapist = Auth::user()->therapist;
        $timezone = $therapist->timezone;

        // Agrupar por día y convertir a hora local
        $availabilities = $therapist->availabilities()
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get()
            ->groupBy('day_of_week')
            ->map(function ($group) use ($timezone) {
                return $group->map(function ($avail) use ($timezone) {
                    $avail->start_time_local = $this->timezoneService->timeToLocal($avail->start_time, $timezone, $avail->day_of_week);
                    $avail->end_time_local = $this->timezoneService->timeToLocal($avail->end_time, $timezone, $avail->day_of_week);
                    return $avail;
                });
            });

        $days = $this->getDaysOfWeek();

        return view('availabilities.index', compact('availabilities', 'days'));
    }

    public function create()
    {
        $days = $this->getDaysOfWeek();
        return view('availabilities.create', compact('days'));
    }

    public function store(StoreAvailabilityRequest $request)
    {
        $therapist = Auth::user()->therapist;
        $timezone = $therapist->timezone;
        $dayOfWeek = $request->input('day_of_week');
        $slots = $request->input('slots');

        // 1. Obtener horarios existentes en LOCAL para comparar
        $existingSlots = $therapist->availabilities()
            ->where('day_of_week', $dayOfWeek)
            ->get()
            ->map(function ($avail) use ($timezone, $dayOfWeek) {
                return [
                    'start' => $this->timezoneService->timeToLocal($avail->start_time, $timezone, $dayOfWeek),
                    'end' => $this->timezoneService->timeToLocal($avail->end_time, $timezone, $dayOfWeek),
                ];
            })->toArray();

        // 2. Verificar solapamientos incluyendo los nuevos
        $allSlots = array_merge($existingSlots, array_map(fn($slot) => ['start' => $slot['start_time'], 'end' => $slot['end_time']], $slots));

        if ($this->hasOverlaps($allSlots)) {
            return back()->withErrors(['slots' => 'Los rangos horarios se solapan con horarios ya existentes o entre sí.'])->withInput();
        }

        // 3. Guardar en UTC
        foreach ($slots as $slot) {
            Availability::create([
                'therapist_id' => $therapist->id,
                'day_of_week' => $dayOfWeek,
                'start_time' => $this->timezoneService->timeToUtc($slot['start_time'], $timezone, $dayOfWeek),
                'end_time' => $this->timezoneService->timeToUtc($slot['end_time'], $timezone, $dayOfWeek),
                'is_active' => isset($slot['is_active']) ? true : false,
            ]);
        }

        return redirect()->route('availabilities.index')->with('status', 'Disponibilidad guardada correctamente.');
    }

    public function destroy(Availability $availability)
    {
        if ($availability->therapist_id !== Auth::user()->therapist->id) {
            abort(403);
        }

        $availability->delete();
        return back()->with('status', 'Horario eliminado.');
    }

    private function hasOverlaps(array $slots): bool
    {
        usort($slots, fn($a, $b) => strcmp($a['start'], $b['start']));
        for ($i = 1; $i < count($slots); $i++) {
            if ($slots[$i]['start'] < $slots[$i - 1]['end']) {
                return true;
            }
        }
        return false;
    }

    private function getDaysOfWeek(): array
    {
        return [
            1 => 'Lunes', 2 => 'Martes', 3 => 'Miércoles', 4 => 'Jueves',
            5 => 'Viernes', 6 => 'Sábado', 7 => 'Domingo'
        ];
    }
}