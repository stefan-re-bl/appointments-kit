<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSessionTypeRequest;
use App\Http\Requests\UpdateSessionTypeRequest;
use App\Models\SessionType;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SessionTypeController implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth'),
            // Middleware anónimo para asegurar que solo terapeutas accedan y sean dueños del recurso
            new Middleware(function ($request, $next) {
                /** @var User $user */
                $user = Auth::user();

                if (!$user->therapist) {
                    abort(403, 'Acceso no autorizado.');
                }

                // Si estamos editando/actualizando/eliminando, verificar pertenencia
                $routeSessionType = $request->route('session_type');
                if ($routeSessionType instanceof SessionType) {
                    if ($routeSessionType->therapist_id !== $user->therapist->id) {
                        abort(403, 'No tienes permiso para modificar este tipo de sesión.');
                    }
                }

                return $next($request);
            }),
        ];
    }

    public function index(): View
    {
        $sessionTypes = Auth::user()->therapist->sessionTypes()->latest()->get();

        return view('session-types.index', compact('sessionTypes'));
    }

    public function create(): View
    {
        return view('session-types.create');
    }

    public function store(StoreSessionTypeRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $validated['therapist_id'] = Auth::user()->therapist->id;
        $validated['is_active'] = $request->boolean('is_active');

        SessionType::create($validated);

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