<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsureFeatureIsEnabled
{
    public function handle(Request $request, Closure $next, string $feature): Response
    {
        abort_unless((bool) config("features.{$feature}", false), 404);

        return $next($request);
    }
}
