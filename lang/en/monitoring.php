<?php

declare(strict_types=1);

return [
    'none' => 'none',

    'connection' => 'Connection: :connection',
    'queue' => 'Queue: :queue',

    'queue_worker_down' => [
        'subject' => 'Umbralia: queue worker down or missing heartbeat',
        'intro' => 'Monitoring detected that the queue worker is not processing the expected heartbeat.',
        'reason' => 'Reason: :reason',
        'last_heartbeat' => 'Last heartbeat: :last_heartbeat',
        'stale_after' => 'Configured threshold: :minutes minute(s).',
    ],

    'queue_busy' => [
        'subject' => 'Umbralia: queue is busy',
        'intro' => 'Laravel detected that a queue exceeded the configured pending jobs threshold.',
        'size' => 'Pending jobs: :size',
    ],

    'queue_job_failed' => [
        'subject' => 'Umbralia: queued job failed',
        'intro' => 'A queued job failed during execution.',
        'job' => 'Job: :job',
        'exception' => 'Exception: :exception',
        'message' => 'Message: :message',
    ],
];