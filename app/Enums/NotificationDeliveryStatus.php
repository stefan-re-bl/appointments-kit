<?php

declare(strict_types=1);

namespace App\Enums;

enum NotificationDeliveryStatus: string
{
    case PENDING = 'pending';
    case QUEUED = 'queued';
    case SUBMITTED = 'submitted';
    case DELIVERED = 'delivered';
    case READ = 'read';
    case FAILED = 'failed';
    case SKIPPED = 'skipped';
}
