<?php

$root = rtrim((string) (getenv('UNIGO_APP_DIR') ?: dirname(__DIR__)), DIRECTORY_SEPARATOR);

if (! is_file($root . '/vendor/autoload.php') || ! is_file($root . '/bootstrap/app.php')) {
    throw new RuntimeException('The configured Laravel application directory is invalid.');
}

require $root . '/vendor/autoload.php';

$app = require $root . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

function batch47Require(bool $condition, string $message): void
{
    if (! $condition) {
        throw new RuntimeException($message);
    }
}

function batch47Normalize(mixed $value): mixed
{
    if (! is_array($value)) {
        return $value;
    }

    if (array_is_list($value)) {
        return array_map('batch47Normalize', $value);
    }

    ksort($value);

    foreach ($value as $key => $item) {
        $value[$key] = batch47Normalize($item);
    }

    return $value;
}

function batch47CheckedDate(mixed $value): string
{
    return substr((string) $value, 0, 10);
}

try {
    $csvPath = $root . '/storage/app/imports/verified_full_audit_batch_47.csv';
    $handle = fopen($csvPath, 'r');

    if ($handle === false) {
        throw new RuntimeException('The allowlisted batch 47 CSV is unavailable for readback.');
    }

    try {
        $headers = fgetcsv($handle, 0, ',', '"', '\\');
        if (! is_array($headers)) {
            throw new RuntimeException('The batch 47 CSV has no header row.');
        }

        $rows = [];
        while (($values = fgetcsv($handle, 0, ',', '"', '\\')) !== false) {
            if (count($values) !== count($headers)) {
                throw new RuntimeException('The batch 47 CSV row does not match its header.');
            }
            $rows[] = array_combine($headers, $values);
        }
    } finally {
        fclose($handle);
    }

    batch47Require(count($rows) === 1, 'Batch 47 must contain exactly one row.');

    $expected = $rows[0];

    batch47Require(
        strtoupper((string) ($expected['passport_iso3'] ?? '')) === 'BGD'
        && strtoupper((string) ($expected['destination_iso2'] ?? '')) === 'IM'
        && (string) ($expected['travel_purpose'] ?? '') === 'tourism'
        && (string) ($expected['verification_status'] ?? '') === 'verified'
        && (string) ($expected['publish_status'] ?? '') === 'published',
        'Batch 47 identity or approval fields do not match the allowlist.'
    );

    $records = \App\Models\VisaRequirement::query()
        ->with(['country:id,name,slug,iso2', 'sources'])
        ->where('passport_nationality_iso3', 'BGD')
        ->where('travel_purpose', 'tourism')
        ->get();

    $published = $records->filter(
        fn ($record) => $record->verification_status === 'verified' && (bool) $record->is_published
    )->count();

    $pending = $records->count() - $published;

    $duplicates = \Illuminate\Support\Facades\DB::table('visa_requirements')
        ->where('passport_nationality_iso3', 'BGD')
        ->where('travel_purpose', 'tourism')
        ->select('country_id', 'passport_nationality_iso3', 'travel_purpose')
        ->groupBy('country_id', 'passport_nationality_iso3', 'travel_purpose')
        ->havingRaw('COUNT(*) > 1')
        ->get()
        ->count();

    $imRows = $records->filter(fn ($record) => $record->country?->iso2 === 'IM');
    batch47Require($imRows->count() === 1, 'Production must contain exactly one BGD tourism Isle of Man record.');
    $im = $imRows->first();

    batch47Require($im->country_id !== null, 'The Isle of Man record is not linked to its country master row.');
    batch47Require($im->country?->name === 'Isle of Man' && $im->country?->slug === 'isle-of-man', 'The Isle of Man identity or slug is incorrect.');
    batch47Require($im->verification_status === 'verified' && (bool) $im->is_published, 'The Isle of Man record is not verified and published.');

    $booleanMap = [
        'visa_required' => 'visa_required',
        'visa_free_eligibility' => 'visa_free_available',
        'visa_on_arrival_available' => 'visa_on_arrival_available',
        'evisa_available' => 'evisa_available',
    ];

    foreach ($booleanMap as $column => $field) {
        $value = filter_var(trim((string) ($expected[$column] ?? '')), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        batch47Require($value !== null, 'The allowlisted boolean is invalid: ' . $column . '.');
        batch47Require((bool) $im->{$field} === $value, 'Production field mismatch: ' . $field . '.');
    }

    $stringMap = [
        'visa_requirement_type' => 'requirement_type',
        'maximum_permitted_stay' => 'maximum_permitted_stay',
        'entry_count' => 'entry_count',
        'passport_validity_rule' => 'passport_validity_rule',
        'blank_passport_pages_rule' => 'blank_passport_pages_rule',
        'return_ticket_rule' => 'return_ticket_rule',
        'proof_of_funds_rule' => 'proof_of_funds_rule',
        'accommodation_rule' => 'accommodation_rule',
        'travel_insurance_rule' => 'travel_insurance_rule',
        'yellow_fever_or_health_rule' => 'health_requirements',
        'transit_rule' => 'transit_requirements',
        'application_channel' => 'application_channel',
        'application_fee' => 'application_fee',
        'processing_time' => 'processing_time',
    ];

    foreach ($stringMap as $column => $field) {
        batch47Require(
            (string) $im->{$field} === (string) ($expected[$column] ?? ''),
            'Production field mismatch: ' . $field . '.'
        );
    }

    batch47Require(
        batch47CheckedDate($im->official_source_checked_at) === (string) $expected['official_source_last_checked'],
        'The primary official source checked date does not match the allowlist.'
    );

    $expectedDocuments = array_values(array_filter(array_map('trim', explode('|', (string) $expected['required_documents']))));
    $actualDocuments = is_array($im->required_documents) ? $im->required_documents : [];
    batch47Require(
        batch47Normalize($actualDocuments) === batch47Normalize($expectedDocuments),
        'The production applicant document list does not match the allowlist.'
    );

    $expectedPhoto = json_decode((string) $expected['photo_specifications'], true, 512, JSON_THROW_ON_ERROR);
    $expectedChecklists = json_decode((string) $expected['applicant_checklists'], true, 512, JSON_THROW_ON_ERROR);

    batch47Require(
        batch47Normalize($im->photo_specifications) === batch47Normalize($expectedPhoto),
        'Production photo specifications do not match the allowlist.'
    );

    batch47Require(
        batch47Normalize($im->applicant_checklists) === batch47Normalize($expectedChecklists),
        'Production applicant checklists do not match the allowlist.'
    );

    batch47Require(
        data_get($im->photo_specifications, 'status') === 'no_fixed_size_published'
        && data_get($im->photo_specifications, 'captured_at_vac') === true
        && data_get($im->photo_specifications, 'biometric_capture') === true
        && data_get($im->photo_specifications, 'dimensions_mm') === null
        && data_get($im->photo_specifications, 'digital_dimensions_px') === null,
        'The Isle of Man photo status or captured-at-VAC distinction is incorrect.'
    );

    $expectedSources = json_decode((string) $expected['additional_sources'], true, 512, JSON_THROW_ON_ERROR);
    $sources = $im->sources;

    $sourceExists = function (string $url, string $type, bool $primary, string $date) use ($sources): bool {
        return $sources->contains(function ($source) use ($url, $type, $primary, $date): bool {
            return (string) $source->source_url === $url
                && (string) $source->source_type === $type
                && (bool) $source->is_primary === $primary
                && batch47CheckedDate($source->source_last_checked_at) === $date;
        });
    };

    $primaryUrl = (string) $expected['official_source_url'];
    $crossUrl = (string) $expected['cross_check_source_url'];
    $checkedDate = (string) $expected['official_source_last_checked'];

    batch47Require(
        $sourceExists($primaryUrl, 'official', true, $checkedDate),
        'The primary official source was not read back.'
    );

    batch47Require(
        $sourceExists($crossUrl, 'official', false, $checkedDate),
        'The cross-check official source was not read back.'
    );

    foreach ($expectedSources as $source) {
        batch47Require(
            $sourceExists(
                (string) $source['source_url'],
                (string) $source['source_type'],
                false,
                $checkedDate
            ),
            'An additional official/provider source was not read back: ' . (string) $source['source_url']
        );
    }

    batch47Require($records->count() === 136, 'The production BGD tourism requirement count is not 136.');
    batch47Require($published === 135 && $pending === 1, 'Production verified/pending counts do not match the expected post-import state.');
    batch47Require($duplicates === 0, 'Production contains duplicate BGD tourism keys.');
    batch47Require(\App\Models\Country::query()->count() === 252, 'The production country master count changed unexpectedly.');

    $apiBase = rtrim((string) config('app.url'), '/');
    batch47Require($apiBase !== '', 'The production API base URL is not configured.');

    $list = \Illuminate\Support\Facades\Http::timeout(45)
        ->acceptJson()
        ->get($apiBase . '/api/visa-requirements', ['passport' => 'BGD', 'purpose' => 'tourism'])
        ->throw()
        ->json();

    batch47Require((int) data_get($list, 'meta.total') === 135, 'The public BGD tourism API total is not 135.');

    $detail = \Illuminate\Support\Facades\Http::timeout(45)
        ->acceptJson()
        ->get($apiBase . '/api/visa-requirements/IM', ['passport' => 'BGD', 'purpose' => 'tourism'])
        ->throw()
        ->json();

    batch47Require(strtoupper((string) data_get($detail, 'data.country.iso2')) === 'IM', 'The public API returned the wrong destination.');
    batch47Require(strtoupper((string) data_get($detail, 'data.passport_nationality_iso3')) === 'BGD', 'The public API returned the wrong passport nationality.');
    batch47Require((string) data_get($detail, 'data.travel_purpose') === 'tourism', 'The public API returned the wrong travel purpose.');
    batch47Require((string) data_get($detail, 'data.verification_status') === 'verified', 'The public API record is not verified.');
    batch47Require(data_get($detail, 'data.is_published') === true, 'The public API record is not published.');

    foreach ($booleanMap as $column => $field) {
        $value = filter_var(trim((string) ($expected[$column] ?? '')), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        batch47Require((bool) data_get($detail, 'data.' . $field) === $value, 'The public API route flag does not match: ' . $field . '.');
    }

    batch47Require(
        (string) data_get($detail, 'data.application_fee') === (string) $expected['application_fee']
        && (string) data_get($detail, 'data.processing_time') === (string) $expected['processing_time']
        && (string) data_get($detail, 'data.maximum_permitted_stay') === (string) $expected['maximum_permitted_stay'],
        'The public API fee, processing time or permitted stay does not match the allowlist.'
    );

    batch47Require(
        data_get($detail, 'data.photo_specifications.status') === 'no_fixed_size_published'
        && data_get($detail, 'data.photo_specifications.captured_at_vac') === true
        && data_get($detail, 'data.photo_specifications.biometric_capture') === true,
        'The public API photo specification does not match the allowlist.'
    );

    $apiChecklists = data_get($detail, 'data.applicant_checklists', []);
    batch47Require(is_array($apiChecklists) && count($apiChecklists) === 4, 'The public API does not expose all four applicant checklists.');

    $apiChecklistTypes = array_column($apiChecklists, 'applicant_type');
    batch47Require(
        count(array_diff(['employee', 'self_employed_or_business_owner', 'student', 'retired'], $apiChecklistTypes)) === 0,
        'The public API applicant checklist types are incomplete.'
    );

    $apiSources = data_get($detail, 'data.sources', []);
    batch47Require(is_array($apiSources), 'The public API source history is missing.');

    $apiSourceExists = function (string $url, string $type, bool $primary, string $date) use ($apiSources): bool {
        return collect($apiSources)->contains(function ($source) use ($url, $type, $primary, $date): bool {
            return (string) data_get($source, 'source_url') === $url
                && (string) data_get($source, 'source_type') === $type
                && (bool) data_get($source, 'is_primary') === $primary
                && batch47CheckedDate(data_get($source, 'source_last_checked_at')) === $date;
        });
    };

    batch47Require(
        $apiSourceExists($primaryUrl, 'official', true, $checkedDate)
        && $apiSourceExists($crossUrl, 'official', false, $checkedDate),
        'The public API primary/cross-check source history is incomplete.'
    );

    foreach ($expectedSources as $source) {
        batch47Require(
            $apiSourceExists((string) $source['source_url'], (string) $source['source_type'], false, $checkedDate),
            'The public API is missing an additional source.'
        );
    }

    $result = [
        'country_count' => \App\Models\Country::query()->count(),
        'requirement_count' => $records->count(),
        'published_count' => $published,
        'pending_count' => $pending,
        'duplicate_keys' => $duplicates,
        'IM' => [
            'record_id' => $im->id,
            'country_id' => $im->country_id,
            'country_name' => $im->country?->name,
            'country_slug' => $im->country?->slug,
            'verification_status' => $im->verification_status,
            'is_published' => (bool) $im->is_published,
            'application_fee' => $im->application_fee,
            'processing_time' => $im->processing_time,
            'photo_status' => data_get($im->photo_specifications, 'status'),
            'applicant_checklists' => count($im->applicant_checklists ?? []),
            'source_count' => $sources->count(),
        ],
        'public_api_total' => data_get($list, 'meta.total'),
        'public_api_detail_verified' => 'IM',
        'source_history_verified' => true,
    ];

    echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
} catch (\Throwable $exception) {
    fwrite(STDERR, 'BATCH47_READBACK_ERROR ' . get_class($exception) . ': ' . $exception->getMessage() . PHP_EOL);
    exit(1);
}
