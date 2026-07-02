<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreAvailabilityRequest;
use App\Models\Availability;
use App\Models\User;
use App\Services\AvailabilityService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

final class AvailabilityController implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth'),
            new Middleware(function (Request $request, callable $next) {
                /** @var User|null $user */
                $user = Auth::user();

                if (! $user instanceof User || ! $user->therapist) {
                    abort(403, __('app.availability.errors.unauthorized'));
                }

                $routeAvailability = $request->route('availability');

                if ($routeAvailability instanceof Availability) {
                    if ((int) $routeAvailability->therapist_id !== (int) $user->therapist->id) {
                        abort(403, __('app.availability.errors.forbidden'));
                    }
                }

                return $next($request);
            }),
        ];
    }

    public function index(AvailabilityService $availabilityService): View
    {
        /** @var User $user */
        $user = Auth::user();

        $therapist = $user->therapist;
        $availabilities = $availabilityService->groupedLocalAvailabilities($therapist);

        $days = $this->getDaysOfWeek();

        return view('availabilities.index', compact('availabilities', 'days'));
    }

    public function create(): View
    {
        $days = $this->getDaysOfWeek();

        return view('availabilities.create', compact('days'));
    }

    public function store(StoreAvailabilityRequest $request, AvailabilityService $availabilityService): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        $therapist = $user->therapist;
        $dayOfWeek = (int) $request->input('day_of_week');

        /** @var array<int, array{start_time: string, end_time: string, is_active?: mixed}> $slots */
        $slots = $request->input('slots', []);

        if ($availabilityService->hasOverlaps($therapist, $dayOfWeek, $slots)) {
            return back()
                ->withErrors(['slots' => __('app.availability.errors.overlap')])
                ->withInput();
        }

        $availabilityService->createMany($therapist, $dayOfWeek, $slots);

        return redirect()
            ->route('availabilities.index')
            ->with('status', __('app.availability.saved'));
    }

    public function destroy(Availability $availability): RedirectResponse
    {
        $availability->delete();

        return back()->with('status', __('app.availability.deleted'));
    }

    /**
     * @return array<int, string>
     */
    private function getDaysOfWeek(): array
    {
        return [
            1 => __('app.day_1'),
            2 => __('app.day_2'),
            3 => __('app.day_3'),
            4 => __('app.day_4'),
            5 => __('app.day_5'),
            6 => __('app.day_6'),
            7 => __('app.day_7'),
        ];
    }
}
