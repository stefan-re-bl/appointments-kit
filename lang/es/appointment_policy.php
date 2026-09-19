<?php

declare(strict_types=1);

return [
    'show' => [
        'title' => 'Mi cita',
        'subtitle' => 'Consultá el estado de tu cita y las acciones disponibles según la política vigente.',
        'patient' => 'Cliente',
        'patient_email' => 'Email del cliente',
        'professional' => 'Profesional',
        'session_type' => 'Tipo de sesión',
        'starts_at' => 'Inicio',
        'ends_at' => 'Fin',
        'timezone' => 'Zona horaria',
        'reschedule_count' => 'Reprogramaciones usadas',
        'appointment_status' => 'Estado de la cita',
        'payment_status' => 'Estado del pago',
        'paid_at' => 'Pago registrado el',
        'google_meet' => 'Link de Google Meet',
        'not_available' => 'No disponible',
    ],

    'messages' => [
        'refund_available' => 'La cancelación con derecho a reembolso puede solicitarse hasta :refund_hours horas antes del horario acordado. Como el pago figura registrado, la devolución debe coordinarse manualmente.',
        'cancel_without_refund' => 'La cita puede cancelarse, pero ya no está dentro del plazo de reembolso de :refund_hours horas o no figura un pago registrado. La modificación de fecha u horario puede solicitarse hasta :reschedule_hours horas antes del turno.',
        'reschedule_only' => 'La cita ya no está dentro del plazo de reembolso, pero la modificación de fecha u horario todavía puede solicitarse porque faltan al menos :reschedule_hours horas.',
        'reschedule_limit_reached' => 'Esta cita ya alcanzó el límite de :max_reschedules reprogramaciones. Cualquier excepción queda sujeta al acuerdo del negocio.',
        'too_late' => 'La cita está fuera del plazo de modificación. Pasado ese plazo, solo se admitirán cambios bajo acuerdo del negocio.',
        'not_actionable' => 'Esta cita ya no admite acciones de autoservicio por su estado actual.',
    ],

    'actions' => [
        'title' => 'Acciones disponibles',
        'cancel_with_refund' => 'Cancelar y coordinar devolución',
        'cancel_without_refund' => 'Cancelar cita',
        'request_reschedule' => 'Solicitar reprogramación',
        'open_google_meet' => 'Abrir Google Meet',
        'none_available' => 'No hay acciones de autoservicio disponibles para esta cita.',
    ],

    'flash' => [
        'cancelled' => 'La cita fue cancelada correctamente. Si corresponde reembolso por haber sido solicitada hasta 24 horas antes del horario acordado, debe coordinarse manualmente.',
        'cancel_not_allowed' => 'La cita no puede cancelarse desde esta página porque está fuera del plazo permitido.',
    ],

    'mail' => [
        'reschedule_subject' => 'Solicitud de reprogramación de cita',
        'reschedule_body' => "Hola, soy :patient.\n\nQuisiera coordinar la reprogramación de mi cita.\n\nLink de referencia: :url",
    ],

    'status' => [
        'appointment' => [
            'pending' => 'Pendiente',
            'confirmed' => 'Confirmada',
            'cancelled' => 'Cancelada',
            'completed' => 'Completada',
        ],
        'payment' => [
            'pending' => 'Pendiente',
            'paid' => 'Pagado',
            'waived' => 'Eximido',
        ],
    ],
    'sections' => [
        'policy' => 'Política de cancelación y reprogramación',
    ],
];
