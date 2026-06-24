<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreAvailabilityRequest;
use App\Models\Availability;
use App\Models\User;
use App\Services\TimezoneService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

final class AvailabilityController implements HasMiddleware
{
    public function __construct(private readonly TimezoneService $timezoneService)
    {
    }

    public static function middleware(): array
    {
        return [
            new Middleware('auth'),
            new Middleware(function (Request $request, callable $next) {
                /** @var User|null $user */
                $user = Auth::user();

                if (! $user instanceof User || ! $user->therapist) {
                    abort(403, 'Acceso no autorizado.');
                }

                $routeAvailability = $request->route('availability');

                if ($routeAvailability instanceof Availability) {
                    if ((int) $routeAvailability->therapist_id !== (int) $user->therapist->id) {
                        abort(403, 'No tienes permiso para modificar esta disponibilidad.');
                    }
                }

                return $next($request);
            }),
        ];
    }

    public function index(): View
    {
        /** @var User $user */
        $user = Auth::user();

        $therapist = $user->therapist;
        $timezone = $therapist->timezone;

        // Agrupar por día y convertir a hora local.
        $availabilities = $therapist->availabilities()
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get()
            ->groupBy('day_of_week')
            ->map(function ($group) use ($timezone) {
                return $group->map(function (Availability $availability) use ($timezone): Availability {
                    $availability->start_time_local = $this->timezoneService->timeToLocal(
                        $availability->start_time,
                        $timezone,
                        $availability->day_of_week,
                    );

                    $availability->end_time_local = $this->timezoneService->timeToLocal(
                        $availability->end_time,
                        $timezone,
                        $availability->day_of_week,
                    );

                    return $availability;
                });
            });

        $days = $this->getDaysOfWeek();

        return view('availabilities.index', compact('availabilities', 'days'));
    }

    public function create(): View
    {
        $days = $this->getDaysOfWeek();

        return view('availabilities.create', compact('days'));
    }

    public function store(StoreAvailabilityRequest $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        $therapist = $user->therapist;
        $timezone = $therapist->timezone;

        $dayOfWeek = (int) $request->input('day_of_week');

        /** @var array<int, array{start_time: string, end_time: string, is_active?: mixed}> $slots */
        $slots = $request->input('slots', []);

        // 1. Obtener horarios existentes en LOCAL para comparar.
        $existingSlots = $therapist->availabilities()
            ->where('day_of_week', $dayOfWeek)
            ->get()
            ->map(function (Availability $availability) use ($timezone, $dayOfWeek): array {
                return [
                    'start' => $this->timezoneService->timeToLocal($availability->start_time, $timezone, $dayOfWeek),
                    'end' => $this->timezoneService->timeToLocal($availability->end_time, $timezone, $dayOfWeek),
                ];
            })
            ->toArray();

        // 2. Verificar solapamientos incluyendo los nuevos.
        $newSlots = array_map(
            fn (array $slot): array => [
                'start' => $slot['start_time'],
                'end' => $slot['end_time'],
            ],
            $slots,
        );

        $allSlots = array_merge($existingSlots, $newSlots);

        if ($this->hasOverlaps($allSlots)) {
            return back()
                ->withErrors(['slots' => 'Los rangos horarios se solapan con horarios ya existentes o entre sí.'])
                ->withInput();
        }

        // 3. Guardar en UTC sin mass assignment de therapist_id.
        foreach ($slots as $slot) {
            $therapist->availabilities()->create([
                'day_of_week' => $dayOfWeek,
                'start_time' => $this->timezoneService->timeToUtc($slot['start_time'], $timezone, $dayOfWeek),
                'end_time' => $this->timezoneService->timeToUtc($slot['end_time'], $timezone, $dayOfWeek),
                'is_active' => isset($slot['is_active']),
            ]);
        }

        return redirect()
            ->route('availabilities.index')
            ->with('status', 'Disponibilidad guardada correctamente.');
    }

    public function destroy(Availability $availability): RedirectResponse
    {
        $availability->delete();

        return back()->with('status', 'Horario eliminado.');
    }

    /**
     * @param array<int, array{start: string, end: string}> $slots
     */
    private function hasOverlaps(array $slots): bool
    {
        usort($slots, fn (array $a, array $b): int => strcmp($a['start'], $b['start']));

        for ($i = 1; $i < count($slots); $i++) {
            if ($slots[$i]['start'] < $slots[$i - 1]['end']) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array<int, string>
     */
    private function getDaysOfWeek(): array
    {
        return [
            1 => 'Lunes',
            2 => 'Martes',
            3 => 'Miércoles',
            4 => 'Jueves',
            5 => 'Viernes',
            6 => 'Sábado',
            7 => 'Domingo',
        ];
    }
}