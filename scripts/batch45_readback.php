<?php

use App\Models\Country;
use App\Models\VisaRequirement;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

$root = getenv('APP_ROOT') ?: dirname(__DIR__);
require $root . '/vendor/autoload.php';
$app = require $root . '/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

try {
    $records = VisaRequirement::query()
        ->with(['country:id,name,slug,iso2,iso3,is_published', 'sources'])
        ->where('passport_nationality_iso3', 'BGD')
        ->where('travel_purpose', 'tourism')
        ->get();

    $published = $records->filter(fn ($row) => $row->verification_status === 'verified' && (bool) $row->is_published)->count();
    $pending = $records->count() - $published;
    $duplicates = DB::table('visa_requirements')
        ->where('passport_nationality_iso3', 'BGD')
        ->where('travel_purpose', 'tourism')
        ->select('country_id', 'passport_nationality_iso3', 'travel_purpose')
        ->groupBy('country_id', 'passport_nationality_iso3', 'travel_purpose')
        ->havingRaw('COUNT(*) > 1')
        ->count();

    $country = Country::query()->where('iso2', 'TA')->first();
    $matches = $records->filter(fn ($row) => $row->country?->iso2 === 'TA');
    $record = $matches->first();
    if (! $country || ! $record || $matches->count() !== 1) {
        throw new RuntimeException('TA country or unique BGD tourism requirement is missing.');
    }

    $sourceDate = fn ($source) => substr((string) $source->source_last_checked_at, 0, 10);
    $primaryUrl = 'https://www.tristandc.com/visitsorganise.php';
    $crossUrl = 'https://www.tristandc.com/visitsadmin.php';
    $primary = $record->sources->contains(fn ($source) => $source->source_type === 'official' && (bool) $source->is_primary && $source->source_url === $primaryUrl && $sourceDate($source) === '2026-11-15');
    $cross = $record->sources->contains(fn ($source) => in_array($source->source_type, ['official', 'secondary'], true) && ! (bool) $source->is_primary && $source->source_url === $crossUrl && $sourceDate($source) === '2026-11-15');
    $checklists = is_array($record->applicant_checklists) ? $record->applicant_checklists : [];
    $expectedTypes = ['employee', 'self_employed_or_business_owner', 'student', 'retired'];
    $types = array_values(array_unique(array_column($checklists, 'applicant_type')));
    sort($types);
    $expectedSorted = $expectedTypes;
    sort($expectedSorted);
    $checklistsValid = $types === $expectedSorted;
    foreach ($checklists as $item) {
        if (! is_array($item) || empty($item['source_name']) || ! str_starts_with((string) ($item['source_url'] ?? ''), 'https://') || ($item['checked_at'] ?? null) !== '2026-11-15' || ! is_array($item['documents'] ?? null) || count($item['documents']) < 6) {
            $checklistsValid = false;
        }
    }
    $photo = is_array($record->photo_specifications) ? $record->photo_specifications : [];
    $photoValid = ($photo['status'] ?? null) === 'not_specified'
        && ($photo['checked_at'] ?? null) === '2026-11-15'
        && ! empty($photo['source_name'])
        && str_starts_with((string) ($photo['source_url'] ?? ''), 'https://')
        && empty($photo['dimensions_mm'])
        && empty($photo['digital_dimensions_px']);

    $databaseChecks = [
        'country_count' => Country::query()->count() === 251,
        'requirement_count' => $records->count() === 136,
        'published_count' => $published === 134,
        'pending_count' => $pending === 2,
        'duplicate_keys' => $duplicates === 0,
        'country_iso2' => strtoupper((string) $country->iso2) === 'TA',
        'country_iso3_null' => $country->iso3 === null,
        'country_slug' => $country->slug === 'tristan-da-cunha',
        'country_unpublished_master_flag' => ! (bool) $country->is_published,
        'passport_scope' => $record->passport_nationality_iso3 === 'BGD' && $record->travel_purpose === 'tourism',
        'verified_published' => $record->verification_status === 'verified' && (bool) $record->is_published,
        'requirement_type' => $record->requirement_type === 'No visa; prior landing permission',
        'entry_flags' => $record->visa_required === false && $record->visa_free_available === false && $record->visa_on_arrival_available === false && $record->evisa_available === false,
        'stay_rule' => str_contains(strtolower((string) $record->maximum_permitted_stay), 'no universal maximum'),
        'fee_rule' => str_contains((string) $record->application_fee, 'GBP 30') && str_contains((string) $record->application_fee, 'GBP 15'),
        'processing_rule' => str_contains(strtolower((string) $record->processing_time), 'no published decision sla'),
        'application_channel' => str_contains((string) $record->application_channel, 'tdcenquiries@tdc-gov.com'),
        'required_documents' => is_array($record->required_documents) && count($record->required_documents) >= 6,
        'photo_specification' => $photoValid,
        'four_applicant_checklists' => $checklistsValid,
        'primary_official_source' => $primary,
        'cross_check_source' => $cross,
        'official_source_count' => $record->sources->where('source_type', 'official')->count() >= 3,
        'official_checked_at' => substr((string) $record->official_source_checked_at, 0, 10) === '2026-11-15',
    ];
    $failed = array_keys(array_filter($databaseChecks, fn ($ok) => $ok !== true));
    if ($failed) {
        throw new RuntimeException('Database readback checks failed: ' . implode(', ', $failed));
    }

    $base = rtrim((string) config('app.url'), '/');
    $list = Http::timeout(45)->acceptJson()->get($base . '/api/visa-requirements', ['passport' => 'BGD', 'purpose' => 'tourism'])->throw()->json();
    $detail = Http::timeout(45)->acceptJson()->get($base . '/api/visa-requirements/TA', ['passport' => 'BGD', 'purpose' => 'tourism'])->throw()->json();
    $countryList = Http::timeout(45)->acceptJson()->get($base . '/api/countries')->throw()->json();
    $apiRequirement = data_get($detail, 'data', []);
    $apiCountryRows = data_get($countryList, 'data', []);
    $apiCountry = collect($apiCountryRows)->first(fn ($item) => strtoupper((string) data_get($item, 'iso2')) === 'TA');
    $apiSources = data_get($apiRequirement, 'sources', []);
    $apiChecklists = data_get($apiRequirement, 'applicant_checklists', []);
    $apiTypes = is_array($apiChecklists) ? array_values(array_unique(array_column($apiChecklists, 'applicant_type'))) : [];
    sort($apiTypes);
    $apiExpectedTypes = $expectedTypes;
    sort($apiExpectedTypes);
    $apiPrimary = collect($apiSources)->contains(fn ($source) => data_get($source, 'source_type') === 'official' && data_get($source, 'is_primary') === true && data_get($source, 'source_url') === $primaryUrl && substr((string) data_get($source, 'source_last_checked_at'), 0, 10) === '2026-11-15');
    $apiCross = collect($apiSources)->contains(fn ($source) => in_array(data_get($source, 'source_type'), ['official', 'secondary'], true) && data_get($source, 'is_primary') === false && data_get($source, 'source_url') === $crossUrl && substr((string) data_get($source, 'source_last_checked_at'), 0, 10) === '2026-11-15');
    $apiChecklistsValid = $apiTypes === $apiExpectedTypes;
    foreach ($apiChecklists as $item) {
        if (! data_get($item, 'source_name') || ! str_starts_with((string) data_get($item, 'source_url'), 'https://') || data_get($item, 'checked_at') !== '2026-11-15') {
            $apiChecklistsValid = false;
        }
    }
    $apiChecks = [
        'public_list_total' => data_get($list, 'meta.total') === 134,
        'public_detail_identity' => strtoupper((string) data_get($apiRequirement, 'country.iso2')) === 'TA' && data_get($apiRequirement, 'country.slug') === 'tristan-da-cunha' && data_get($apiRequirement, 'country.iso3') === null,
        'public_detail_verified_published' => data_get($apiRequirement, 'verification_status') === 'verified' && data_get($apiRequirement, 'is_published') === true,
        'public_detail_route_flags' => data_get($apiRequirement, 'visa_required') === false && data_get($apiRequirement, 'visa_free_available') === false && data_get($apiRequirement, 'visa_on_arrival_available') === false && data_get($apiRequirement, 'evisa_available') === false,
        'public_detail_photo' => data_get($apiRequirement, 'photo_specifications.status') === 'not_specified',
        'public_detail_checklists' => $apiChecklistsValid,
        'public_detail_primary_source' => $apiPrimary,
        'public_detail_cross_check_source' => $apiCross,
        'public_detail_source_count' => count($apiSources) >= 4,
        'public_country_directory' => is_array($apiCountryRows) && $apiCountry && data_get($apiCountry, 'slug') === 'tristan-da-cunha',
    ];
    $apiFailed = array_keys(array_filter($apiChecks, fn ($ok) => $ok !== true));
    if ($apiFailed) {
        throw new RuntimeException('Public API readback checks failed: ' . implode(', ', $apiFailed));
    }

    $result = [
        'country_count' => Country::query()->count(),
        'bgd_tourism_requirement_count' => $records->count(),
        'published_count' => $published,
        'pending_count' => $pending,
        'duplicate_keys' => $duplicates,
        'TA' => [
            'name' => $country->name,
            'slug' => $country->slug,
            'iso2' => $country->iso2,
            'iso3' => $country->iso3,
            'verification_status' => $record->verification_status,
            'is_published' => (bool) $record->is_published,
            'requirement_type' => $record->requirement_type,
            'visa_required' => (bool) $record->visa_required,
            'visa_free_available' => (bool) $record->visa_free_available,
            'application_fee' => $record->application_fee,
            'processing_time' => $record->processing_time,
            'source_count' => $record->sources->count(),
            'photo_status' => data_get($photo, 'status'),
            'applicant_checklist_types' => $types,
        ],
        'public_api_total' => data_get($list, 'meta.total'),
        'public_country_directory_count' => count($apiCountryRows),
        'database_checks' => $databaseChecks,
        'api_checks' => $apiChecks,
    ];
    echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL;
} catch (Throwable $exception) {
    fwrite(STDERR, 'BATCH45_READBACK_ERROR ' . get_class($exception) . ': ' . $exception->getMessage() . PHP_EOL);
    exit(1);
}
