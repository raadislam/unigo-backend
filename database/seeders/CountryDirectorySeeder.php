<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\Service;
use App\Models\VisaCategory;
use Illuminate\Database\Seeder;

class CountryDirectorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'E-Visa', 'slug' => 'e-visa', 'sort_order' => 1],
            ['name' => 'Flexible Submission', 'slug' => 'flexible-submission', 'sort_order' => 2],
            ['name' => 'In-Person Submission', 'slug' => 'in-person-submission', 'sort_order' => 3],
            ['name' => 'Visa Free', 'slug' => 'visa-free', 'sort_order' => 4],
            ['name' => 'Visa on Arrival', 'slug' => 'visa-on-arrival', 'sort_order' => 5],
        ];

        foreach ($categories as $category) {
            VisaCategory::updateOrCreate(['slug' => $category['slug']], $category);
        }

        $services = [
            ['name' => 'Visa Consultancy', 'slug' => 'visa-consultancy', 'sort_order' => 1],
            ['name' => 'Document Legalization', 'slug' => 'document-legalization', 'sort_order' => 2],
            ['name' => 'E-Visa Processing', 'slug' => 'e-visa-processing', 'sort_order' => 3],
            ['name' => 'Premium Consultancy', 'slug' => 'premium-consultancy', 'sort_order' => 4],
            ['name' => 'Express Consultation', 'slug' => 'express-consultation', 'sort_order' => 5],
            ['name' => 'Study Visa Guidance', 'slug' => 'study-visa-guidance', 'sort_order' => 6],
        ];

        foreach ($services as $service) {
            Service::updateOrCreate(['slug' => $service['slug']], $service);
        }

        $countries = [
            ['name' => 'Afghanistan', 'slug' => 'afghanistan', 'iso2' => 'AF', 'iso3' => 'AFG', 'flag_emoji' => '🇦🇫'],
            ['name' => 'Albania', 'slug' => 'albania', 'iso2' => 'AL', 'iso3' => 'ALB', 'flag_emoji' => '🇦🇱'],
            ['name' => 'Algeria', 'slug' => 'algeria', 'iso2' => 'DZ', 'iso3' => 'DZA', 'flag_emoji' => '🇩🇿'],
            ['name' => 'Andorra', 'slug' => 'andorra', 'iso2' => 'AD', 'iso3' => 'AND', 'flag_emoji' => '🇦🇩'],
            ['name' => 'Angola', 'slug' => 'angola', 'iso2' => 'AO', 'iso3' => 'AGO', 'flag_emoji' => '🇦🇴'],
            ['name' => 'Antigua and Barbuda', 'slug' => 'antigua-and-barbuda', 'iso2' => 'AG', 'iso3' => 'ATG', 'flag_emoji' => '🇦🇬'],
            ['name' => 'Argentina', 'slug' => 'argentina', 'iso2' => 'AR', 'iso3' => 'ARG', 'flag_emoji' => '🇦🇷'],
            ['name' => 'Armenia', 'slug' => 'armenia', 'iso2' => 'AM', 'iso3' => 'ARM', 'flag_emoji' => '🇦🇲'],
            ['name' => 'Australia', 'slug' => 'australia', 'iso2' => 'AU', 'iso3' => 'AUS', 'flag_emoji' => '🇦🇺'],
            ['name' => 'Bangladesh', 'slug' => 'bangladesh', 'iso2' => 'BD', 'iso3' => 'BGD', 'flag_emoji' => '🇧🇩'],
            ['name' => 'Canada', 'slug' => 'canada', 'iso2' => 'CA', 'iso3' => 'CAN', 'flag_emoji' => '🇨🇦'],
            ['name' => 'Malaysia', 'slug' => 'malaysia', 'iso2' => 'MY', 'iso3' => 'MYS', 'flag_emoji' => '🇲🇾'],
            ['name' => 'Thailand', 'slug' => 'thailand', 'iso2' => 'TH', 'iso3' => 'THA', 'flag_emoji' => '🇹🇭'],
            ['name' => 'United Kingdom', 'slug' => 'united-kingdom', 'iso2' => 'GB', 'iso3' => 'GBR', 'flag_emoji' => '🇬🇧'],
            ['name' => 'United States', 'slug' => 'united-states', 'iso2' => 'US', 'iso3' => 'USA', 'flag_emoji' => '🇺🇸'],
        ];

        foreach ($countries as $index => $countryData) {
            $country = Country::updateOrCreate(
                ['iso2' => $countryData['iso2']],
                array_merge($countryData, [
                    'short_description' => 'Explore visa information and available UniGo assistance for ' . $countryData['name'] . '.',
                    'seo_title' => $countryData['name'] . ' Visa Services from Bangladesh | UniGo',
                    'seo_description' => 'Explore ' . $countryData['name'] . ' visa information, application guidance and available services from UniGo Bangladesh.',
                    'is_published' => false,
                    'sort_order' => $index + 1,
                ])
            );

        }
    }
}