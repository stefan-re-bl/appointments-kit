<?php

return [
    'section_navigation' => 'Patient information navigation',
    'booking_policy_link' => 'Read about payment, cancellation and rescheduling',
    'nav' => [
        'patients' => 'For patients',
        'payment_and_cancellation' => 'Payment and policies',
    ],
    'faq' => [
        'view_all' => 'View all questions',
    ],
    'pages' => [
        'how_it_works' => [
            'meta_title' => 'How Umbralia works',
            'meta_description' => 'Learn every step to choose a therapist, book an online session and manage your appointment.',
            'eyebrow' => 'How it works',
            'title' => 'From choosing a therapist to your online session',
            'intro' => 'Umbralia organizes booking and appointment communications. Professional care and payment are arranged directly with the therapist.',
            'sections' => [
                ['title' => 'Choose a therapist', 'description' => 'Open the booking flow and review the active professionals available before continuing.'],
                ['title' => 'Choose a session type', 'description' => 'Each therapist configures consultation types, duration, price and currency. Select the right option for you.'],
                ['title' => 'Select a date and time', 'description' => 'The system displays actual availability and converts times to the timezone detected on your device.'],
                ['title' => 'Confirm your details', 'description' => 'Enter your name and email. Before booking, review the therapist, date, time, duration and price.'],
                ['title' => 'Receive confirmation', 'description' => 'The appointment is confirmed and you receive “My Appointment”, online access and operational information.'],
                ['title' => 'Arrange payment', 'description' => 'Umbralia does not charge inside the platform. The therapist agrees on payment with you and records its status.'],
            ],
            'notice' => [
                'title' => 'The session takes place online',
                'description' => 'Use a private space, stable connection and the link provided by email or on “My Appointment”.',
            ],
            'cta' => [
                'title' => 'Ready to find a time?',
                'description' => 'Review therapists, session types and availability before confirming.',
                'label' => 'Start booking',
            ],
        ],
        'patients' => [
            'meta_title' => 'Patient information | Umbralia',
            'meta_description' => 'Practical information for preparing, booking and managing an online psychological care session.',
            'eyebrow' => 'For patients',
            'title' => 'What to know before your session',
            'intro' => 'The platform simplifies appointment management. The therapeutic relationship, clinical guidance and payment agreements belong to the selected professional.',
            'sections' => [
                ['title' => 'Before booking', 'description' => 'Review session type, duration, price and availability. Times are shown in your timezone.', 'items' => ['Use an email address you can access.', 'Confirm the date and time.', 'Remember payment is arranged externally.']],
                ['title' => 'After confirming', 'description' => 'You receive an email with appointment details and a personal “My Appointment” link.', 'items' => ['Keep the confirmation email.', 'Do not share the personal link.', 'Review payment status and the video call link.']],
                ['title' => 'For the video call', 'description' => 'Prepare a place where you can talk privately and without interruptions.', 'items' => ['Test camera, microphone and connection.', 'Join a few minutes early.', 'Contact the therapist if the link fails.']],
                ['title' => 'Managing your appointment', 'description' => '“My Appointment” shows details and actions based on status and notice time.', 'items' => ['Rescheduling requires at least 24 hours.', 'Self-service cancellation requires at least 48 hours.', 'A maximum of two reschedules per appointment.']],
            ],
            'notice' => [
                'title' => 'Not an emergency service',
                'description' => 'Umbralia does not provide immediate crisis care. If there is immediate danger, contact local emergency services.',
            ],
            'cta' => [
                'title' => 'Book when you are ready',
                'description' => 'The flow lets you review all information before confirming.',
                'label' => 'View therapists',
            ],
        ],
        'payment_and_cancellation' => [
            'meta_title' => 'Payment, cancellation and rescheduling | Umbralia',
            'meta_description' => 'Learn how payments are arranged and the rules for cancellation and rescheduling.',
            'eyebrow' => 'Payment and policies',
            'title' => 'Clear rules for managing your appointment',
            'intro' => 'Umbralia records appointments and payment statuses, but does not process charges or refunds inside the platform.',
            'sections' => [
                ['title' => 'Payment outside Umbralia', 'description' => 'After booking, the therapist arranges payment method and terms directly with you.', 'items' => ['The appointment is confirmed while payment remains pending.', 'The therapist updates payment status manually.', 'Umbralia does not store cards or process transfers.']],
                ['title' => 'Cancellation with 48 hours', 'description' => 'You can cancel from “My Appointment” when at least 48 hours remain and the appointment is active.', 'items' => ['Without recorded payment there is no refund pending.', 'With recorded payment, any refund is arranged manually with the therapist.']],
                ['title' => 'Rescheduling with 24 hours', 'description' => 'You can choose another available time when at least 24 hours remain.', 'items' => ['Session type, price and payment status remain unchanged.', 'Each appointment allows at most two reschedules.', 'Signed links are personal and expire after 24 hours.']],
                ['title' => 'Outside the deadlines', 'description' => 'If fewer than 24 hours remain or the limit is reached, automatic options are unavailable.', 'items' => ['Contact the therapist directly.', 'The platform does not promise refunds outside policy.', 'Exceptions are arranged with the professional.']],
            ],
            'notice' => [
                'title' => 'Refunds are not automatic',
                'description' => 'Even when a refund should be coordinated, money moves outside Umbralia.',
            ],
            'cta' => [
                'title' => 'Review the terms before booking',
                'description' => 'The final summary displays price, duration and the payment coordination notice.',
                'label' => 'Find a session',
            ],
        ],
        'faq' => [
            'meta_title' => 'Frequently asked questions | Umbralia',
            'meta_description' => 'Answers about online sessions, booking, payment, timezones, cancellation and rescheduling.',
            'eyebrow' => 'Frequently asked questions',
            'title' => 'Answers before booking',
            'intro' => 'These answers describe how Umbralia operates. Ask the therapist about clinical matters or individual agreements.',
            'sections' => [
                ['title' => 'Does Umbralia provide therapy?', 'description' => 'Umbralia facilitates appointment management. Psychological care is provided by the selected therapist.'],
                ['title' => 'Are sessions online?', 'description' => 'Yes. The video call link appears in the confirmation email and on “My Appointment” when available.'],
                ['title' => 'How do I choose a therapist?', 'description' => 'The booking flow shows active professionals, followed by session type, date and time.'],
                ['title' => 'Are times shown in my timezone?', 'description' => 'Yes. The system detects the device timezone and converts times automatically.'],
                ['title' => 'Is payment made through Umbralia?', 'description' => 'No. Payment is arranged directly with the therapist, who records its status manually.'],
                ['title' => 'When can I cancel?', 'description' => 'Self-service cancellation is available at least 48 hours before an active appointment.'],
                ['title' => 'When can I reschedule?', 'description' => 'With at least 24 hours notice and a maximum of two changes per appointment.'],
                ['title' => 'What if I do not receive the email?', 'description' => 'Check spam. If the issue continues, contact the therapist or administration.'],
                ['title' => 'What should I do in an emergency?', 'description' => 'Do not use Umbralia for immediate crises. Contact local emergency services.'],
            ],
            'notice' => [
                'title' => 'Still need help?',
                'description' => 'Until the support form is implemented, use the contact email shown in the footer.',
            ],
            'cta' => [
                'title' => 'Have the information you need?',
                'description' => 'Start booking and review availability without commitment.',
                'label' => 'Start booking',
            ],
        ],
    ],
];
