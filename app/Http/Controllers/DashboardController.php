<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;

final class DashboardController extends Controller implements HasMiddleware
{
    /**
     * @return array<int, Middleware>
     */
    public static function middleware(): array
    {
        return [
            new Middleware('auth'),
            new Middleware('verified'),
        ];
    }

    public function __invoke(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();

        $therapist = $user->therapist()
            ->withCount([
                'sessionTypes as active_session_types_count' => fn (Builder $query) => $query->where('is_active', true),
                'availabilities as active_availabilities_count' => fn (Builder $query) => $query->where('is_active', true),
            ])
            ->first();

        return view('dashboard', [
            'therapist' => $therapist,
        ]);
    }
}
