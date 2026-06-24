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
                    abort(403, 'Acceso no autorizado.');
                }

                // Si estamos editando/actualizando/eliminando, verificar pertenencia.
                $routeSessionType = $request->route('session_type');

                if ($routeSessionType instanceof SessionType) {
                    if ((int) $routeSessionType->therapist_id !== (int) $user->therapist->id) {
                        abort(403, 'No tienes permiso para modificar este tipo de sesión.');
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

        $sessionTypes = $user->therapist->sessionTypes()->latest()->get();

        return view('session-types.index', compact('sessionTypes'));
    }

    public function create(): View
    {
        return view('session-types.create');
    }

    public function store(StoreSessionTypeRequest $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        $validated = $request->validated();
        $validated['is_active'] = $request->boolean('is_active');

        $user->therapist->sessionTypes()->create($validated);

        return redirect()->route('session-types.index')
            ->with('success', 'Tipo de sesión creado exitosamente.');
    }

    public function edit(SessionType $sessionType): View
    {
        return view('session-types.edit', compact('sessionType'));
    }

    public function update(UpdateSessionTypeRequest $request, SessionType $sessionType): RedirectResponse
    {
        $validated = $request->validated();
        $validated['is_active'] = $request->boolean('is_active');

        $sessionType->update($validated);

        return redirect()->route('session-types.index')
            ->with('success', 'Tipo de sesión actualizado exitosamente.');
    }

    public function destroy(SessionType $sessionType): RedirectResponse
    {
        $sessionType->delete();

        return redirect()->route('session-types.index')
            ->with('success', 'Tipo de sesión eliminado exitosamente.');
    }
}