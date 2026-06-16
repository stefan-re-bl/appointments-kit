<?php

namespace App\Providers;

use App\Events\AppointmentBooked;
use App\Listeners\SendBookingNotificationToPatient;
use App\Listeners\SendBookingNotificationToTherapist;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        AppointmentBooked::class => [
            SendBookingNotificationToPatient::class,
            SendBookingNotificationToTherapist::class,
        ],
    ];

    public function boot(): void
    {
        //
    }

    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}