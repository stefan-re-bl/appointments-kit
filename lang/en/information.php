<?php

return [
    'section_navigation' => 'Customer information navigation',
    'booking_policy_link' => 'Read about coordination, payments and changes',
    'nav' => [
        'patients' => 'For customers',
        'payment_and_cancellation' => 'Payment and policies',
    ],
    'faq' => [
        'view_all' => 'View all questions',
    ],
    'pages' => [
        'how_it_works' => [
            'meta_title' => 'How Appointments Kit works',
            'meta_description' => 'Learn each step to choose a service, schedule an appointment and receive confirmations.',
            'eyebrow' => 'How it works',
            'title' => 'From initial request to confirmed appointment',
            'intro' => 'The platform organizes services, professionals, schedules and communications in one simple flow.',
            'sections' => [
                ['title' => 'Choose a service', 'description' => 'Review the available options and select the service you need.'],
                ['title' => 'Choose a professional', 'description' => 'Review public profiles when the directory is enabled.'],
                ['title' => 'Select date and time', 'description' => 'Availability follows schedule rules and timezone settings.'],
                ['title' => 'Confirm your details', 'description' => 'Complete the information required to register the appointment.'],
                ['title' => 'Receive confirmation', 'description' => 'The system sends appointment information through enabled channels.'],
                ['title' => 'Manage allowed changes', 'description' => 'The platform applies configurable cancellation and rescheduling rules.'],
            ],
            'notice' => [
                'title' => 'The appointment may require preparation',
                'description' => 'Review the channel, time and instructions sent in the confirmation.',
            ],
            'cta' => [
                'title' => 'Would you like to book an appointment?',
                'description' => 'Start the booking flow and complete the requested details.',
                'label' => 'Book appointment',
            ],
        ],
        'patients' => [
            'meta_title' => 'Customer information | Appointments Kit',
            'meta_description' => 'Practical information to book, pay, cancel or reschedule an appointment.',
            'eyebrow' => 'For customers',
            'title' => 'What to know before your session',
            'intro' => 'Each business defines its services, schedule rules, payment methods and communication channels.',
            'sections' => [
                ['title' => 'Before starting', 'description' => 'Review services, professionals and times before confirming.', 'items' => ['Keep your contact details current.', 'Verify the displayed timezone.', 'Read the business cancellation rules.']],
                ['title' => 'During booking', 'description' => 'The flow shows the data required to register the appointment.', 'items' => ['Select an available option.', 'Complete required fields.', 'Confirm only if the data is correct.']],
                ['title' => 'Confirmation', 'description' => 'The platform registers the appointment and sends information through enabled channels.', 'items' => ['Save the link or instructions.', 'Check your email or WhatsApp if applicable.', 'Contact the business if you need help.']],
                ['title' => 'Later changes', 'description' => 'Cancellation and rescheduling depend on business rules.', 'items' => ['Respect the configured deadlines.', 'Use public links if enabled.', 'Coordinate exceptions with the business.']],
            ],
            'notice' => [
                'title' => 'Service depends on availability',
                'description' => 'Availability depends on each professional, service and business configuration.',
            ],
            'cta' => [
                'title' => 'Book when you find an available time',
                'description' => 'Choose the service, confirm your details and save the information sent.',
                'label' => 'Book appointment',
            ],
        ],
        'payment_and_cancellation' => [
            'meta_title' => 'Payment, cancellation and rescheduling | Appointments Kit',
            'meta_description' => 'Learn how payments, cancellations and appointment changes are coordinated.',
            'eyebrow' => 'Payment and policies',
            'title' => 'Clear coordination before starting',
            'intro' => 'The platform supports manual payment tracking. It does not process charges inside the system.',
            'sections' => [
                ['title' => 'Manual payment', 'description' => 'The business defines the payment method outside the platform.', 'items' => ['The platform does not store cards.', 'Administration can record payment status.', 'Receipts or agreements are managed outside the system.']],
                ['title' => 'Cancellation with refund eligibility', 'description' => 'Appointment cancellation with refund eligibility may be requested up to 24 hours before the agreed time.', 'items' => ['After that window, the appointment may be non-refundable.', 'Any refund is coordinated outside the platform.', 'The operational agreement remains between customer and business.']],
                ['title' => 'Schedules', 'description' => 'Schedules depend on professional availability and timezone.', 'items' => ['The platform shows available times.', 'The professional can manage their calendar.', 'Changes follow the configured policy.']],
                ['title' => 'Date or time changes', 'description' => 'Date or time changes may be requested up to 48 hours before the appointment.', 'items' => ['After that window, the change may be blocked.', 'Any exception depends on the business agreement.', 'Use the indicated channel to coordinate changes.']],
                ['title' => 'Operational record', 'description' => 'The platform can be used for public appointments or team-created appointments.', 'items' => ['Operational data is used to confirm appointments.', 'Administration can review statuses.', 'Each business defines its internal process.']],
            ],
            'notice' => [
                'title' => 'Payment and refund policy',
                'description' => 'Cancellation with refund eligibility may be requested up to 24 hours before the agreed time. Rescheduling may be requested up to 48 hours before the appointment. After those deadlines, any exception depends on the business agreement.',
            ],
            'cta' => [
                'title' => 'Would you like to book an appointment?',
                'description' => 'Choose a service and review the conditions before confirming.',
                'label' => 'Book appointment',
            ],
        ],
        'faq' => [
            'meta_title' => 'Frequently asked questions | Appointments Kit',
            'meta_description' => 'Answers about bookings, professionals, payments and schedule changes.',
            'eyebrow' => 'Frequently asked questions',
            'title' => 'Answers before starting',
            'intro' => 'These answers describe the general booking process.',
            'sections' => [
                ['title' => 'What does the platform do?', 'description' => 'It organizes services, professionals, schedules, bookings and communications.'],
                ['title' => 'How do I request an appointment?', 'description' => 'Use the booking flow and complete the requested details.'],
                ['title' => 'How is the professional selected?', 'description' => 'The customer chooses an available professional, or the business assigns one through its process.'],
                ['title' => 'Can I view professionals?', 'description' => 'Yes. You can review public profiles if the directory is enabled.'],
                ['title' => 'How is the schedule arranged?', 'description' => 'The time comes from configured availability.'],
                ['title' => 'Is payment made through the platform?', 'description' => 'No. The platform tracks manual payments, but does not process charges.'],
                ['title' => 'Are appointments online?', 'description' => 'Each business defines whether appointments are online, in person or mixed.'],
                ['title' => 'What if I need to change an appointment?', 'description' => 'Use public rescheduling if enabled, or contact the business.'],
                ['title' => 'What should I do in an emergency?', 'description' => 'Use the emergency channels that apply to the service and your location.'],
            ],
            'notice' => [
                'title' => 'Still need help?',
                'description' => 'Use the contact form or the email shown in the footer.',
            ],
            'cta' => [
                'title' => 'Would you like to start?',
                'description' => 'Review available services and book an appointment.',
                'label' => 'Book appointment',
            ],
        ],
    ],
];
