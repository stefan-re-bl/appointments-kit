<?php

namespace App\Providers;

use App\Services\BookingService;
use App\Services\TimezoneService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Registramos el servicio de zonas horarias como Singleton
        $this->app->singleton(TimezoneService::class, function ($app) {
            return new TimezoneService();
        });

        // Servicio de reservas con prevención de race conditions (Ticket #9)
        $this->app->singleton(BookingService::class);
    }

    public function boot(): void
    {
        //
    }
}