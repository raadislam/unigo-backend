<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Seeder;

class KosovoCountryBackfillSeeder extends Seeder
{
    public function run(): void
    {
        Country::firstOrCreate(
            ['iso2' => 'XK'],
            [
                'name' => 'Kosovo',
                'slug' => 'kosovo',
                'iso3' => 'XKX',
                'flag_emoji' => '🇽🇰',
                'capital' => 'Pristina',
                'short_description' => 'A landlocked Balkan destination in southeastern Europe.',
                'seo_title' => 'Kosovo Visa Requirements for Bangladesh Passport Holders | UniGo',
                'seo_description' => 'Check Kosovo short-stay visa requirements for Bangladeshi ordinary passport holders, including Visa C, the EUR 40 fee and conditional Schengen exemptions.',
                'seo_keywords' => [
                    'Kosovo visa for Bangladeshi citizens',
                    'Kosovo tourist visa',
                    'Bangladesh passport Kosovo visa',
                    'Kosovo Visa C',
                ],
                'is_published' => false,
                'sort_order' => 250,
            ]
        );
    }
}
