<?php

$root = rtrim((string) (getenv('UNIGO_APP_DIR') ?: dirname(__DIR__)), DIRECTORY_SEPARATOR);

if (! is_file($root . '/vendor/autoload.php') || ! is_file($root . '/bootstrap/app.php')) {
    throw new RuntimeException('The configured Laravel application directory is invalid.');
}

require $root . '/vendor/autoload.php';

$app = require $root . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$records = \App\Models\VisaRequirement::query()
    ->with('country:id,name,slug,iso2')
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

$imCountry = \App\Models\Country::query()->where('iso2', 'IM')->first();
$imRows = $records->filter(fn ($record) => $record->country?->iso2 === 'IM');
$im = $imRows->first();

$result = [
    'country_count' => \App\Models\Country::query()->count(),
    'requirement_count' => $records->count(),
    'published_count' => $published,
    'pending_count' => $pending,
    'duplicate_keys' => $duplicates,
    'IM_country_present' => (bool) $imCountry,
    'IM_requirement_count' => $imRows->count(),
    'IM_country_name' => $im?->country?->name,
    'IM_country_slug' => $im?->country?->slug,
    'IM_passport' => $im?->passport_nationality_iso3,
    'IM_purpose' => $im?->travel_purpose,
    'IM_status' => $im?->verification_status,
    'IM_is_published' => (bool) ($im?->is_published ?? false),
    'IM_country_linked' => (bool) ($im?->country_id ?? false),
    'IM_source_count' => $im?->sources()->count() ?? 0,
    'photo_specifications_column' => \Illuminate\Support\Facades\Schema::hasColumn('visa_requirements', 'photo_specifications'),
    'applicant_checklists_column' => \Illuminate\Support\Facades\Schema::hasColumn('visa_requirements', 'applicant_checklists'),
];

$valid = $result['country_count'] === 252
    && $result['requirement_count'] === 136
    && $result['published_count'] === 134
    && $result['pending_count'] === 2
    && $result['duplicate_keys'] === 0
    && $result['IM_country_present']
    && $result['IM_requirement_count'] === 1
    && $result['IM_country_name'] === 'Isle of Man'
    && $result['IM_country_slug'] === 'isle-of-man'
    && $result['IM_passport'] === 'BGD'
    && $result['IM_purpose'] === 'tourism'
    && $result['IM_status'] === 'pending_review'
    && ! $result['IM_is_published']
    && $result['IM_country_linked']
    && $result['photo_specifications_column']
    && $result['applicant_checklists_column'];

if (! $valid) {
    fwrite(STDERR, json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL);
    exit(1);
}

echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
