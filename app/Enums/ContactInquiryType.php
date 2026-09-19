<?php

declare(strict_types=1);

namespace App\Enums;

enum ContactInquiryType: string
{
    case BOOKING_PROBLEM = 'booking_problem';
    case PAYMENT_PROBLEM = 'payment_problem';
    case GENERAL = 'general';
    case PROFESSIONAL_APPLICATION = 'professional_application';
    case OTHER = 'other';
}
