<?php

namespace Database\\Seeders;

use App\\Models\\Country;
use Illuminate\\Database\\Seeder;
use Illuminate\\Support\\Facades\\DB;
use RuntimeException;

class ImportGlobalCountriesSeeder extends Seeder
{
    public function run(): void
    {
        $path = storage_path('app/imports/unigo_professional_country_import.csv');

        if (! is_file($path)) {
            throw new RuntimeException('Country import CSV was not found at ' . $path);
        }

        $handle = fopen($path, 'r');

        if ($handle === false) {
            throw new RuntimeException('Unable to open the country import CSV.');
        }

        $headers = fgetcsv($handle);

        if (! is_array($headers)) {
            fclose($handle);
            throw new RuntimeException('The country import CSV is empty.');
        }

        $headers[0] = preg_replace('/^\\xEF\\xBB\\xBF/', '', $headers[0]);
        $required = ['name', 'slug', 'iso2', 'iso3', 'flag_emoji', 'capital', 'short_description', 'overview', 'seo_title', 'seo_description'];
        $missing = array_diff($required, $headers);

        if ($missing !== []) {
            fclose($handle);
            throw new RuntimeException('Missing CSV columns: ' . implode(', ', $missing));
        }

        $count = 0;

        DB::transaction(function () use ($handle, $headers, &$count): void {
            while (($values = fgetcsv($handle)) !== false) {
                if (count($values) !== count($headers)) {
                    continue;
                }

                $row = array_combine($headers, $values);

                if (empty($row['name']) || empty($row['iso2']) || empty($row['slug'])) {
                    continue;
                }

                Country::updateOrCreate(
                    ['iso2' => strtoupper(trim($row['iso2']))],
                    [
                        'name' => trim($row['name']),
                        'slug' => trim($row['slug']),
                        'iso2' => strtoupper(trim($row['iso2'])),
                        'iso3' => trim($row['iso3']) ?: null,
                        'flag_emoji' => trim($row['flag_emoji']) ?: null,
                        'capital' => trim($row['capital']) ?: null,
                        'short_description' => trim($row['short_description']) ?: null,
                        'overview' => trim($row['overview']) ?: null,
                        'visa_information' => null,
                        'seo_title' => trim($row['seo_title']) ?: null,
                        'seo_description' => trim($row['seo_description']) ?: null,
                        'seo_keywords' => [
                            trim($row['name']) . ' visa',
                            trim($row['name']) . ' visa from Bangladesh',
                            'Bangladesh passport ' . trim($row['name']) . ' entry requirements',
                        ],
                        'is_published' => false,
                        'sort_order' => $count + 1,
                    ]
                );

                $count++;
            }
        });

        fclose($handle);

        $this->command?->info($count . ' country records imported or updated. All remain unpublished pending visa verification.');
    }
}
