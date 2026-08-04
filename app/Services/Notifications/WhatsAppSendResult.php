<?php

declare(strict_types=1);

namespace App\Services\Notifications;

final readonly class WhatsAppSendResult
{
    public function __construct(
        public bool $successful,
        public ?string $providerMessageId = null,
        public ?string $errorCode = null,
        public ?string $errorMessage = null,
        public bool $retryable = false,
    ) {}
}
