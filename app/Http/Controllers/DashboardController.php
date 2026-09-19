<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
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

    public function __invoke(Request $request): RedirectResponse|View
    {
        /** @var User $user */
        $user = $request->user();

        if ($user->isAdmin()) {
            return redirect()->route('admin.index');
        }

        $professional = $user->professional()
            ->withCount([
                'availabilities as active_availabilities_count' => fn (Builder $query) => $query->where('is_active', true),
            ])
            ->first();

        return view('dashboard', [
            'professional' => $professional,
        ]);
    }
}
