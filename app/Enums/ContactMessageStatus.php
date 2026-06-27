<?php

declare(strict_types=1);

namespace App\Enums;

enum ContactMessageStatus: string
{
    case OPEN = 'open';
    case IN_REVIEW = 'in_review';
    case RESOLVED = 'resolved';
}
