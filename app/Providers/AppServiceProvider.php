<?php

namespace App\Providers;

use App\Services\TimezoneService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Registramos el servicio como Singleton
        $this->app->singleton(TimezoneService::class, function ($app) {
            return new TimezoneService();
        });
    }

    public function boot(): void
    {
        //
    }
}