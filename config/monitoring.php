<?php

declare(strict_types=1);

return [
    'alerts' => [
        'email' => env('MONITORING_ALERT_EMAIL'),
    ],

    'queue' => [
        'enabled' => env('MONITORING_QUEUE_ENABLED', true),

        'connection' => env('MONITORING_QUEUE_CONNECTION', 'redis'),
        'queue' => env('MONITORING_QUEUE_NAME', 'default'),

        'max_jobs' => env('MONITORING_QUEUE_MAX_JOBS', 25),

        'heartbeat_cache_key' => env('MONITORING_QUEUE_HEARTBEAT_CACHE_KEY', 'monitoring:queue-worker-heartbeat'),
        'heartbeat_ttl_minutes' => env('MONITORING_QUEUE_HEARTBEAT_TTL_MINUTES', 10),
        'stale_after_minutes' => env('MONITORING_QUEUE_STALE_AFTER_MINUTES', 3),

        'alert_cooldown_minutes' => env('MONITORING_QUEUE_ALERT_COOLDOWN_MINUTES', 30),
    ],
];