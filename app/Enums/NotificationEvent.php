<?php

declare(strict_types=1);

namespace App\Enums;

enum NotificationEvent: string
{
    case BOOKING_CONFIRMED = 'booking_confirmed';
    case APPOINTMENT_REMINDER = 'appointment_reminder';
}
