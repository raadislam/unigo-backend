<?php

$path = $argv[1] ?? '';
$expectedTotal = (int) ($argv[2] ?? 0);
$expectedPublished = (int) ($argv[3] ?? 0);
$expectedCountryTable = (int) ($argv[4] ?? 0);
$targets = array_values(array_filter(array_map('strtoupper', explode(',', $argv[5] ?? ''))));

$fail = static function (string $message): never {
    fwrite(STDERR, $message . PHP_EOL);
    exit(1);
};

if ($path === '' || ! is_file($path)) {
    $fail('Audit export file is missing.');
}

$payload = json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
$counts = $payload['verification_status_counts'] ?? [];

if ((int) ($payload['total'] ?? -1) !== $expectedTotal) {
    $fail('Requirement total changed; manual reconciliation is required.');
}
if ((int) ($payload['published_count'] ?? -1) !== $expectedPublished) {
    $fail('Published requirement total changed; manual reconciliation is required.');
}
if ((int) ($payload['public_country_directory_count'] ?? -1) !== $expectedPublished) {
    $fail('Public country directory total does not match the expected audit baseline.');
}
if ((int) ($payload['country_table_count'] ?? -1) !== $expectedCountryTable) {
    $fail('Country table total changed; manual reconciliation is required.');
}
if ((int) ($counts['verified'] ?? 0) !== $expectedPublished || (int) ($counts['pending_review'] ?? 0) !== 0) {
    $fail('The current verification-status mix differs from the reviewed baseline.');
}

$targetSet = array_fill_keys($targets, true);
$existing = [];
foreach (($payload['records'] ?? []) as $record) {
    if (($record['passport_nationality_iso3'] ?? '') !== 'BGD' || ($record['travel_purpose'] ?? '') !== 'tourism') {
        continue;
    }
    $iso2 = strtoupper((string) ($record['country']['iso2'] ?? ''));
    if (isset($targetSet[$iso2])) {
        $existing[] = $iso2;
    }
}
if ($existing !== []) {
    $fail('Target BGD tourism records already exist; refusing to overwrite: ' . implode(',', array_unique($existing)));
}

$countryRows = [];
foreach (($payload['country_table'] ?? []) as $country) {
    $iso2 = strtoupper((string) ($country['iso2'] ?? ''));
    if ($iso2 !== '') {
        $countryRows[$iso2] = true;
    }
}
$missing = array_values(array_diff($targets, array_keys($countryRows)));
if ($missing !== []) {
    $fail('Destination country rows are missing: ' . implode(',', $missing));
}

echo 'Preflight passed: ' . $expectedTotal . ' existing requirements, ' . $expectedPublished . ' verified/published, ' . $expectedCountryTable . ' country rows; all ' . count($targets) . ' target country rows exist and no target BGD tourism record exists.' . PHP_EOL;
