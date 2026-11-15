<?php

use App\Models\Country;
use App\Models\VisaRequirement;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use RuntimeException;

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

    $country = Country::query()->where('iso2', 'AC')->first();
    $matches = $records->filter(fn ($row) => $row->country?->iso2 === 'AC');
    $record = $matches->first();
    if (! $country || ! $record || $matches->count() !== 1) {
        throw new RuntimeException('AC country or unique BGD tourism requirement is missing.');
    }

    $sourceDate = fn ($source) => substr((string) $source->source_last_checked_at, 0, 10);
    $primaryUrl = 'https://www.ascension-visas.com/faq';
    $crossUrl = 'https://www.ascension-visas.com/useful-information';
    $primary = $record->sources->contains(fn ($source) => $source->source_type === 'official' && (bool) $source->is_primary && $source->source_url === $primaryUrl && $sourceDate($source) === '2026-11-15');
    $cross = $record->sources->contains(fn ($source) => $source->source_type === 'official' && ! (bool) $source->is_primary && $source->source_url === $crossUrl && $source->source_name === 'Ascension Island Government — eVisa Eligibility Information' && $sourceDate($source) === '2026-11-15');

    $expectedTypes = ['employee', 'self_employed_or_business_owner', 'student', 'retired'];
    $checklists = is_array($record->applicant_checklists) ? $record->applicant_checklists : [];
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
    $photoValid = ($photo['status'] ?? null) === 'no_fixed_size_published'
        && ($photo['checked_at'] ?? null) === '2026-11-15'
        && ! empty($photo['source_name'])
        && ($photo['source_url'] ?? null) === $primaryUrl
        && strtoupper((string) ($photo['file_format'] ?? '')) === 'JPEG'
        && empty($photo['dimensions_mm'])
        && empty($photo['digital_dimensions_px']);

    $databaseChecks = [
        'country_count' => Country::query()->count() === 252,
        'requirement_count' => $records->count() === 137,
        'published_count' => $published === 135,
        'pending_count' => $pending === 2,
        'duplicate_keys' => $duplicates === 0,
        'country_iso2' => strtoupper((string) $country->iso2) === 'AC',
        'country_iso3_null' => $country->iso3 === null,
        'country_slug' => $country->slug === 'ascension-island',
        'country_unpublished_master_flag' => ! (bool) $country->is_published,
        'passport_scope' => $record->passport_nationality_iso3 === 'BGD' && $record->travel_purpose === 'tourism',
        'verified_published' => $record->verification_status === 'verified' && (bool) $record->is_published,
        'requirement_type' => $record->requirement_type === 'eVisa required',
        'entry_flags' => $record->visa_required === true && $record->visa_free_available === false && $record->visa_on_arrival_available === false && $record->evisa_available === true,
        'stay_rule' => str_contains(strtolower((string) $record->maximum_permitted_stay), '3 months') && str_contains(strtolower((string) $record->maximum_permitted_stay), '12-month'),
        'fee_rule' => str_contains((string) $record->application_fee, 'GBP 40') && str_contains((string) $record->application_fee, 'GBP 60'),
        'processing_rule' => str_contains(strtolower((string) $record->processing_time), '21 days') && str_contains(strtolower((string) $record->processing_time), '6 weeks'),
        'application_channel' => str_contains((string) $record->application_channel, 'ascension-visas.com'),
        'passport_validity_rule' => str_contains(strtolower((string) $record->passport_validity_rule), 'six months'),
        'insurance_rule' => str_contains((string) $record->travel_insurance_rule, '500,000'),
        'conditional_uk_visa_rule' => str_contains(strtolower((string) $record->transit_rule), 'may require') && str_contains((string) $record->transit_rule, 'Bangladesh'),
        'required_documents' => is_array($record->required_documents) && count($record->required_documents) >= 5,
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
    $detail = Http::timeout(45)->acceptJson()->get($base . '/api/visa-requirements/AC', ['passport' => 'BGD', 'purpose' => 'tourism'])->throw()->json();
    $countryList = Http::timeout(45)->acceptJson()->get($base . '/api/countries')->throw()->json();
    $apiRequirement = data_get($detail, 'data', []);
    $apiCountryRows = data_get($countryList, 'data', []);
    $apiCountry = collect($apiCountryRows)->first(fn ($item) => strtoupper((string) data_get($item, 'iso2')) === 'AC');
    $apiSources = data_get($apiRequirement, 'sources', []);
    $apiChecklists = data_get($apiRequirement, 'applicant_checklists', []);
    $apiTypes = is_array($apiChecklists) ? array_values(array_unique(array_column($apiChecklists, 'applicant_type'))) : [];
    sort($apiTypes);
    $apiExpectedTypes = $expectedTypes;
    sort($apiExpectedTypes);
    $apiPrimary = collect($apiSources)->contains(fn ($source) => data_get($source, 'source_type') === 'official' && data_get($source, 'is_primary') === true && data_get($source, 'source_url') === $primaryUrl && substr((string) data_get($source, 'source_last_checked_at'), 0, 10) === '2026-11-15');
    $apiCross = collect($apiSources)->contains(fn ($source) => data_get($source, 'source_type') === 'official' && data_get($source, 'is_primary') === false && data_get($source, 'source_url') === $crossUrl && data_get($source, 'source_name') === 'Ascension Island Government — eVisa Eligibility Information' && substr((string) data_get($source, 'source_last_checked_at'), 0, 10) === '2026-11-15');
    $apiChecklistsValid = $apiTypes === $apiExpectedTypes;
    foreach ($apiChecklists as $item) {
        if (! data_get($item, 'source_name') || ! str_starts_with((string) data_get($item, 'source_url'), 'https://') || data_get($item, 'checked_at') !== '2026-11-15') {
            $apiChecklistsValid = false;
        }
    }
    $apiChecks = [
        'public_list_total' => data_get($list, 'meta.total') === 135,
        'public_detail_identity' => strtoupper((string) data_get($apiRequirement, 'country.iso2')) === 'AC' && data_get($apiRequirement, 'country.slug') === 'ascension-island' && data_get($apiRequirement, 'country.iso3') === null,
        'public_detail_verified_published' => data_get($apiRequirement, 'verification_status') === 'verified' && data_get($apiRequirement, 'is_published') === true,
        'public_detail_route_flags' => data_get($apiRequirement, 'visa_required') === true && data_get($apiRequirement, 'visa_free_available') === false && data_get($apiRequirement, 'visa_on_arrival_available') === false && data_get($apiRequirement, 'evisa_available') === true,
        'public_detail_stay_fee' => str_contains(strtolower((string) data_get($apiRequirement, 'maximum_permitted_stay')), '3 months') && str_contains((string) data_get($apiRequirement, 'application_fee'), 'GBP 40'),
        'public_detail_photo' => data_get($apiRequirement, 'photo_specifications.status') === 'no_fixed_size_published',
        'public_detail_checklists' => $apiChecklistsValid,
        'public_detail_primary_source' => $apiPrimary,
        'public_detail_cross_check_source' => $apiCross,
        'public_detail_source_count' => count($apiSources) >= 3,
        'public_country_directory' => is_array($apiCountryRows) && $apiCountry && data_get($apiCountry, 'slug') === 'ascension-island',
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
        'AC' => [
            'name' => $country->name,
            'slug' => $country->slug,
            'iso2' => $country->iso2,
            'iso3' => $country->iso3,
            'verification_status' => $record->verification_status,
            'is_published' => (bool) $record->is_published,
            'requirement_type' => $record->requirement_type,
            'visa_required' => (bool) $record->visa_required,
            'visa_free_available' => (bool) $record->visa_free_available,
            'evisa_available' => (bool) $record->evisa_available,
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
    fwrite(STDERR, 'BATCH46_READBACK_ERROR ' . get_class($exception) . ': ' . $exception->getMessage() . PHP_EOL);
    exit(1);
}
