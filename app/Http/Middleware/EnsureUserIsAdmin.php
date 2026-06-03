<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Si no hay usuario logueado o el método isAdmin() retorna false
        if (! $request->user() || ! $request->user()->isAdmin()) {
            abort(403, 'Acceso no autorizado.');
        }

        return $next($request);
    }
}