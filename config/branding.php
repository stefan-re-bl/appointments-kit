<?php

declare(strict_types=1);

return [
    'name' => env('BRANDING_NAME', env('APP_NAME', 'Appointments Kit')),
    'logo' => env('BRANDING_LOGO', 'images/client-logo.png'),
    'favicon' => env('BRANDING_FAVICON', 'favicon.png'),

    'images' => [
        'home_hero' => env('BRANDING_HOME_HERO_IMAGE', 'images/client-home-hero.jpg'),
        'information_hero' => env('BRANDING_INFORMATION_HERO_IMAGE', 'images/client-information-hero.jpg'),
    ],

    'videos' => [
        'intro' => env('BRANDING_INTRO_VIDEO', 'videos/client-intro.mp4'),
    ],

    'contact' => [
        'whatsapp_number' => env('BRANDING_WHATSAPP_NUMBER'),
        'instagram_url' => env('BRANDING_INSTAGRAM_URL'),
    ],
];
