<?php

declare(strict_types=1);

namespace App\Services\Notifications;

final readonly class WhatsAppTemplate
{
    /**
     * @param  array<int, string>  $variables
     */
    public function __construct(
        public string $name,
        public string $metaLanguageCode,
        public array $variables,
    ) {}
}
