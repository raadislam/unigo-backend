<?php

use App\Models\VisaRequirement;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use RuntimeException;

$root = getenv('APP_ROOT') ?: dirname(__DIR__);
require $root . '/vendor/autoload.php';
$app = require $root . '/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$sourceUrl = 'https://www.ascension-visas.com/useful-information';
$sourceName = 'Ascension Island Government — eVisa Eligibility Information';
$record = VisaRequirement::query()
    ->where('passport_nationality_iso3', 'BGD')
    ->where('travel_purpose', 'tourism')
    ->whereHas('country', fn ($query) => $query->where('iso2', 'AC'))
    ->first();

if (! $record) {
    throw new RuntimeException('The allowlisted AC / BGD / tourism requirement is missing.');
}

$sourceQuery = DB::table('visa_requirement_sources')
    ->where('visa_requirement_id', $record->id)
    ->where('source_url', $sourceUrl)
    ->where('source_name', $sourceName)
    ->where('is_primary', false);

if ((clone $sourceQuery)->count() !== 1) {
    throw new RuntimeException('Expected exactly one official AIG eVisa eligibility cross-check source for AC.');
}

$source = $sourceQuery->first();
if (! in_array($source->source_type, ['secondary', 'official'], true)) {
    throw new RuntimeException('The AC cross-check source has an unexpected source classification.');
}

$updated = 0;
if ($source->source_type === 'secondary') {
    $updated = DB::table('visa_requirement_sources')
        ->where('id', $source->id)
        ->where('source_type', 'secondary')
        ->update(['source_type' => 'official']);
}

$verified = DB::table('visa_requirement_sources')->where('id', $source->id)->first();
if (! $verified
    || $verified->source_type !== 'official'
    || (bool) $verified->is_primary
    || substr((string) $verified->source_last_checked_at, 0, 10) !== '2026-11-15') {
    throw new RuntimeException('The official AIG eVisa eligibility cross-check did not pass exact readback.');
}

$result = [
    'destination_iso2' => 'AC',
    'passport_iso3' => 'BGD',
    'travel_purpose' => 'tourism',
    'source_type' => $verified->source_type,
    'source_name' => $verified->source_name,
    'source_url' => $verified->source_url,
    'is_primary' => (bool) $verified->is_primary,
    'source_last_checked_at' => substr((string) $verified->source_last_checked_at, 0, 10),
    'updated_rows' => $updated,
    'result' => 'ASCENSION_CROSS_CHECK_SOURCE_REPAIRED',
];

echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL;
