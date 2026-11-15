<?php

$root = getenv('UNIGO_APP_ROOT') ?: dirname(__DIR__);
chdir($root);

require $root . '/vendor/autoload.php';
$app = require $root . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$mode = $argv[1] ?? '';
if (! in_array($mode, ['preflight', 'readback'], true)) {
    fwrite(STDERR, "Expected preflight or readback mode.\n");
    exit(2);
}

$countryCount = App\Models\Country::query()->count();
$records = App\Models\VisaRequirement::query()
    ->where('passport_nationality_iso3', 'BGD')
    ->where('travel_purpose', 'tourism')
    ->get();
$publishedCount = $records->filter(
    fn ($row) => $row->verification_status === 'verified' && (bool) $row->is_published
)->count();
$pendingCount = $records->count() - $publishedCount;
$sourceCount = Illuminate\Support\Facades\DB::table('visa_requirement_sources')->count();
$duplicateKeys = Illuminate\Support\Facades\DB::table('visa_requirements')
    ->where('passport_nationality_iso3', 'BGD')
    ->where('travel_purpose', 'tourism')
    ->select('country_id', 'passport_nationality_iso3', 'travel_purpose')
    ->groupBy('country_id', 'passport_nationality_iso3', 'travel_purpose')
    ->havingRaw('COUNT(*) > 1')
    ->count();

$country = App\Models\Country::query()->where('iso2', 'AC')->first();
$slugConflict = App\Models\Country::query()
    ->where('slug', 'ascension-island')
    ->where('iso2', '!=', 'AC')
    ->exists();
$ascensionRequirementCount = $country
    ? App\Models\VisaRequirement::query()
        ->where('country_id', $country->id)
        ->where('passport_nationality_iso3', 'BGD')
        ->where('travel_purpose', 'tourism')
        ->count()
    : 0;

$countryIdentityValid = $country
    && $country->name === 'Ascension Island'
    && $country->slug === 'ascension-island'
    && $country->iso2 === 'AC'
    && $country->iso3 === null
    && $country->flag_emoji === null
    && ! (bool) $country->is_published
    && (int) $country->sort_order > 0
    && $country->visa_information === null;

$baselineValid = $records->count() === 136
    && $publishedCount === 133
    && $pendingCount === 3
    && $sourceCount === 924
    && $duplicateKeys === 0
    && ! $slugConflict
    && $ascensionRequirementCount === 0;

if ($mode === 'preflight') {
    $countryStateValid = ($countryCount === 251 && ! $country)
        || ($countryCount === 252 && $countryIdentityValid);

    $result = [
        'mode' => 'preflight',
        'country_count' => $countryCount,
        'requirement_count' => $records->count(),
        'source_count' => $sourceCount,
        'published_count' => $publishedCount,
        'pending_count' => $pendingCount,
        'duplicate_keys' => $duplicateKeys,
        'AC_exists' => (bool) $country,
        'AC_identity_valid' => (bool) $countryIdentityValid,
        'AC_BGD_tourism_requirement_count' => $ascensionRequirementCount,
        'can_proceed' => $baselineValid && $countryStateValid,
    ];

    if (! $baselineValid || ! $countryStateValid) {
        fwrite(STDERR, json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL);
        exit(1);
    }

    echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
    exit(0);
}

$finalValid = $countryCount === 252
    && (bool) $countryIdentityValid
    && $baselineValid
    && $ascensionRequirementCount === 0;

$result = [
    'mode' => 'readback',
    'country_count' => $countryCount,
    'requirement_count' => $records->count(),
    'source_count' => $sourceCount,
    'published_count' => $publishedCount,
    'pending_count' => $pendingCount,
    'duplicate_keys' => $duplicateKeys,
    'AC' => $country ? [
        'name' => $country->name,
        'slug' => $country->slug,
        'iso2' => $country->iso2,
        'iso3' => $country->iso3,
        'flag_emoji' => $country->flag_emoji,
        'is_published' => (bool) $country->is_published,
        'sort_order' => $country->sort_order,
    ] : null,
    'AC_BGD_tourism_requirement_count' => $ascensionRequirementCount,
    'readback_passed' => $finalValid,
];

if (! $finalValid) {
    fwrite(STDERR, json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL);
    exit(1);
}

echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
