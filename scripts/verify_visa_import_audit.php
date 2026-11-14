<?php

$path = $argv[1] ?? '';
$fail = static function (string $message): never {
    fwrite(STDERR, $message . PHP_EOL);
    exit(1);
};

if ($path === '' || ! is_file($path)) {
    $fail('Post-import audit export file is missing.');
}

$payload = json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
$counts = $payload['verification_status_counts'] ?? [];
$expected = [
    'total' => 59,
    'verified' => 53,
    'pending_review' => 6,
    'published_count' => 53,
    'public_country_directory_count' => 53,
    'country_table_count' => 249,
];

$actual = [
    'total' => (int) ($payload['total'] ?? -1),
    'verified' => (int) ($counts['verified'] ?? 0),
    'pending_review' => (int) ($counts['pending_review'] ?? 0),
    'published_count' => (int) ($payload['published_count'] ?? -1),
    'public_country_directory_count' => (int) ($payload['public_country_directory_count'] ?? -1),
    'country_table_count' => (int) ($payload['country_table_count'] ?? -1),
];

foreach ($expected as $field => $value) {
    if ($actual[$field] !== $value) {
        $fail('Post-import ' . $field . ' mismatch: expected ' . $value . ', got ' . $actual[$field]);
    }
}

$publishedTargets = ['ID', 'MU', 'NP', 'PH', 'TR', 'BN', 'RW', 'TZ', 'HK', 'MO', 'UZ', 'KR'];
$pendingTargets = ['LK', 'BT', 'KZ', 'QA', 'SA', 'JO'];
$targets = array_fill_keys(array_merge($publishedTargets, $pendingTargets), true);
$found = [];
$errors = [];

foreach (($payload['records'] ?? []) as $record) {
    if (($record['passport_nationality_iso3'] ?? '') !== 'BGD' || ($record['travel_purpose'] ?? '') !== 'tourism') {
        continue;
    }
    $iso2 = strtoupper((string) ($record['country']['iso2'] ?? ''));
    if (! isset($targets[$iso2])) {
        continue;
    }
    if (isset($found[$iso2])) {
        $errors[] = 'Duplicate target record: ' . $iso2;
        continue;
    }
    $found[$iso2] = true;
    $shouldPublish = in_array($iso2, $publishedTargets, true);
    $status = $record['verification_status'] ?? '';
    $published = (bool) ($record['is_published'] ?? false);
    if ($shouldPublish && ($status !== 'verified' || ! $published)) {
        $errors[] = $iso2 . ' should be verified and published.';
    }
    if (! $shouldPublish && ($status !== 'pending_review' || $published)) {
        $errors[] = $iso2 . ' must remain pending_review and unpublished.';
    }
    $sources = $record['sources'] ?? [];
    $official = array_values(array_filter($sources, static fn ($source) => ($source['source_type'] ?? '') === 'official' && str_starts_with((string) ($source['source_url'] ?? ''), 'https://')));
    $primary = array_values(array_filter($official, static fn ($source) => (bool) ($source['is_primary'] ?? false)));
    if (count($official) < 2 || count($primary) !== 1) {
        $errors[] = $iso2 . ' does not have at least two HTTPS official sources and exactly one official primary source.';
    }
    foreach ($official as $source) {
        if (substr((string) ($source['source_last_checked_at'] ?? ''), 0, 10) !== '2026-11-14') {
            $errors[] = $iso2 . ' has an official source not checked on 2026-11-14.';
            break;
        }
    }
}

$missing = array_values(array_diff(array_keys($targets), array_keys($found)));
if ($missing !== []) {
    $errors[] = 'Missing target records: ' . implode(',', $missing);
}
if ($errors !== []) {
    $fail(implode(PHP_EOL, $errors));
}

echo 'Post-import audit passed: 59 total records; 53 verified/published; 6 pending_review/unpublished; 249 country rows; all 18 target records and official sources read back.' . PHP_EOL;
