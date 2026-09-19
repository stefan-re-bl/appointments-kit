<?php

declare(strict_types=1);

return [
    'policies' => [
        'cancellation_notice_hours' => (int) env('BOOKING_CANCELLATION_NOTICE_HOURS', 24),
        'refund_notice_hours' => (int) env('BOOKING_REFUND_NOTICE_HOURS', 24),
        'reschedule_notice_hours' => (int) env('BOOKING_RESCHEDULE_NOTICE_HOURS', 48),
        'max_reschedules' => (int) env('BOOKING_MAX_RESCHEDULES', 2),
    ],

    'pending_expiration_minutes' => (int) env('BOOKING_PENDING_EXPIRATION_MINUTES', 15),

    'reminders' => [
        'lead_hours' => (int) env('BOOKING_REMINDER_LEAD_HOURS', 24),
        'stale_queue_minutes' => (int) env('BOOKING_REMINDER_STALE_QUEUE_MINUTES', 15),
    ],
];
