<?php

use App\Models\VisaRequirement;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

$root = getenv('APP_ROOT') ?: dirname(__DIR__);
require $root . '/vendor/autoload.php';
$app = require $root . '/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$api = static function (string $method, string $url, array $payload, string $secret): array {
    $body = $payload === [] ? '' : json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    $timestamp = (string) now()->timestamp;
    $signature = hash_hmac('sha256', $timestamp . '.' . $body, $secret);
    $request = Http::timeout(45)->acceptJson()->withHeaders([
        'X-UniGo-Timestamp' => $timestamp,
        'X-UniGo-Signature' => $signature,
        'X-UniGo-Admin-User' => 'UniGo Automation',
        'Content-Type' => 'application/json',
    ]);
    $response = strtoupper($method) === 'GET'
        ? $request->get($url)
        : $request->withBody($body, 'application/json')->send(strtoupper($method), $url);
    if (! $response->successful()) {
        throw new RuntimeException('Signed admin API request failed with HTTP ' . $response->status() . '.');
    }
    return $response->json() ?? [];
};

try {
    $base = rtrim((string) config('app.url'), '/');
    $secret = (string) config('services.unigo_admin.secret');
    if ($base === '' || $secret === '') {
        throw new RuntimeException('Configured admin API URL or signing secret is unavailable.');
    }

    $matches = VisaRequirement::query()
        ->with('country:id,iso2,name,slug')
        ->where('passport_nationality_iso3', 'BGD')
        ->where('travel_purpose', 'tourism')
        ->get()
        ->filter(fn ($row) => strtoupper((string) $row->country?->iso2) === 'MY');

    if ($matches->count() !== 1) {
        throw new RuntimeException('Expected exactly one Malaysia BGD tourism record.');
    }

    $record = $matches->first();
    if ($record->verification_status === 'pending_review' && ! (bool) $record->is_published) {
        echo json_encode([
            'country_iso2' => 'MY',
            'verification_status' => 'pending_review',
            'is_published' => false,
            'already_withheld' => true,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
        exit(0);
    }

    if ($record->verification_status !== 'verified' || ! (bool) $record->is_published) {
        throw new RuntimeException('Malaysia record is not in the expected verified/published preflight state.');
    }

    $id = (int) $record->id;
    $api('POST', $base . '/api/admin/visa-requirements/' . $id . '/unpublish', [], $secret);
    $api('POST', $base . '/api/admin/visa-requirements/' . $id . '/verification', [
        'verification_status' => 'pending_review',
        'change_summary' => 'Publication withheld pending written clarification from the High Commission of Malaysia in Dhaka. The current MYVISA terms state a 6-month single-entry visa validity while the Dhaka FAQ states 3 months; the Dhaka checklist pages also differ on whether 3 or 6 months of bank statements are required. Keep the Bangladesh social-visit record unpublished until the controlling route-specific requirements are confirmed.',
        'official_source_checked_at' => '2026-11-15',
    ], $secret);

    $saved = data_get($api('GET', $base . '/api/admin/visa-requirements/' . $id, [], $secret), 'data', []);
    if (! is_array($saved) || ($saved['verification_status'] ?? null) !== 'pending_review' || (bool) ($saved['is_published'] ?? false)) {
        throw new RuntimeException('Malaysia did not return to pending_review and unpublished status.');
    }

    echo json_encode([
        'country_iso2' => 'MY',
        'passport_iso3' => 'BGD',
        'travel_purpose' => 'tourism',
        'verification_status' => $saved['verification_status'],
        'is_published' => (bool) $saved['is_published'],
        'change_summary' => 'Publication withheld pending official clarification of the 3-month/6-month SEV validity and bank-statement conflicts.',
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL;
} catch (Throwable $exception) {
    fwrite(STDERR, 'BATCH55_UNPUBLISH_ERROR ' . get_class($exception) . ': ' . $exception->getMessage() . PHP_EOL);
    exit(1);
}
