<?php

declare(strict_types=1);

return [
    'nav' => [
        'appointment_reports' => 'Reportes de turnos',
    ],

    'appointments' => [
        'eyebrow' => 'Administración',
        'title' => 'Reportes y exportación de turnos',
        'description' => 'Audita turnos reservados, cancelaciones, pagos manuales pendientes y montos cobrados por profesional.',
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
        'professional' => 'Profesional',
        'all_professionals' => 'Todos los profesionales',
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
        'pending_by_professional' => 'Pagos pendientes por profesional',
        'appointment_audit' => 'Detalle auditable de turnos',
        'appointment_audit_description' => 'Cada fila cruza turno, profesional, tipo de sesión, precio, estado de pago y fecha manual de pago.',
    ],

    'table' => [
        'currency' => 'Moneda',
        'estimated_amount' => 'Monto estimado',
        'collected_amount' => 'Monto cobrado',
        'pending_amount' => 'Monto pendiente',
        'professional' => 'Profesional',
        'pending_appointments' => 'Turnos pendientes',
        'patient' => 'Cliente',
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
        'no_professional_name' => 'Sin nombre',
    ],

    'empty' => [
        'no_amounts' => 'No hay montos para el rango seleccionado.',
        'no_pending_payments' => 'No hay pagos pendientes para el rango seleccionado.',
        'no_appointments' => 'No hay turnos para el rango seleccionado.',
    ],

    'csv' => [
        'headers' => [
            'appointment_id' => 'ID cita',
            'patient_name' => 'Cliente',
            'patient_email' => 'Email cliente',
            'professional_name' => 'Profesional',
            'professional_email' => 'Email profesional',
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
