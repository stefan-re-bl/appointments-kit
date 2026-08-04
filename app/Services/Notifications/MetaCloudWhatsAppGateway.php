<?php

declare(strict_types=1);

namespace App\Services\Notifications;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Throwable;

final class MetaCloudWhatsAppGateway implements WhatsAppGateway
{
    public function sendTemplate(string $recipientPhone, WhatsAppTemplate $template): WhatsAppSendResult
    {
        $phoneNumberId = trim((string) config('services.meta_whatsapp.phone_number_id'));
        $graphVersion = trim((string) config('services.meta_whatsapp.graph_version', 'v23.0'));
        $accessToken = (string) config('services.meta_whatsapp.access_token');

        if ($phoneNumberId === '' || $accessToken === '') {
            return new WhatsAppSendResult(false, errorCode: 'missing_configuration', errorMessage: 'Meta WhatsApp configuration is incomplete.');
        }

        $payload = [
            'messaging_product' => 'whatsapp',
            'to' => ltrim($recipientPhone, '+'),
            'type' => 'template',
            'template' => [
                'name' => $template->name,
                'language' => ['code' => $template->metaLanguageCode],
                'components' => [[
                    'type' => 'body',
                    'parameters' => array_map(
                        static fn (string $value): array => ['type' => 'text', 'text' => $value],
                        $template->variables,
                    ),
                ]],
            ],
        ];

        try {
            $response = Http::withToken($accessToken)
                ->acceptJson()
                ->asJson()
                ->connectTimeout((int) config('services.meta_whatsapp.connect_timeout', 5))
                ->timeout((int) config('services.meta_whatsapp.timeout', 10))
                ->post("https://graph.facebook.com/{$graphVersion}/{$phoneNumberId}/messages", $payload);
        } catch (ConnectionException $exception) {
            return new WhatsAppSendResult(false, errorCode: 'connection_error', errorMessage: $exception->getMessage(), retryable: true);
        } catch (Throwable $exception) {
            return new WhatsAppSendResult(false, errorCode: 'unexpected_error', errorMessage: $exception->getMessage());
        }

        if ($response->successful()) {
            $messageId = $response->json('messages.0.id');

            return new WhatsAppSendResult(true, is_string($messageId) ? $messageId : null);
        }

        $errorCode = $response->json('error.code');
        $errorMessage = $response->json('error.message');
        $status = $response->status();

        return new WhatsAppSendResult(
            successful: false,
            errorCode: is_scalar($errorCode) ? (string) $errorCode : 'http_'.$status,
            errorMessage: is_scalar($errorMessage) ? (string) $errorMessage : 'Meta WhatsApp API request failed.',
            retryable: $status === 429 || $status >= 500,
        );
    }
}
