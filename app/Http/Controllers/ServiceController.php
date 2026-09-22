<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreServiceRequest;
use App\Http\Requests\UpdateServiceRequest;
use App\Models\Service;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

final class ServiceController implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth'),
            new Middleware(function (Request $request, callable $next) {
                /** @var User|null $user */
                $user = Auth::user();

                if (! $user instanceof User || ! $user->professional) {
                    abort(403, __('app.session_type_management.errors.unauthorized'));
                }

                $routeService = $request->route('service');

                if ($routeService instanceof Service) {
                    if ((int) $routeService->professional_id !== (int) $user->professional->id) {
                        abort(403, __('app.session_type_management.errors.forbidden'));
                    }
                }

                return $next($request);
            }),
        ];
    }

    public function index(): View
    {
        abort(404);
    }

    public function create(): View|RedirectResponse
    {
        return redirect()->route('dashboard');
    }

    public function store(StoreServiceRequest $request): RedirectResponse
    {
        return redirect()->route('dashboard');
    }

    public function edit(Service $service): View|RedirectResponse
    {
        return redirect()->route('dashboard');
    }

    public function update(UpdateServiceRequest $request, Service $service): RedirectResponse
    {
        return redirect()->route('dashboard');
    }

    public function destroy(Service $service): RedirectResponse
    {
        return redirect()->route('dashboard');
    }
}
