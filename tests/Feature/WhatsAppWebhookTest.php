<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class WhatsAppWebhookTest extends TestCase
{
    public function test_meta_webhook_verification_returns_challenge_for_matching_token(): void
    {
        config(['services.whatsapp.webhook_verify_token' => 'test-verify-token']);

        $response = $this->get('/api/whatsapp/webhook?hub.mode=subscribe&hub.verify_token=test-verify-token&hub.challenge=challenge-123');

        $response->assertOk();
        $response->assertSeeText('challenge-123');
    }

    public function test_meta_webhook_verification_rejects_wrong_token(): void
    {
        config(['services.whatsapp.webhook_verify_token' => 'test-verify-token']);

        $response = $this->get('/api/whatsapp/webhook?hub.mode=subscribe&hub.verify_token=wrong&hub.challenge=challenge-123');

        $response->assertForbidden();
    }

    public function test_webhook_rejects_invalid_signature(): void
    {
        config(['services.whatsapp.app_secret' => 'test-app-secret']);

        $response = $this->postJson('/api/whatsapp/webhook', ['object' => 'whatsapp_business_account'], [
            'X-Hub-Signature-256' => 'sha256=invalid',
        ]);

        $response->assertUnauthorized();
    }

    public function test_webhook_accepts_valid_signature_and_returns_event_received(): void
    {
        config(['services.whatsapp.app_secret' => 'test-app-secret']);
        Log::shouldReceive('info')->once()->with('WhatsApp webhook received', [
            'object' => 'whatsapp_business_account',
            'entries' => 1,
            'messages' => 1,
            'statuses' => 0,
        ]);

        $payload = [
            'object' => 'whatsapp_business_account',
            'entry' => [
                [
                    'changes' => [
                        [
                            'value' => [
                                'messages' => [['id' => 'wamid.test']],
                                'statuses' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ];
        $content = json_encode($payload);
        $signature = 'sha256=' . hash_hmac('sha256', $content, 'test-app-secret');

        $response = $this->call('POST', '/api/whatsapp/webhook', [], [], [], [
            'HTTP_X_HUB_SIGNATURE_256' => $signature,
            'CONTENT_TYPE' => 'application/json',
        ], $content);

        $response->assertOk();
        $response->assertSeeText('EVENT_RECEIVED');
    }
}
