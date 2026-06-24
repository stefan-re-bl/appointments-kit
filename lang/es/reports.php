<?php

declare(strict_types=1);

return [
    'nav' => [
        'appointment_reports' => 'Reportes de turnos',
    ],

    'appointments' => [
        'eyebrow' => 'Administración',
        'title' => 'Reportes y exportación de turnos',
        'description' => 'Audita turnos reservados, cancelaciones, pagos manuales pendientes y montos cobrados por terapeuta.',
        'timezone_notice' => 'El rango de fechas se interpreta usando la zona horaria actual: :timezone.',
    ],

    'actions' => [
        'apply_filters' => 'Aplicar filtros',
        'clear' => 'Limpiar',
        'export_csv' => 'Exportar CSV',
    ],

    'filters' => [
        'date_from' => 'Desde',
        'date_to' => 'Hasta',
        'therapist' => 'Terapeuta',
        'all_therapists' => 'Todas las terapeutas',
    ],

    'metrics' => [
        'total_appointments' => 'Total de citas reservadas',
        'cancelled_appointments' => 'Citas canceladas',
        'paid_appointments' => 'Citas pagadas',
        'pending_payment_appointments' => 'Pagos pendientes',
        'payment_conversion_rate' => 'Conversión de pago',
    ],

    'sections' => [
        'amounts_by_currency' => 'Montos por moneda',
        'pending_by_therapist' => 'Pagos pendientes por terapeuta',
        'appointment_audit' => 'Detalle auditable de turnos',
        'appointment_audit_description' => 'Cada fila cruza turno, terapeuta, tipo de sesión, precio, estado de pago y fecha manual de pago.',
    ],

    'table' => [
        'currency' => 'Moneda',
        'estimated_amount' => 'Monto estimado',
        'collected_amount' => 'Monto cobrado',
        'pending_amount' => 'Monto pendiente',
        'therapist' => 'Terapeuta',
        'pending_appointments' => 'Turnos pendientes',
        'patient' => 'Paciente',
        'session_type' => 'Tipo de sesión',
        'starts_at' => 'Fecha del turno',
        'appointment_status' => 'Estado de cita',
        'payment_status' => 'Estado de pago',
        'paid_at' => 'Marcado como pagado',
        'amount' => 'Monto',
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
            'waived' => 'Bonificado',
        ],
    ],

    'values' => [
        'not_paid' => 'Sin pago registrado',
        'no_therapist_name' => 'Sin nombre',
    ],

    'empty' => [
        'no_amounts' => 'No hay montos para el rango seleccionado.',
        'no_pending_payments' => 'No hay pagos pendientes para el rango seleccionado.',
        'no_appointments' => 'No hay turnos para el rango seleccionado.',
    ],

    'csv' => [
        'headers' => [
            'appointment_id' => 'ID cita',
            'patient_name' => 'Paciente',
            'patient_email' => 'Email paciente',
            'therapist_name' => 'Terapeuta',
            'therapist_email' => 'Email terapeuta',
            'session_type' => 'Tipo de sesión',
            'starts_at_local' => 'Inicio local',
            'ends_at_local' => 'Fin local',
            'starts_at_utc' => 'Inicio UTC',
            'ends_at_utc' => 'Fin UTC',
            'appointment_status' => 'Estado cita',
            'payment_status' => 'Estado pago',
            'paid_at_local' => 'Pagado local',
            'paid_at_utc' => 'Pagado UTC',
            'price' => 'Precio',
            'currency' => 'Moneda',
            'estimated_amount' => 'Monto estimado',
            'collected_amount' => 'Monto cobrado',
            'pending_amount' => 'Monto pendiente',
        ],
    ],
];