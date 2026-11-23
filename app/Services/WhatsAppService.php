<?php

namespace App\Services;

use App\Models\WhatsAppKnowledge;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
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
        $savedReply = $this->findSavedReply($message);

        if ($savedReply !== null) {
            Log::info('WhatsApp knowledge answer reused', ['knowledge_id' => $savedReply->id]);
            $savedReply->increment('use_count');
            return $savedReply->answer;
        }

        $apiKey = (string) config('services.gemini.api_key', '');

        if ($apiKey === '') {
            return 'Thank you for contacting UniGo. We have received your message. Our team will assist you shortly.';
        }

        $systemPrompt = implode("\n", [
            'You are UniGo AI, the helpful WhatsApp assistant for UniGo Tours & Travels in Bangladesh.',
            'Help users with visa consultation, travel planning, document checklists, tours, student programs, and general UniGo services.',
            'Be friendly, concise, professional, and reply in the language the customer uses (Bangla or English).',
            'Ask one clear follow-up question when essential details are missing.',
            'Never guarantee visa approval, invent official requirements, or claim to submit applications.',
            'For complex, personal, or time-sensitive visa questions, explain that requirements should be verified with official sources and offer human assistance.',
            'Keep WhatsApp replies short and easy to read. Do not use markdown tables.',
            'If a user asks to speak to a human, direct them to UniGo team support.',
            'Do not repeat or request sensitive personal data such as passport numbers, bank details, passwords, or OTPs.',
            'Do not present uncertain or outdated immigration information as confirmed fact.',
        ]);

        try {
            $model = (string) config('services.gemini.model', 'gemini-3.8-flash');
            $response = Http::withHeaders(['x-goog-api-key' => $apiKey])
                ->timeout((int) config('services.gemini.timeout', 25))
                ->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent", [
                    'systemInstruction' => [
                        'parts' => [['text' => $systemPrompt]],
                    ],
                    'contents' => [
                        ['role' => 'user', 'parts' => [['text' => $message]]],
                    ],
                    'generationConfig' => [
                        'temperature' => 0.4,
                        'maxOutputTokens' => 500,
                    ],
                ]);

            if (!$response->successful()) {
                Log::warning('UniGo Gemini reply generation failed', [
                    'status' => $response->status(),
                    'model' => $model,
                ]);
                return 'Thank you for contacting UniGo. We have received your message. Our team will assist you shortly.';
            }

            $reply = trim((string) $response->json('candidates.0.content.parts.0.text', ''));

            if ($reply === '') {
                return 'Thank you for contacting UniGo. Our team will assist you shortly.';
            }

            $reply = mb_substr($reply, 0, 4000);
            $this->saveKnowledgeAutomatically($message, $reply);

            return $reply;
        } catch (Throwable $exception) {
            Log::warning('UniGo Gemini reply generation error', ['error' => $exception->getMessage()]);
            return 'Thank you for contacting UniGo. We have received your message. Our team will assist you shortly.';
        }
    }

    private function findSavedReply(string $message): ?WhatsAppKnowledge
    {
        $normalized = $this->normalizeQuestion($message);

        if ($normalized === '') {
            return null;
        }

        $exact = WhatsAppKnowledge::query()
            ->where('normalized_question_hash', hash('sha256', $normalized))
            ->first();

        if ($exact) {
            return $exact;
        }

        $tokens = $this->questionTokens($normalized);

        if (count($tokens) < 3) {
            return null;
        }

        $candidates = WhatsAppKnowledge::query()
            ->latest('id')
            ->limit(300)
            ->get(['id', 'question', 'normalized_question', 'answer', 'use_count']);

        $best = null;
        $bestScore = 0.0;

        foreach ($candidates as $candidate) {
            $candidateTokens = $this->questionTokens($candidate->normalized_question);
            if (count($candidateTokens) < 3) {
                continue;
            }

            $intersection = count(array_intersect($tokens, $candidateTokens));
            $union = count(array_unique(array_merge($tokens, $candidateTokens)));
            $jaccard = $union > 0 ? $intersection / $union : 0.0;
            $containment = min(count($tokens), count($candidateTokens)) > 0
                ? $intersection / min(count($tokens), count($candidateTokens))
                : 0.0;
            $score = max($jaccard, $containment * 0.85);

            if ($score > $bestScore) {
                $bestScore = $score;
                $best = $candidate;
            }
        }

        return $bestScore >= 0.78 ? $best : null;
    }

    private function saveKnowledgeAutomatically(string $question, string $answer): void
    {
        if (!$this->isReusableQuestion($question)) {
            return;
        }

        $normalized = $this->normalizeQuestion($question);

        if ($normalized === '' || WhatsAppKnowledge::where('normalized_question', $normalized)->exists()) {
            return;
        }

        try {
            WhatsAppKnowledge::create([
                'question' => mb_substr(trim($question), 0, 1000),
                'normalized_question' => $normalized,
                'normalized_question_hash' => hash('sha256', $normalized),
                'answer' => $answer,
                'use_count' => 0,
            ]);
        } catch (Throwable $exception) {
            Log::warning('WhatsApp knowledge auto-save failed', ['error' => $exception->getMessage()]);
        }
    }

    private function isReusableQuestion(string $question): bool
    {
        $text = trim($question);

        if (preg_match('/[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}/i', $text)) {
            return false;
        }

        if (preg_match('/(?:\+?\d[\d\s().-]{7,}\d)/', $text)) {
            return false;
        }

        if (preg_match('/\b(passport|application|file|case|tracking|reference|bank|account|nid|national id|personal)\s*(?:number|no\.?|details|status)?\b/i', $text)) {
            return false;
        }

        return mb_strlen($text) <= 500;
    }

    private function normalizeQuestion(string $question): string
    {
        $question = mb_strtolower(trim($question));
        $question = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $question);
        $question = preg_replace('/\s+/u', ' ', $question);
        return trim($question);
    }

    private function questionTokens(string $question): array
    {
        $tokens = preg_split('/\s+/u', $question, -1, PREG_SPLIT_NO_EMPTY);
        return array_values(array_unique(array_filter($tokens, fn ($token) => mb_strlen($token) > 1)));
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
