<?php

declare(strict_types=1);

return [
    'public_information_pages' => env('FEATURE_PUBLIC_INFORMATION_PAGES', true),
    'public_faq' => env('FEATURE_PUBLIC_FAQ', true),
    'public_contact_form' => env('FEATURE_PUBLIC_CONTACT_FORM', true),
    'public_provider_directory' => env('FEATURE_PUBLIC_PROVIDER_DIRECTORY', true),
    'intro_video' => env('FEATURE_INTRO_VIDEO', true),
    'whatsapp' => env('FEATURE_WHATSAPP', env('WHATSAPP_ENABLED', false)),
    'email_notifications' => env('FEATURE_EMAIL_NOTIFICATIONS', true),
    'manual_payments' => env('FEATURE_MANUAL_PAYMENTS', true),
    'public_rescheduling' => env('FEATURE_PUBLIC_RESCHEDULING', true),
    'public_cancellation' => env('FEATURE_PUBLIC_CANCELLATION', true),
    'reports' => env('FEATURE_REPORTS', true),
    'visible_audit' => env('FEATURE_VISIBLE_AUDIT', true),
];
