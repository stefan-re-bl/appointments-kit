<?php

declare(strict_types=1);

return [
    'provider' => [
        'singular' => env('TERM_PROVIDER_SINGULAR', 'profesional'),
        'plural' => env('TERM_PROVIDER_PLURAL', 'profesionales'),
    ],

    'customer' => [
        'singular' => env('TERM_CUSTOMER_SINGULAR', 'cliente'),
        'plural' => env('TERM_CUSTOMER_PLURAL', 'clientes'),
    ],

    'service' => [
        'singular' => env('TERM_SERVICE_SINGULAR', 'servicio'),
        'plural' => env('TERM_SERVICE_PLURAL', 'servicios'),
    ],

    'appointment' => [
        'singular' => env('TERM_APPOINTMENT_SINGULAR', 'cita'),
        'plural' => env('TERM_APPOINTMENT_PLURAL', 'citas'),
    ],
];
