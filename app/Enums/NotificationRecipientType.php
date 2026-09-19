<?php

declare(strict_types=1);

namespace App\Enums;

enum NotificationRecipientType: string
{
    case PATIENT = 'patient';
    case PROFESSIONAL = 'professional';
}
