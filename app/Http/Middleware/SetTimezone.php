<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Rules\ValidTimezone;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class SetTimezone
{
    public function handle(Request $request, Closure $next): Response
    {
        // Leer la cookie; si no existe o no es válida, usar fallback de config.
        $timezone = ValidTimezone::normalize($request->cookie('user_timezone'));

        if ($timezone === null) {
            $timezone = ValidTimezone::normalize((string) config('app.timezone', 'UTC')) ?? 'UTC';
        }

        // Guardar en el Singleton/Binding del contenedor de Laravel.
        app()->instance('user.timezone', $timezone);

        return $next($request);
    }
}