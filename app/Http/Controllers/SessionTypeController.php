<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreSessionTypeRequest;
use App\Http\Requests\UpdateSessionTypeRequest;
use App\Models\SessionType;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

final class SessionTypeController implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth'),
            // Middleware anónimo para asegurar que solo terapeutas accedan y sean dueños del recurso.
            new Middleware(function (Request $request, callable $next) {
                /** @var User|null $user */
                $user = Auth::user();

                if (! $user instanceof User || ! $user->therapist) {
                    abort(403, __('app.session_type_management.errors.unauthorized'));
                }

                // Si estamos editando/actualizando/eliminando, verificar pertenencia.
                $routeSessionType = $request->route('session_type');

                if ($routeSessionType instanceof SessionType) {
                    if ((int) $routeSessionType->therapist_id !== (int) $user->therapist->id) {
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

    public function store(StoreSessionTypeRequest $request): RedirectResponse
    {
        return redirect()->route('dashboard');
    }

    public function edit(SessionType $sessionType): View|RedirectResponse
    {
        return redirect()->route('dashboard');
    }

    public function update(UpdateSessionTypeRequest $request, SessionType $sessionType): RedirectResponse
    {
        return redirect()->route('dashboard');
    }

    public function destroy(SessionType $sessionType): RedirectResponse
    {
        return redirect()->route('dashboard');
    }
}
