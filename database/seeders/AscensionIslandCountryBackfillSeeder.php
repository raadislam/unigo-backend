<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Seeder;

class AscensionIslandCountryBackfillSeeder extends Seeder
{
    public function run(): void
    {
        Country::firstOrCreate(
            ['iso2' => 'AC'],
            [
                'name' => 'Ascension Island',
                'slug' => 'ascension-island',
                'iso3' => null,
                'flag_emoji' => null,
                'capital' => null,
                'short_description' => 'A remote South Atlantic British Overseas Territory with an online visitor eVisa requirement.',
                'overview' => 'Ascension Island is part of the Saint Helena, Ascension and Tristan da Cunha British Overseas Territory. Ordinary Bangladesh passport holders are not listed among the nationalities excluded from the official AIG visitor eVisa route. A visitor eVisa is required before travel, subject to the possible additional multi-entry UK visa condition for UK visa nationals.',
                'visa_information' => 'Visitor eVisa required before travel. Single entry, up to three months per visit and no more than three months cumulatively in any 12-month period. Passport validity must be at least six months at entry. Visitor fee: GBP 40 standard, GBP 60 fast-track/priority, GBP 10 under-12s. AIG targets 90% of decisions within 21 days and all within six weeks; fast-track target 14 days. Full-stay medical insurance including air evacuation is mandatory, with at least GBP 500,000 evacuation cover. AIG may require a multi-entry UK visa for Bangladesh nationals because Bangladesh is a UK visa-national country; applicants must confirm this condition with AIG.',
                'seo_title' => 'Ascension Island Visa Requirements for Bangladesh Passport Holders | UniGo',
                'seo_description' => 'Ascension Island visitor eVisa requirements for Bangladesh ordinary passport holders, including fees, stay, insurance, photo guidance and the conditional UK visa rule.',
                'seo_keywords' => ['Ascension Island visa for Bangladesh', 'Bangladesh passport Ascension Island eVisa', 'Ascension Island visitor visa', 'Ascension Island visa fee', 'Ascension Island photo requirements'],
                'is_published' => false,
                'sort_order' => 252,
            ]
        );
    }
}
