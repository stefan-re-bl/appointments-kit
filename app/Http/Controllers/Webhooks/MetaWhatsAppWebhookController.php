<?php

declare(strict_types=1);

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Jobs\Notifications\ProcessMetaWhatsAppWebhook;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

final class MetaWhatsAppWebhookController extends Controller
{
    public function verify(Request $request): Response
    {
        $mode = $request->query('hub_mode') ?? $request->query('hub.mode');
        $token = $request->query('hub_verify_token') ?? $request->query('hub.verify_token');
        $challenge = $request->query('hub_challenge') ?? $request->query('hub.challenge');

        if ($mode === 'subscribe'
            && is_string($token)
            && hash_equals((string) config('services.meta_whatsapp.webhook_verify_token'), $token)
            && is_scalar($challenge)) {
            return response((string) $challenge, 200);
        }

        return response('Forbidden', 403);
    }

    public function receive(Request $request): Response
    {
        if (! $this->hasValidSignature($request)) {
            Log::warning('Rejected Meta WhatsApp webhook with invalid signature.');

            return response('Forbidden', 403);
        }

        $payload = $request->all();

        if (! is_array($payload)) {
            return response('Bad Request', 400);
        }

        ProcessMetaWhatsAppWebhook::dispatch($payload);

        return response('OK', 200);
    }

    private function hasValidSignature(Request $request): bool
    {
        $appSecret = (string) config('services.meta_whatsapp.app_secret');

        if ($appSecret === '') {
            return false;
        }

        $signature = (string) $request->header('X-Hub-Signature-256', '');

        if (! str_starts_with($signature, 'sha256=')) {
            return false;
        }

        $expected = 'sha256='.hash_hmac('sha256', $request->getContent(), $appSecret);

        return hash_equals($expected, $signature);
    }
}
