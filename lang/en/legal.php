<?php

return [
    'eyebrow' => 'Legal information',
    'nav' => [
        'index' => 'Legal',
        'terms' => 'Terms',
        'privacy' => 'Privacy',
        'emergency' => 'Emergency notice',
    ],
    'footer' => [
        'title' => 'Legal',
        'disclaimer' => 'Umbralia does not handle emergencies and does not process payments inside the platform.',
    ],
    'booking' => [
        'emergency_title' => 'Umbralia is not an emergency service',
        'emergency_text' => 'If you or someone else is at immediate risk, contact your local emergency services. Umbralia only helps manage bookings and operational communication for online appointments.',
        'emergency_link' => 'Read emergency notice',
        'accept_terms_prefix' => 'I accept the',
        'accept_terms_and' => 'and the',
        'accept_email_communications' => 'I agree to receive operational emails related to my appointment, such as confirmations, links and reminders.',
    ],
    'email' => [
        'patient_disclaimer' => 'Reminder: Umbralia does not handle emergencies and does not process payments inside the platform. Payment is coordinated directly with the therapist.',
    ],
    'pages' => [
        'index' => [
            'meta_title' => 'Legal | Umbralia',
            'meta_description' => 'Legal information, privacy and important notices from Umbralia.',
            'title' => 'Umbralia legal information',
            'intro' => 'These texts summarize service limits, data use and basic conditions for booking online appointments.',
            'sections' => [
                [
                    'title' => 'Service scope',
                    'description' => 'Umbralia helps manage bookings, reminders and operational communication between patients and therapists.',
                    'items' => [
                        'It does not replace emergency services.',
                        'It does not provide immediate crisis care.',
                        'It does not process payments inside the platform.',
                    ],
                ],
                [
                    'title' => 'Main pages',
                    'description' => 'Review the terms, privacy policy and specific emergency notice before booking.',
                ],
            ],
        ],
        'terms' => [
            'meta_title' => 'Terms and conditions | Umbralia',
            'meta_description' => 'Umbralia usage conditions for booking online appointments.',
            'title' => 'Terms and conditions',
            'intro' => 'By booking an appointment you agree to use Umbralia as a booking and operational communication tool.',
            'sections' => [
                [
                    'title' => 'Platform use',
                    'description' => 'Umbralia lets you choose a therapist, session type, date and available time to confirm an online appointment.',
                ],
                [
                    'title' => 'Payments',
                    'description' => 'Umbralia does not process payments. The method, final amount and payment conditions are coordinated directly with the therapist.',
                ],
                [
                    'title' => 'Cancellation and rescheduling',
                    'description' => 'Available options depend on timing and the current policy shown on the corresponding information page.',
                ],
                [
                    'title' => 'Limits',
                    'description' => 'The platform is not designed for emergencies, crises or immediate-risk situations.',
                ],
            ],
        ],
        'privacy' => [
            'meta_title' => 'Privacy policy | Umbralia',
            'meta_description' => 'Information about personal data used to manage appointments in Umbralia.',
            'title' => 'Privacy policy',
            'intro' => 'We use the data needed to manage your appointment and send operational communications.',
            'sections' => [
                [
                    'title' => 'Data used',
                    'description' => 'Booking requires name, email, timezone and details of the selected appointment.',
                ],
                [
                    'title' => 'Purpose',
                    'description' => 'Data is used to confirm bookings, send reminders, show the public appointment page and support operational communication.',
                ],
                [
                    'title' => 'Communications',
                    'description' => 'By booking you agree to receive emails related to your appointment, such as confirmations, reminders and management links.',
                ],
            ],
        ],
        'emergency' => [
            'meta_title' => 'Emergency notice | Umbralia',
            'meta_description' => 'Important notice: Umbralia does not handle emergencies.',
            'title' => 'Emergency notice',
            'intro' => 'Umbralia is not an emergency service or immediate crisis-care channel.',
            'sections' => [
                [
                    'title' => 'Immediate risk',
                    'description' => 'If you or someone else is in danger, contact your local emergency services immediately.',
                ],
                [
                    'title' => 'Umbralia scope',
                    'description' => 'The platform only helps manage bookings, reminders and operational communication for scheduled online care.',
                ],
                [
                    'title' => 'Do not wait for a platform response',
                    'description' => 'Do not use Umbralia to request urgent help or immediate intervention.',
                ],
            ],
        ],
    ],
];
