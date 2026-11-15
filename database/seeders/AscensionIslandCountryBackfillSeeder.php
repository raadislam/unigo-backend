<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class AscensionIslandCountryBackfillSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $country = Country::query()->where('iso2', 'AC')->first();

            if ($country) {
                if ($country->name !== 'Ascension Island' || $country->slug !== 'ascension-island') {
                    throw new RuntimeException('The AC country row exists with an unexpected identity; no fields were changed.');
                }

                if ($country->iso3 !== null || (bool) $country->is_published) {
                    throw new RuntimeException('The existing AC country row is not an unpublished reserved-code master row.');
                }

                $this->command?->info('Ascension Island master row already exists; no existing fields were overwritten.');
                return;
            }

            if (Country::query()->where('slug', 'ascension-island')->exists()) {
                throw new RuntimeException('The ascension-island slug is already assigned to another country row.');
            }

            Country::query()->create([
                'name' => 'Ascension Island',
                'slug' => 'ascension-island',
                'iso2' => 'AC',
                'iso3' => null,
                'flag_emoji' => null,
                'capital' => null,
                'short_description' => 'A remote South Atlantic island within the British Overseas Territory of Saint Helena, Ascension and Tristan da Cunha.',
                'overview' => 'Ascension Island is a distinct visitor destination within the British Overseas Territory of Saint Helena, Ascension and Tristan da Cunha. Its government operates a separate entry-control and eVisa system. Bangladesh ordinary-passport eligibility remains under official confirmation. This unpublished master record contains no visa permission or applicant advice.',
                'visa_information' => null,
                'seo_title' => 'Ascension Island Entry Information for Bangladesh Passport Holders | UniGo',
                'seo_description' => 'Research status for Ascension Island visitor entry. Bangladesh ordinary-passport eVisa eligibility and route conditions remain under official confirmation.',
                'seo_keywords' => [
                    'Ascension Island entry requirements Bangladesh',
                    'Ascension Island visa Bangladesh passport',
                    'Bangladesh ordinary passport Ascension Island',
                ],
                'is_published' => false,
                'sort_order' => ((int) Country::query()->max('sort_order')) + 1,
            ]);

            $created = Country::query()->where('iso2', 'AC')->first();

            if (! $created || $created->slug !== 'ascension-island' || (bool) $created->is_published) {
                throw new RuntimeException('Ascension Island unpublished master-row readback failed.');
            }

            $this->command?->info('Ascension Island master row created as unpublished. No visa requirement was created.');
        });
    }
}
