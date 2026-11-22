<?php

namespace App\\Http\\Controllers\\Api;

use App\\Services\\WhatsAppService;
use Illuminate\\Http\\Request;
use Illuminate\\Support\\Facades\\Cache;
use Illuminate\\Support\\Facades\\Log;
use Symfony\\Component\\HttpFoundation\\Response;

class WhatsAppWebhookController
{
    public function verify(Request $request): Response
    {
        $mode = $request->query('hub.mode', $request->query('hub_mode'));
        $token = (string) $request->query('hub.verify_token', $request->query('hub_verify_token', ''));
        $challenge = (string) $request->query('hub.challenge', $request->query('hub_challenge', ''));
        $expectedToken = (string) config('services.whatsapp.webhook_verify_token', '');

        if ($mode === 'subscribe' && $expectedToken !== '' && hash_equals($expectedToken, $token)) {
            return response($challenge, 200, ['Content-Type' => 'text/plain']);
        }

        return response('Forbidden', 403, ['Content-Type' => 'text/plain']);
    }

    public function receive(Request $request, WhatsAppService $whatsAppService): Response
    {
        $appSecret = (string) config('services.whatsapp.app_secret', '');

        if ($appSecret === '') {
            return response('Webhook app secret is not configured', 503);
        }

        $signature = (string) $request->header('X-Hub-Signature-256', '');
        $expectedSignature = 'sha256=' . hash_hmac('sha256', $request->getContent(), $appSecret);

        if ($signature === '' || !hash_equals($expectedSignature, $signature)) {
            return response('Invalid signature', 401);
        }

        $payload = $request->json()->all();
        $entryCount = count($payload['entry'] ?? []);
        $messageCount = 0;
        $statusCount = 0;

        foreach (($payload['entry'] ?? []) as $entry) {
            foreach (($entry['changes'] ?? []) as $change) {
                $value = $change['value'] ?? [];
                $messages = $value['messages'] ?? [];
                $messageCount += count($messages);
                $statusCount += count($value['statuses'] ?? []);

                foreach ($messages as $message) {
                    if (($message['type'] ?? '') !== 'text') {
                        continue;
                    }

                    $messageId = (string) ($message['id'] ?? '');
                    $sender = (string) ($message['from'] ?? '');
                    $text = trim((string) ($message['text']['body'] ?? ''));

                    if ($messageId === '' || $sender === '' || $text === '') {
                        continue;
                    }

                    $cacheKey = 'whatsapp:incoming:' . hash('sha256', $messageId);
                    if (!Cache::add($cacheKey, true, now()->addDay())) {
                        continue;
                    }

                    $whatsAppService->replyToIncomingMessage($sender, $text);
                }
            }
        }

        Log::info('WhatsApp webhook received', [
            'object' => $payload['object'] ?? null,
            'entries' => $entryCount,
            'messages' => $messageCount,
            'statuses' => $statusCount,
        ]);

        return response('EVENT_RECEIVED', 200);
    }
}
