<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Appointment;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\URL;

final class AppointmentSignedUrlService
{
    private const RESCHEDULE_LINK_EXPIRATION_HOURS = 24;

    public function rescheduleUrl(Appointment $appointment): string
    {
        return URL::signedRoute(
            'appointments.public.reschedule',
            ['token' => $appointment->token],
            CarbonImmutable::now('UTC')->addHours(self::RESCHEDULE_LINK_EXPIRATION_HOURS),
        );
    }
}