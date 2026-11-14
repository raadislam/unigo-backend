<?php

use App\\Models\\Country;
use App\\Models\\VisaRequirement;

$root = getenv('UNIGO_APP_DIR') ?: dirname(__DIR__);
chdir($root);
require $root . '/vendor/autoload.php';
$app = require $root . '/bootstrap/app.php';
$app->make(Illuminate\\Contracts\\Console\\Kernel::class)->bootstrap();

$mode = $argv[1] ?? '';
if (! in_array($mode, ['preflight', 'verify'], true)) {
    fwrite(STDERR, "Expected mode: preflight or verify.\n");
    exit(2);
}

$specs = [
    'new_country_visa_batch_06_07.csv' => ['ID', 'MU', 'NP', 'PH', 'TR', 'BN', 'RW', 'TZ'],
    'new_country_visa_batch_08.csv' => ['HK', 'MO', 'UZ', 'KR'],
];

$truthy = static fn ($value): ?bool => $value === '' || $value === null ? null : filter_var($value, FILTER_VALIDATE_BOOLEAN);

$expectedFields = static function (array $row) use ($truthy): array {
    return [
        'requirement_type' => trim($row['visa_requirement_type']) ?: null,
        'visa_required' => $truthy($row['visa_required'] ?? ''),
        'visa_free_available' => $truthy($row['visa_free_eligibility'] ?? ''),
        'visa_on_arrival_available' => $truthy($row['visa_on_arrival_available'] ?? ''),
        'evisa_available' => $truthy($row['evisa_available'] ?? ''),
        'maximum_permitted_stay' => trim($row['maximum_permitted_stay'] ?? '') ?: null,
        'entry_count' => trim($row['entry_count'] ?? '') ?: null,
        'passport_validity_rule' => trim($row['passport_validity_rule'] ?? '') ?: null,
        'blank_passport_pages_rule' => trim($row['blank_passport_pages_rule'] ?? '') ?: null,
        'return_ticket_rule' => trim($row['return_ticket_rule'] ?? '') ?: null,
        'proof_of_funds_rule' => trim($row['proof_of_funds_rule'] ?? '') ?: null,
        'accommodation_rule' => trim($row['accommodation_rule'] ?? '') ?: null,
        'travel_insurance_rule' => trim($row['travel_insurance_rule'] ?? '') ?: null,
        'health_requirements' => trim($row['yellow_fever_or_health_rule'] ?? '') ?: null,
        'transit_requirements' => trim($row['transit_rule'] ?? '') ?: null,
        'application_channel' => trim($row['application_channel'] ?? '') ?: null,
        'application_fee' => trim($row['application_fee'] ?? '') ?: null,
        'processing_time' => trim($row['processing_time'] ?? '') ?: null,
        'required_documents' => array_values(array_filter(array_map('trim', explode('|', $row['required_documents'] ?? '')))),
    ];
};

$assertRecordMatches = static function (VisaRequirement $record, array $row) use ($expectedFields): void {
    foreach ($expectedFields($row) as $field => $expected) {
        $actual = $record->{$field};
        if ($field === 'required_documents') {
            $actualList = is_array($actual) ? $actual : [];
            $expectedList = $expected;
            sort($actualList);
            sort($expectedList);
            if ($actualList !== $expectedList) {
                throw new RuntimeException('Existing record mismatch for ' . $row['destination_iso2'] . ': ' . $field);
            }
        } elseif (in_array($field, ['visa_required', 'visa_free_available', 'visa_on_arrival_available', 'evisa_available'], true)) {
            if ($expected === null ? $actual !== null : (bool) $actual !== $expected) {
                throw new RuntimeException('Existing record mismatch for ' . $row['destination_iso2'] . ': ' . $field);
            }
        } elseif ((string) ($actual ?? '') !== (string) ($expected ?? '')) {
            throw new RuntimeException('Existing record mismatch for ' . $row['destination_iso2'] . ': ' . $field);
        }
    }

    if ($record->verification_status !== 'pending_review' || (bool) $record->is_published) {
        throw new RuntimeException('Existing record is not safely pending review and unpublished: ' . $row['destination_iso2']);
    }

    $primary = $record->sources()
        ->where('source_type', 'official')
        ->where('source_url', trim($row['official_source_url']))
        ->where('is_primary', true)
        ->exists();

    $crossCheck = $record->sources()
        ->where('source_url', trim($row['cross_check_source_url']))
        ->exists();

    if (! $primary || ! $crossCheck) {
        throw new RuntimeException('Existing source links do not match the staged CSV for ' . $row['destination_iso2']);
    }
};

$total = 0;
$existingCount = 0;
$missingCount = 0;
$seen = [];

