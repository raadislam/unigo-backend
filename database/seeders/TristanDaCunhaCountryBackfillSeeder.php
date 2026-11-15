<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Seeder;

class TristanDaCunhaCountryBackfillSeeder extends Seeder
{
    public function run(): void
    {
        Country::firstOrCreate(
            ['iso2' => 'TA'],
            [
                'name' => 'Tristan da Cunha',
                'slug' => 'tristan-da-cunha',
                'iso3' => null,
                'flag_emoji' => null,
                'capital' => null,
                'short_description' => 'A remote South Atlantic island destination requiring advance Island Council approval before travel.',
                'overview' => 'Tristan da Cunha is a remote South Atlantic British Overseas Territory. Visitors must obtain advance approval from the Administrator / Island Council. A valid passport, confirmed and fully paid return passage, adequate medical insurance including evacuation to Cape Town, and sufficient funds are required. Visitors receive a landing stamp on arrival; a visa is not required.',
                'visa_information' => 'No visa is required, but prior territorial permission is mandatory. Main-island landing and harbour charges are GBP 30 for cruise passengers and GBP 15 for yacht passengers. The additional Nightingale / Inaccessible landing charge differs between two official pages and should be confirmed with the Administrator if those islands are included.',
                'seo_title' => 'Tristan da Cunha Entry Requirements for Bangladesh Passport Holders | UniGo',
                'seo_description' => 'Check Tristan da Cunha visitor permission, Island Council approval, landing stamp, vessel passage, insurance and landing fees for Bangladeshi ordinary passport holders.',
                'seo_keywords' => [
                    'Tristan da Cunha entry requirements Bangladesh',
                    'Tristan da Cunha visitor permission',
                    'Bangladesh passport Tristan da Cunha',
                    'Tristan da Cunha landing permission',
                ],
                'is_published' => false,
                'sort_order' => 251,
            ]
        );
    }
}
