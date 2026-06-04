<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Verificar Query Param (?lang=es o ?lang=en)
        if ($request->has('lang') && in_array($request->get('lang'), ['es', 'en'])) {
            $locale = $request->get('lang');
            Session::put('locale', $locale);
            App::setLocale($locale);
        }
        // 2. Verificar Sesión
        elseif (Session::has('locale') && in_array(Session::get('locale'), ['es', 'en'])) {
            App::setLocale(Session::get('locale'));
        }
        // 3. Verificar preferencia del Navegador (Browser default)
        else {
            $browserLang = substr($request->getPreferredLanguage(), 0, 2);
            if (in_array($browserLang, ['es', 'en'])) {
                App::setLocale($browserLang);
            } else {
                App::setLocale(config('app.locale')); // Fallback a config/app.php
            }
        }

        return $next($request);
    }
}