foreach ($specs as $file => $expectedCodes) {
    $path = $root . '/database/data/' . $file;
    if (! is_file($path)) {
        throw new RuntimeException('Staged CSV is missing: ' . $file);
    }

    $handle = fopen($path, 'r');
    if ($handle === false) {
        throw new RuntimeException('Unable to open staged CSV: ' . $file);
    }

    try {
        $headers = fgetcsv($handle);
        if (! is_array($headers)) {
            throw new RuntimeException('Staged CSV is empty: ' . $file);
        }
        $headers[0] = preg_replace('/^\\xEF\\xBB\\xBF/', '', (string) $headers[0]);
        $requiredHeaders = [
            'passport_iso3', 'destination_iso2', 'travel_purpose', 'visa_requirement_type',
            'visa_required', 'official_source_url', 'verification_status', 'required_documents',
            'official_immigration_source_name', 'official_source_last_checked',
            'cross_check_source_url', 'cross_check_source_name', 'application_fee',
            'processing_time', 'review_notes',
        ];
        $missingHeaders = array_diff($requiredHeaders, $headers);
        if ($missingHeaders !== []) {
            throw new RuntimeException('Missing columns in ' . $file . ': ' . implode(', ', $missingHeaders));
        }

        $fileCodes = [];
        $fileExisting = 0;
        $fileMissing = 0;

        while (($values = fgetcsv($handle)) !== false) {
            if (count($values) !== count($headers)) {
                throw new RuntimeException('Malformed CSV row in ' . $file);
            }
            $row = array_combine($headers, $values);
            if (! is_array($row)) {
                throw new RuntimeException('Unable to parse CSV row in ' . $file);
            }

            $iso2 = strtoupper(trim($row['destination_iso2'] ?? ''));
            if (! in_array($iso2, $expectedCodes, true) || isset($seen[$iso2])) {
                throw new RuntimeException('Unexpected or duplicate destination code: ' . $iso2);
            }
            $seen[$iso2] = true;
            $fileCodes[] = $iso2;

            if (strtoupper(trim($row['passport_iso3'] ?? '')) !== 'BGD'
                || trim($row['travel_purpose'] ?? '') !== 'tourism'
                || trim($row['verification_status'] ?? '') !== 'verified') {
                throw new RuntimeException('Row is not a verified BGD tourism record: ' . $iso2);
            }

            foreach (['visa_requirement_type', 'official_immigration_source_name', 'application_fee', 'processing_time', 'required_documents', 'review_notes'] as $field) {
                if (trim((string) ($row[$field] ?? '')) === '') {
                    throw new RuntimeException('Required field is blank for ' . $iso2 . ': ' . $field);
                }
            }

            foreach (['official_source_url', 'cross_check_source_url'] as $field) {
                $url = trim((string) ($row[$field] ?? ''));
                if (! filter_var($url, FILTER_VALIDATE_URL) || strtolower((string) parse_url($url, PHP_URL_SCHEME)) !== 'https') {
                    throw new RuntimeException('Invalid HTTPS source URL for ' . $iso2 . ': ' . $field);
                }
            }

            $country = Country::query()->where('iso2', $iso2)->first();
            if (! $country) {
                throw new RuntimeException('Country row is missing in production for ' . $iso2);
            }

            $record = VisaRequirement::query()
                ->where('country_id', $country->id)
                ->where('passport_nationality_iso3', 'BGD')
                ->where('travel_purpose', 'tourism')
                ->first();

            if ($record) {
                $assertRecordMatches($record, $row);
                $fileExisting++;
            } else {
                $fileMissing++;
            }
            $total++;
        }

        sort($fileCodes);
        $sortedExpected = $expectedCodes;
        sort($sortedExpected);
        if ($fileCodes !== $sortedExpected) {
            throw new RuntimeException('Destination list mismatch in ' . $file);
        }

        $existingCount += $fileExisting;
        $missingCount += $fileMissing;
        if ($mode === 'preflight') {
            echo 'PRECHECK_FILE ' . $file . ' total=' . count($expectedCodes) . ' existing=' . $fileExisting . ' missing=' . $fileMissing . PHP_EOL;
        }
    } finally {
        fclose($handle);
    }
}

if ($total !== 12 || count($seen) !== 12) {
    throw new RuntimeException('Expected exactly 12 unique staged destination records; found ' . $total);
}

if ($mode === 'verify' && ($existingCount !== 12 || $missingCount !== 0)) {
    throw new RuntimeException('Post-import verification did not find all 12 records.');
}

if ($mode === 'preflight') {
    echo 'PRECHECK_OK total=12 existing=' . $existingCount . ' missing=' . $missingCount . PHP_EOL;
} else {
    echo 'VERIFY_OK total=12 pending_review=12 unpublished=12' . PHP_EOL;
}
