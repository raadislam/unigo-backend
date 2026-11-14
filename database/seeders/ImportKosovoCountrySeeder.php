<?php

namespace Database\\Seeders;

use App\\Models\\Country;
use Illuminate\\Database\\Seeder;

class ImportKosovoCountrySeeder extends Seeder
{
    public function run(): void
    {
        Country::firstOrCreate(
            ['iso2' => 'XK'],
            [
                'name' => 'Kosovo',
                'slug' => 'kosovo',
                'iso3' => null,
                'flag_emoji' => '🇽🇰',
                'capital' => 'Pristina',
                'short_description' => 'Explore Kosovo entry requirements for Bangladeshi ordinary passport holders.',
                'overview' => 'Kosovo is a destination in the Western Balkans. Bangladeshi ordinary passport holders generally require a visa for tourism or transit unless a specific exemption applies. A qualifying valid biometric Schengen residence permit or valid multiple-entry Schengen visa permits entry or transit for up to 15 days without a Kosovo visa. Visa applications are submitted in person at designated Kosovo diplomatic or consular missions.',
                'visa_information' => 'The Kosovo Ministry of Foreign Affairs lists Bangladesh among nationalities requiring a visa for ordinary passport holders. The Bangladesh-specific MFA page names Tirana and Istanbul as visa-issuing posts. Confirm the current application jurisdiction and appointment process before travel.',
                'seo_title' => 'Kosovo Visa Requirements for Bangladesh Passport Holders | UniGo',
                'seo_description' => 'Check Kosovo tourist visa requirements for Bangladeshi ordinary passport holders, including the Schengen exemption, application route, fees and documents.',
                'seo_keywords' => [
                    'Kosovo visa for Bangladesh passport',
                    'Bangladeshi passport Kosovo entry requirements',
                    'Kosovo tourist visa from Bangladesh',
                    'Kosovo visa application'
                ],
                'is_published' => false,
                'sort_order' => 250,
            ]
        );
    }
}
