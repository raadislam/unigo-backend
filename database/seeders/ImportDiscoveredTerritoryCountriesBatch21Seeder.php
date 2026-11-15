<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ImportDiscoveredTerritoryCountriesBatch21Seeder extends Seeder
{
    public function run(): void
    {
        $path = storage_path('app/imports/country_master_backfill_batch_21.csv');
        if (!is_file($path)) {
            throw new RuntimeException('Country master batch 21 CSV is missing.');
        }

        $handle = fopen($path, 'r');
        if ($handle === false) {
            throw new RuntimeException('Country master batch 21 CSV cannot be opened.');
        }

        try {
            $headers = fgetcsv($handle);
            if (!is_array($headers)) {
                throw new RuntimeException('Country master batch 21 CSV has no header.');
            }
            $rows = [];
            while (($values = fgetcsv($handle)) !== false) {
                if (count($values) !== count($headers)) {
                    throw new RuntimeException('Country master batch 21 contains a malformed row.');
                }
                $rows[] = array_combine($headers, $values);
            }
        } finally {
            fclose($handle);
        }

        $expected = ['AC' => 'Ascension Island', 'TA' => 'Tristan da Cunha'];
        $codes = array_map(fn (array $row) => strtoupper(trim($row['iso2'] ?? '')), $rows);
        sort($codes);
        $expectedCodes = array_keys($expected);
        sort($expectedCodes);
        if ($codes !== $expectedCodes || count($rows) !== 2) {
            throw new RuntimeException('Country master batch 21 must contain exactly AC and TA.');
        }

        $created = [];
        $present = [];
        DB::transaction(function () use ($rows, $expected, &$created, &$present): void {
            foreach ($rows as $row) {
                $iso2 = strtoupper(trim($row['iso2'] ?? ''));
                $name = trim($row['name'] ?? '');
                $slug = trim($row['slug'] ?? '');
                $iso3 = trim($row['iso3'] ?? '');
                $isPublished = filter_var($row['is_published'] ?? 'false', FILTER_VALIDATE_BOOLEAN);

                if (!isset($expected[$iso2]) || $expected[$iso2] !== $name || $iso3 !== '' || $isPublished) {
                    throw new RuntimeException("Invalid or unsafe country master row for {$iso2}.");
                }
                if (!str_contains(strtolower($row['code_status'] ?? ''), 'exceptionally reserved')) {
                    throw new RuntimeException("Exceptional territory code status is missing for {$iso2}.");
                }
                if (trim($row['flag_emoji'] ?? '') !== '') {
                    throw new RuntimeException("Emoji flags are prohibited for {$iso2}.");
                }
                $sourceUrls = array_filter(explode(';', (string) ($row['official_source_urls'] ?? '')));
                if (count(array_filter($sourceUrls, fn ($url) => str_starts_with($url, 'https://'))) < 2) {
                    throw new RuntimeException("Official source URLs are incomplete for {$iso2}.");
                }
                $keywords = json_decode((string) ($row['seo_keywords'] ?? '[]'), true);
                if (!is_array($keywords) || $keywords === []) {
                    throw new RuntimeException("SEO keywords are invalid for {$iso2}.");
                }

                $existing = Country::query()->where('iso2', $iso2)->first();
                if ($existing) {
                    if ($existing->name !== $name || $existing->slug !== $slug || $existing->iso3 !== null || (bool) $existing->is_published !== false || $existing->flag_emoji !== null) {
                        throw new RuntimeException("Existing country row {$iso2} conflicts with the approved master batch.");
                    }
                    $present[] = $iso2;
                    continue;
                }
                if (Country::query()->where('slug', $slug)->exists()) {
                    throw new RuntimeException("Country slug {$slug} is already assigned to another row.");
                }

                Country::query()->create([
                    'name' => $name,
                    'slug' => $slug,
                    'iso2' => $iso2,
                    'iso3' => null,
                    'capital' => trim($row['capital'] ?? '') ?: null,
                    'flag_emoji' => null,
                    'short_description' => trim($row['short_description'] ?? ''),
                    'overview' => trim($row['overview'] ?? ''),
                    'visa_information' => trim($row['visa_information'] ?? '') ?: null,
                    'seo_title' => trim($row['seo_title'] ?? ''),
                    'seo_description' => trim($row['seo_description'] ?? ''),
                    'seo_keywords' => $keywords,
                    'is_published' => false,
                    'sort_order' => (int) ($row['sort_order'] ?? 0),
                ]);
                $created[] = $iso2;
            }
        });

        $this->command?->info('COUNTRY_MASTER_BATCH21_CREATED=' . implode(',', $created));
        $this->command?->info('COUNTRY_MASTER_BATCH21_PRESENT=' . implode(',', $present));
        $this->command?->info('COUNTRY_MASTER_BATCH21_UNPUBLISHED=true');
    }
}
