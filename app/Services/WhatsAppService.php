<?php

namespace App\\Services;

use Illuminate\\Support\\Facades\\Http;
use Illuminate\\Support\\Facades\\Log;
use Throwable;

class WhatsAppService
{
    public function replyToIncomingMessage(string $recipient, string $message): void
    {
        $reply = $this->generateReply($message);
        $this->sendTextMessage($recipient, $reply);
    }

    public function generateReply(string $message): string
    {
        $apiKey = (string) config('services.openai.api_key', '');

        if ($apiKey === '') {
            return 'Thank you for contacting UniGo. We have received your message. Our team will assist you shortly.';
        }

        $systemPrompt = implode("\\n", [
            'You are UniGo AI, the helpful WhatsApp assistant for UniGo Tours & Travels in Bangladesh.',
            'Help users with visa consultation, travel planning, document checklists, tours, student programs, and general UniGo services.',
            'Be friendly, concise, professional, and reply in the language the customer uses (Bangla or English).',
            'Ask one clear follow-up question when essential details are missing.',
            'Never guarantee visa approval, invent official requirements, or claim to submit applications.',
            'For complex, personal, or time-sensitive visa questions, explain that requirements should be verified with official sources and offer human assistance.',
            'Keep WhatsApp replies short and easy to read. Do not use markdown tables.',
            'If a user asks to speak to a human, direct them to UniGo team support.',
        ]);

        try {
            $response = Http::withToken($apiKey)
                ->timeout((int) config('services.openai.timeout', 25))
                ->post((string) config('services.openai.chat_completions_url'), [
                    'model' => (string) config('services.openai.model', 'gpt-4.1-mini'),
                    'messages' => [
                        ['role' => 'system', 'content' => $systemPrompt],
                        ['role' => 'user', 'content' => $message],
                    ],
                    'temperature' => 0.4,
                    'max_tokens' => 500,
                ]);

            if (!$response->successful()) {
                Log::warning('UniGo AI reply generation failed', ['status' => $response->status()]);
                return 'Thank you for contacting UniGo. We have received your message. Our team will assist you shortly.';
            }

            $reply = trim((string) $response->json('choices.0.message.content', ''));

            return $reply !== ''
                ? mb_substr($reply, 0, 4000)
                : 'Thank you for contacting UniGo. Our team will assist you shortly.';
        } catch (Throwable $exception) {
            Log::warning('UniGo AI reply generation error', ['error' => $exception->getMessage()]);
            return 'Thank you for contacting UniGo. We have received your message. Our team will assist you shortly.';
        }
    }

    public function sendTextMessage(string $recipient, string $message): bool
    {
        $accessToken = (string) config('services.whatsapp.access_token', '');
        $phoneNumberId = (string) config('services.whatsapp.phone_number_id', '');

        if ($accessToken === '' || $phoneNumberId === '') {
            Log::warning('WhatsApp sending is not configured');
            return false;
        }

        try {
            $version = (string) config('services.whatsapp.graph_api_version', 'v25.0');
            $response = Http::withToken($accessToken)
                ->timeout((int) config('services.whatsapp.timeout', 20))
                ->post("https://graph.facebook.com/{$version}/{$phoneNumberId}/messages", [
                    'messaging_product' => 'whatsapp',
                    'recipient_type' => 'individual',
                    'to' => $recipient,
                    'type' => 'text',
                    'text' => [
                        'preview_url' => false,
                        'body' => mb_substr($message, 0, 4000),
                    ],
                ]);

            if (!$response->successful()) {
                Log::warning('WhatsApp message send failed', ['status' => $response->status()]);
                return false;
            }

            return true;
        } catch (Throwable $exception) {
            Log::warning('WhatsApp message send error', ['error' => $exception->getMessage()]);
            return false;
        }
    }
}
