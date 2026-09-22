<?php

declare(strict_types=1);

return [
    'nav' => [
        'appointment_reports' => 'Appointment reports',
    ],

    'appointments' => [
        'eyebrow' => 'Administration',
        'title' => 'Appointment reports and export',
        'description' => 'Audit booked appointments, cancellations, pending manual payments and collected amounts by professional.',
        'timezone_notice' => 'The date range is interpreted using the current timezone: :timezone.',
    ],

    'actions' => [
        'apply_filters' => 'Apply filters',
        'clear' => 'Clear',
        'export_csv' => 'Export CSV',
    ],

    'filters' => [
        'date_from' => 'From',
        'date_to' => 'To',
        'professional' => 'Professional',
        'all_professionals' => 'All professionals',
    ],

    'metrics' => [
        'total_appointments' => 'Total booked appointments',
        'cancelled_appointments' => 'Cancelled appointments',
        'paid_appointments' => 'Paid appointments',
        'pending_payment_appointments' => 'Pending payments',
        'payment_conversion_rate' => 'Payment conversion',
    ],

    'sections' => [
        'amounts_by_currency' => 'Amounts by currency',
        'pending_by_professional' => 'Pending payments by professional',
        'appointment_audit' => 'Auditable appointment detail',
        'appointment_audit_description' => 'Each row joins appointment, professional, service, price, payment status and manual payment date.',
    ],

    'table' => [
        'currency' => 'Currency',
        'estimated_amount' => 'Estimated amount',
        'collected_amount' => 'Collected amount',
        'pending_amount' => 'Pending amount',
        'professional' => 'Professional',
        'pending_appointments' => 'Pending appointments',
        'patient' => 'Customer',
        'session_type' => 'Session type',
        'starts_at' => 'Appointment date',
        'appointment_status' => 'Appointment status',
        'payment_status' => 'Payment status',
        'paid_at' => 'Marked as paid',
        'amount' => 'Amount',
    ],

    'status' => [
        'appointment' => [
            'pending' => 'Pending',
            'confirmed' => 'Confirmed',
            'cancelled' => 'Cancelled',
            'completed' => 'Completed',
        ],
        'payment' => [
            'pending' => 'Pending',
            'paid' => 'Paid',
            'waived' => 'Waived',
        ],
    ],

    'values' => [
        'not_paid' => 'No payment registered',
        'no_professional_name' => 'No name',
    ],

    'empty' => [
        'no_amounts' => 'There are no amounts for the selected range.',
        'no_pending_payments' => 'There are no pending payments for the selected range.',
        'no_appointments' => 'There are no appointments for the selected range.',
    ],

    'csv' => [
        'headers' => [
            'appointment_id' => 'Appointment ID',
            'customer_name' => 'Customer',
            'customer_email' => 'Customer email',
            'patient_name' => 'Customer',
            'patient_email' => 'Customer email',
            'professional_name' => 'Professional',
            'professional_email' => 'Professional email',
            'session_type' => 'Service',
            'starts_at_local' => 'Starts local',
            'ends_at_local' => 'Ends local',
            'starts_at_utc' => 'Starts UTC',
            'ends_at_utc' => 'Ends UTC',
            'appointment_status' => 'Appointment status',
            'payment_status' => 'Payment status',
            'paid_at_local' => 'Paid local',
            'paid_at_utc' => 'Paid UTC',
            'price' => 'Price',
            'currency' => 'Currency',
            'estimated_amount' => 'Estimated amount',
            'collected_amount' => 'Collected amount',
            'pending_amount' => 'Pending amount',
        ],
    ],
];
