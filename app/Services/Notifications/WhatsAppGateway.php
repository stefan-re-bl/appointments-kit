<?php

declare(strict_types=1);

namespace App\Services\Notifications;

interface WhatsAppGateway
{
    public function sendTemplate(string $recipientPhone, WhatsAppTemplate $template): WhatsAppSendResult;
}
