<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'meta_whatsapp' => [
        'enabled' => env('WHATSAPP_ENABLED', false),
        'graph_version' => env('META_WHATSAPP_GRAPH_VERSION', 'v23.0'),
        'phone_number_id' => env('META_WHATSAPP_PHONE_NUMBER_ID'),
        'business_account_id' => env('META_WHATSAPP_BUSINESS_ACCOUNT_ID'),
        'access_token' => env('META_WHATSAPP_ACCESS_TOKEN'),
        'webhook_verify_token' => env('META_WHATSAPP_WEBHOOK_VERIFY_TOKEN'),
        'app_secret' => env('META_APP_SECRET'),
        'timeout' => env('META_WHATSAPP_TIMEOUT', 10),
        'connect_timeout' => env('META_WHATSAPP_CONNECT_TIMEOUT', 5),
        'language_codes' => [
            'es' => env('META_WHATSAPP_LANGUAGE_ES', 'es'),
            'en' => env('META_WHATSAPP_LANGUAGE_EN', 'en_US'),
        ],
        'templates' => [
            'customer_confirmation_es' => env('META_WHATSAPP_TEMPLATE_CUSTOMER_CONFIRMATION_ES', env('META_WHATSAPP_TEMPLATE_PATIENT_CONFIRMATION_ES')),
            'customer_confirmation_en' => env('META_WHATSAPP_TEMPLATE_CUSTOMER_CONFIRMATION_EN', env('META_WHATSAPP_TEMPLATE_PATIENT_CONFIRMATION_EN')),
            'professional_confirmation_es' => env('META_WHATSAPP_TEMPLATE_PROFESSIONAL_CONFIRMATION_ES'),
            'professional_confirmation_en' => env('META_WHATSAPP_TEMPLATE_PROFESSIONAL_CONFIRMATION_EN'),
            'customer_reminder_es' => env('META_WHATSAPP_TEMPLATE_CUSTOMER_REMINDER_ES', env('META_WHATSAPP_TEMPLATE_PATIENT_REMINDER_ES')),
            'customer_reminder_en' => env('META_WHATSAPP_TEMPLATE_CUSTOMER_REMINDER_EN', env('META_WHATSAPP_TEMPLATE_PATIENT_REMINDER_EN')),
            'professional_reminder_es' => env('META_WHATSAPP_TEMPLATE_PROFESSIONAL_REMINDER_ES'),
            'professional_reminder_en' => env('META_WHATSAPP_TEMPLATE_PROFESSIONAL_REMINDER_EN'),
        ],
    ],

];
