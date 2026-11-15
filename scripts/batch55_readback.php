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
    $mode = $argv[1] ?? 'verify';
    if (! in_array($mode, ['preflight', 'verify'], true)) {
        throw new RuntimeException('Mode must be preflight or verify.');
    }

    $records = VisaRequirement::query()
        ->with(['country:id,name,slug,iso2,iso3', 'sources'])
        ->where('passport_nationality_iso3', 'BGD')
        ->where('travel_purpose', 'tourism')
        ->get();

    $country = Country::query()->where('iso2', 'MY')->first();
    $matches = $records->filter(fn ($row) => strtoupper((string) $row->country?->iso2) === 'MY');
    $record = $matches->first();
    if (! $country || ! $record || $matches->count() !== 1) {
        throw new RuntimeException('Malaysia country or unique BGD tourism requirement is missing.');
    }

    $published = $records->filter(fn ($row) => $row->verification_status === 'verified' && (bool) $row->is_published)->count();
    $pending = $records->count() - $published;
    $countryCount = Country::query()->count();
    $sourceCount = DB::table('visa_requirement_sources')->count();
    $duplicates = DB::table('visa_requirements')
        ->where('passport_nationality_iso3', 'BGD')
        ->where('travel_purpose', 'tourism')
        ->select('country_id', 'passport_nationality_iso3', 'travel_purpose')
        ->groupBy('country_id', 'passport_nationality_iso3', 'travel_purpose')
        ->havingRaw('COUNT(*) > 1')
        ->count();

    if ($mode === 'preflight') {
        $checks = [
            'country_count' => $countryCount === 252,
            'requirement_count' => $records->count() === 136,
            'published_count' => $published === 134,
            'pending_count' => $pending === 2,
            'source_count' => $sourceCount === 916,
            'duplicate_keys' => $duplicates === 0,
            'passport_scope' => $record->passport_nationality_iso3 === 'BGD' && $record->travel_purpose === 'tourism',
            'currently_verified_published' => $record->verification_status === 'verified' && (bool) $record->is_published,
            'country_identity' => $country->name === 'Malaysia' && $country->slug === 'malaysia' && strtoupper((string) $country->iso2) === 'MY' && strtoupper((string) $country->iso3) === 'MYS',
        ];
        $failed = array_keys(array_filter($checks, fn ($ok) => $ok !== true));
        if ($failed) {
            throw new RuntimeException('Preflight checks failed: ' . implode(', ', $failed));
        }
        echo json_encode([
            'mode' => 'preflight',
            'country_count' => $countryCount,
            'requirement_count' => $records->count(),
            'published_count' => $published,
            'pending_count' => $pending,
            'source_count' => $sourceCount,
            'duplicate_keys' => $duplicates,
            'MY' => [
                'verification_status' => $record->verification_status,
                'is_published' => (bool) $record->is_published,
                'existing_checklist_count' => is_array($record->applicant_checklists) ? count($record->applicant_checklists) : 0,
                'existing_photo_status' => data_get($record->photo_specifications, 'status'),
            ],
            'checks' => $checks,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL;
        exit(0);
    }

    $sourceDate = fn ($source) => substr((string) $source->source_last_checked_at, 0, 10);
    $primaryUrl = 'https://malaysiavisa.imi.gov.my/evisa/assets/pdf/tnc_en.pdf';
    $crossUrl = 'https://www.kln.gov.my/en_US/web/bgd_dhaka/faqs';
    $photoUrl = 'https://malaysiavisa.imi.gov.my/evisa/FAQ/PDF/FAQ/Support/FAQ_en.pdf?version=3.8.1';
    $extraUrls = [
        'https://www.imi.gov.my/index.php/en/main-services/visa/visa-fees/',
        'https://www.kln.gov.my/web/bgd_dhaka/requirement_foreigner',
        $photoUrl,
        'https://malaysiavisa.imi.gov.my/assets/faq/nationalities-exempted.html',
        'https://malaysiavisa.imi.gov.my/assets/faq/requirements.html',
        $crossUrl,
        'https://www.kln.gov.my/web/bgd_dhaka/post/requirements-for-visa-application',
    ];

    $checklists = is_array($record->applicant_checklists) ? $record->applicant_checklists : [];
    $expectedTypes = ['employee', 'self_employed_or_business_owner', 'student', 'retired'];
    $types = array_values(array_unique(array_column($checklists, 'applicant_type')));
    sort($types);
    $expectedSorted = $expectedTypes;
    sort($expectedSorted);
    $checklistsValid = $types === $expectedSorted;
    foreach ($checklists as $item) {
        if (! is_array($item) || empty($item['source_name']) || ($item['source_url'] ?? null) !== $primaryUrl || ($item['checked_at'] ?? null) !== '2026-11-15' || ! is_array($item['documents'] ?? null) || count($item['documents']) < 8) {
            $checklistsValid = false;
        }
    }

    $photo = is_array($record->photo_specifications) ? $record->photo_specifications : [];
    $photoValid = ($photo['status'] ?? null) === 'partially_specified'
        && ($photo['dimensions_mm'] ?? null) === '35 × 50 mm (official MYVISA eVISA FAQ)'
        && ($photo['checked_at'] ?? null) === '2026-11-15'
        && ($photo['source_url'] ?? null) === $photoUrl
        && ! array_key_exists('digital_dimensions_px', $photo)
        && ! array_key_exists('file_size', $photo);

    $sourceChecks = [
        'primary' => $record->sources->contains(fn ($source) => $source->source_type === 'official' && (bool) $source->is_primary && $source->source_url === $primaryUrl && $sourceDate($source) === '2026-11-15'),
        'cross_check' => $record->sources->contains(fn ($source) => $source->source_type === 'official' && ! (bool) $source->is_primary && $source->source_url === $crossUrl && $sourceDate($source) === '2026-11-15'),
    ];
    foreach ($extraUrls as $url) {
        $sourceChecks[hash('sha256', $url)] = $record->sources->contains(fn ($source) => $source->source_type === 'official' && $source->source_url === $url && $sourceDate($source) === '2026-11-15');
    }

    $databaseChecks = [
        'country_count' => $countryCount === 252,
        'requirement_count' => $records->count() === 136,
        'published_count' => $published === 133,
        'pending_count' => $pending === 3,
        'duplicate_keys' => $duplicates === 0,
        'passport_scope' => $record->passport_nationality_iso3 === 'BGD' && $record->travel_purpose === 'tourism',
        'pending_unpublished' => $record->verification_status === 'pending_review' && ! (bool) $record->is_published,
        'country_identity' => $country->name === 'Malaysia' && $country->slug === 'malaysia' && strtoupper((string) $country->iso2) === 'MY' && strtoupper((string) $country->iso3) === 'MYS',
        'entry_flags' => (bool) $record->visa_required && ! (bool) $record->visa_free_available && ! (bool) $record->visa_on_arrival_available && (bool) $record->evisa_available,
        'application_fee' => str_contains((string) $record->application_fee, 'RM20') && str_contains((string) $record->application_fee, 'RM105'),
        'processing_time' => str_contains((string) $record->processing_time, '2–7') && str_contains((string) $record->processing_time, '14 days'),
        'validity_conflict_documented' => str_contains((string) $record->maximum_permitted_stay, '3 months') && str_contains((string) $record->maximum_permitted_stay, '6 months'),
        'required_documents' => is_array($record->required_documents) && count($record->required_documents) >= 8,
        'photo_specification' => $photoValid,
        'four_applicant_checklists' => $checklistsValid,
        'official_source_checked_at' => substr((string) $record->official_source_checked_at, 0, 10) === '2026-11-15',
        'official_sources' => ! in_array(false, $sourceChecks, true),
        'official_source_count' => $record->sources->where('source_type', 'official')->count() >= 8,
    ];
    $failed = array_keys(array_filter($databaseChecks, fn ($ok) => $ok !== true));
    if ($failed) {
        throw new RuntimeException('Database readback checks failed: ' . implode(', ', $failed));
    }

    $base = rtrim((string) config('app.url'), '/');
    $listResponse = Http::timeout(45)->acceptJson()->get($base . '/api/visa-requirements', ['passport' => 'BGD', 'purpose' => 'tourism', 'per_page' => 250]);
    $list = $listResponse->throw()->json();
    $detailResponse = Http::timeout(45)->acceptJson()->get($base . '/api/visa-requirements/MY', ['passport' => 'BGD', 'purpose' => 'tourism']);
    $detail = $detailResponse->json();
    $listRows = data_get($list, 'data', []);
    $myInPublicList = is_array($listRows) && collect($listRows)->contains(fn ($item) => strtoupper((string) data_get($item, 'country.iso2')) === 'MY');
    $detailHidden = $detailResponse->status() === 404 || data_get($detail, 'data.is_published') === false || data_get($detail, 'data') === null;

    $apiChecks = [
        'public_list_total' => data_get($list, 'meta.total') === 133,
        'MY_absent_from_public_list' => ! $myInPublicList,
        'MY_public_detail_hidden' => $detailHidden,
    ];
    $apiFailed = array_keys(array_filter($apiChecks, fn ($ok) => $ok !== true));
    if ($apiFailed) {
        throw new RuntimeException('Public API readback checks failed: ' . implode(', ', $apiFailed));
    }

    echo json_encode([
        'mode' => 'verify',
        'country_count' => $countryCount,
        'bgd_tourism_requirement_count' => $records->count(),
        'published_count' => $published,
        'pending_count' => $pending,
        'source_count' => $sourceCount,
        'duplicate_keys' => $duplicates,
        'MY' => [
            'verification_status' => $record->verification_status,
            'is_published' => (bool) $record->is_published,
            'photo_status' => data_get($photo, 'status'),
            'photo_dimensions_mm' => data_get($photo, 'dimensions_mm'),
            'applicant_checklist_types' => $types,
            'source_count' => $record->sources->count(),
        ],
        'public_api_total' => data_get($list, 'meta.total'),
        'public_list_contains_MY' => $myInPublicList,
        'public_detail_http_status' => $detailResponse->status(),
        'database_checks' => $databaseChecks,
        'api_checks' => $apiChecks,
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL;
} catch (Throwable $exception) {
    fwrite(STDERR, 'BATCH55_READBACK_ERROR ' . get_class($exception) . ': ' . $exception->getMessage() . PHP_EOL);
    exit(1);
}
