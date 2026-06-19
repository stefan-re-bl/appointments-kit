<?php

declare(strict_types=1);

return [
    'common' => [
        'greeting' => 'Hello :name,',
        'patient' => 'Patient',
        'therapist' => 'Therapist',
        'session_type' => 'Session type',
        'timezone' => 'Timezone',
        'view_appointment' => 'View appointment',
        'thanks' => 'Thanks',
        'time_range' => ':start - :end',
    ],

    'rescheduled' => [
        'subject' => 'Your appointment was rescheduled',
        'title' => 'Appointment rescheduled',
        'intro' => 'We are letting you know that the appointment was rescheduled.',
        'previous_time' => 'Previous time',
        'new_time' => 'New time',
        'footer' => 'Times are shown in the indicated timezone.',
    ],

    'reschedule' => [
        'action' => 'Change date',
        'signed_url_notice' => 'This rescheduling link is personal and expires in 24 hours.',
    ],

    'cancelled' => [
        'subject' => 'Your appointment was cancelled',
        'title' => 'Appointment cancelled',
        'intro' => 'We are letting you know that the appointment was cancelled.',
        'appointment_time' => 'Appointment time',
        'footer' => 'If you need to coordinate a new appointment, you can start a new booking.',
    ],
];