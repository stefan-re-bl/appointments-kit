<?php

declare(strict_types=1);

return [
    'none' => 'ninguno',

    'connection' => 'Conexión: :connection',
    'queue' => 'Cola: :queue',

    'queue_worker_down' => [
        'subject' => 'Umbralia: queue worker caído o sin heartbeat',
        'intro' => 'El monitoreo detectó que el worker de colas no está procesando el heartbeat esperado.',
        'reason' => 'Motivo: :reason',
        'last_heartbeat' => 'Último heartbeat: :last_heartbeat',
        'stale_after' => 'Umbral configurado: :minutes minuto(s).',
    ],

    'queue_busy' => [
        'subject' => 'Umbralia: cola saturada',
        'intro' => 'Laravel detectó que una cola superó el umbral configurado de jobs pendientes.',
        'size' => 'Jobs pendientes: :size',
    ],

    'queue_job_failed' => [
        'subject' => 'Umbralia: job fallido',
        'intro' => 'Un job encolado falló durante su ejecución.',
        'job' => 'Job: :job',
        'exception' => 'Excepción: :exception',
        'message' => 'Mensaje: :message',
    ],
];