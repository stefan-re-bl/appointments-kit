<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetTimezone
{
    public function handle(Request $request, Closure $next): Response
    {
        // Leer la cookie, si no existe usar el fallback de config (UTC)
        $timezone = $request->cookie('user_timezone', config('app.timezone'));

        // Seguridad: Validar que la zona horaria sea real y válida
        if (!in_array($timezone, timezone_identifiers_list())) {
            $timezone = config('app.timezone');
        }

        // Guardar en el Singleton/Binding del contenedor de Laravel
        app()->instance('user.timezone', $timezone);

        return $next($request);
    }
}