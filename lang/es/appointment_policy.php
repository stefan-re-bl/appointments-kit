<?php

declare(strict_types=1);

return [
    'show' => [
        'title' => 'Mi cita',
        'subtitle' => 'Consultá el estado de tu cita y las acciones disponibles según la política vigente.',
        'patient' => 'Paciente',
        'patient_email' => 'Email del paciente',
        'therapist' => 'Terapeuta',
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
        'refund_available' => 'La cita puede cancelarse porque faltan al menos :refund_hours horas. Como el pago figura registrado, la devolución debe coordinarse manualmente con la terapeuta.',
        'cancel_without_refund' => 'La cita puede cancelarse porque faltan al menos :refund_hours horas. No figura un pago registrado, por lo que no hay devolución pendiente desde el sistema.',
        'reschedule_only' => 'La cita ya no está dentro del plazo de devolución, pero todavía puede solicitarse reprogramación porque faltan al menos :reschedule_hours horas.',
        'reschedule_limit_reached' => 'Esta cita ya alcanzó el límite de :max_reschedules reprogramaciones. Para cualquier cambio adicional, coordiná directamente con la terapeuta.',
        'too_late' => 'La cita está fuera del plazo de autoservicio. Para cambios o cancelaciones, coordiná directamente con la terapeuta.',
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
        'cancelled' => 'La cita fue cancelada correctamente. Si corresponde devolución, debe coordinarse manualmente con la terapeuta.',
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
];