<?php

declare(strict_types=1);

return [
    'show' => [
        'title' => 'My appointment',
        'subtitle' => 'Check your appointment status and available actions under the current policy.',
        'patient' => 'Patient',
        'patient_email' => 'Patient email',
        'therapist' => 'Therapist',
        'session_type' => 'Session type',
        'starts_at' => 'Starts at',
        'ends_at' => 'Ends at',
        'timezone' => 'Timezone',
        'reschedule_count' => 'Used reschedules',
        'appointment_status' => 'Appointment status',
        'payment_status' => 'Payment status',
        'paid_at' => 'Payment registered at',
        'google_meet' => 'Google Meet link',
        'not_available' => 'Not available',
    ],

    'messages' => [
        'refund_available' => 'Cancellation with refund eligibility may be requested up to :refund_hours hours before the agreed time. Since payment is registered, the refund must be coordinated manually with the therapist.',
        'cancel_without_refund' => 'The appointment can be cancelled, but it is no longer within the :refund_hours-hour refund window or no payment is registered. Date or time changes may be requested up to :reschedule_hours hours before the appointment.',
        'reschedule_only' => 'This appointment is no longer within the refund window, but a date or time change can still be requested because it is at least :reschedule_hours hours away.',
        'reschedule_limit_reached' => 'This appointment has already reached the limit of :max_reschedules reschedules. Any exception is subject to the prior agreement between patient and therapist.',
        'too_late' => 'This appointment is outside the change window. After that window, changes will only be admitted for duly justified force majeure reasons and by prior agreement with the therapist.',
        'not_actionable' => 'This appointment no longer supports self-service actions because of its current status.',
    ],

    'actions' => [
        'title' => 'Available actions',
        'cancel_with_refund' => 'Cancel and coordinate refund',
        'cancel_without_refund' => 'Cancel appointment',
        'request_reschedule' => 'Request reschedule',
        'open_google_meet' => 'Open Google Meet',
        'none_available' => 'There are no self-service actions available for this appointment.',
    ],

    'flash' => [
        'cancelled' => 'The appointment was cancelled successfully. If a refund applies because it was requested up to 24 hours before the agreed time, it must be coordinated manually with the therapist.',
        'cancel_not_allowed' => 'This appointment cannot be cancelled from this page because it is outside the allowed window.',
    ],

    'mail' => [
        'reschedule_subject' => 'Appointment reschedule request',
        'reschedule_body' => "Hi, I am :patient.\n\nI would like to coordinate a reschedule for my appointment.\n\nReference link: :url",
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
    'sections' => [
        'policy' => 'Cancellation and rescheduling policy',
    ],
];
