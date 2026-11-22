<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
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
            'entry' => [[
                'changes' => [[
                    'value' => [
                        'messages' => [['id' => 'wamid.test']],
                        'statuses' => [],
                    ],
                ]],
            ]],
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

    public function test_incoming_text_message_generates_and_sends_ai_reply(): void
    {
        Cache::flush();
        config([
            'services.whatsapp.app_secret' => 'test-app-secret',
            'services.whatsapp.access_token' => 'test-whatsapp-token',
            'services.whatsapp.phone_number_id' => '123456789',
            'services.whatsapp.graph_api_version' => 'v25.0',
            'services.openai.api_key' => 'test-openai-key',
            'services.openai.model' => 'gpt-4.1-mini',
        ]);

        Http::fake([
            'https://api.openai.com/v1/chat/completions' => Http::response([
                'choices' => [['message' => ['content' => 'Hello! How can UniGo help you?']]],
            ], 200),
            'https://graph.facebook.com/v25.0/123456789/messages' => Http::response([
                'messages' => [['id' => 'wamid.reply']],
            ], 200),
        ]);

        $payload = [
            'object' => 'whatsapp_business_account',
            'entry' => [[
                'changes' => [[
                    'value' => [
                        'messages' => [[
                            'id' => 'wamid.incoming-001',
                            'from' => '8801700000000',
                            'type' => 'text',
                            'text' => ['body' => 'Hello UniGo'],
                        ]],
                    ],
                ]],
            ]],
        ];
        $content = json_encode($payload);
        $signature = 'sha256=' . hash_hmac('sha256', $content, 'test-app-secret');

        $response = $this->call('POST', '/api/whatsapp/webhook', [], [], [], [
            'HTTP_X_HUB_SIGNATURE_256' => $signature,
            'CONTENT_TYPE' => 'application/json',
        ], $content);

        $response->assertOk();
        Http::assertSent(fn ($request) => $request->url() === 'https://api.openai.com/v1/chat/completions'
            && $request->hasHeader('Authorization', 'Bearer test-openai-key'));
        Http::assertSent(fn ($request) => $request->url() === 'https://graph.facebook.com/v25.0/123456789/messages'
            && $request['to'] === '8801700000000'
            && $request['text']['body'] === 'Hello! How can UniGo help you?');
    }

    public function test_duplicate_webhook_message_is_not_replied_to_twice(): void
    {
        Cache::flush();
        config([
            'services.whatsapp.app_secret' => 'test-app-secret',
            'services.whatsapp.access_token' => 'test-whatsapp-token',
            'services.whatsapp.phone_number_id' => '123456789',
            'services.openai.api_key' => '',
        ]);
        Http::fake(['https://graph.facebook.com/*' => Http::response(['messages' => [['id' => 'reply']]], 200)]);

        $payload = [
            'object' => 'whatsapp_business_account',
            'entry' => [[
                'changes' => [[
                    'value' => [
                        'messages' => [[
                            'id' => 'wamid.duplicate',
                            'from' => '8801700000000',
                            'type' => 'text',
                            'text' => ['body' => 'Hello'],
                        ]],
                    ],
                ]],
            ]],
        ];
        $content = json_encode($payload);
        $signature = 'sha256=' . hash_hmac('sha256', $content, 'test-app-secret');
        $headers = ['HTTP_X_HUB_SIGNATURE_256' => $signature, 'CONTENT_TYPE' => 'application/json'];

        $this->call('POST', '/api/whatsapp/webhook', [], [], [], $headers, $content)->assertOk();
        $this->call('POST', '/api/whatsapp/webhook', [], [], [], $headers, $content)->assertOk();

        Http::assertSentCount(1);
    }
}
