<?php

namespace Database\Seeders;

use App\Models\VisaRequirement;
use App\Models\VisaRequirementSource;
use Illuminate\Database\Seeder;

class VisaPrimarySourceRepairSeeder extends Seeder
{
    public function run(): void
    {
        $checkedAt = '2026-11-14 00:00:00';

        $sources = [
            'KR' => [
                [
                    'source_type' => 'official',
                    'source_name' => 'Embassy of the Republic of Korea in Bangladesh — Tourist Visa Checklist',
                    'source_url' => 'https://overseas.mofa.go.kr/bd-en/brd/m_23302/view.do?seq=14',
                    'is_primary' => true,
                    'verification_notes' => 'Primary official mission source for Bangladesh ordinary-passport tourist visa requirements. Checked 14 November 2026.',
                ],
                [
                    'source_type' => 'official',
                    'source_name' => 'Embassy of the Republic of Korea in Bangladesh — Visa Notice and Application Information',
                    'source_url' => 'https://overseas.mofa.go.kr/bd-en/brd/m_2124/view.do?seq=760059',
                    'is_primary' => false,
                    'verification_notes' => 'Official Embassy supporting notice for the Bangladesh application route. Checked 14 November 2026.',
                ],
                [
                    'source_type' => 'official',
                    'source_name' => 'VFS Global — Korea Visa Application Centre, Bangladesh',
                    'source_url' => 'https://visa.vfsglobal.com/bgd/en/kor',
                    'is_primary' => false,
                    'verification_notes' => 'Official application-centre partner portal; the Embassy remains the visa decision-maker. Checked 14 November 2026.',
                ],
            ],
            'SG' => [
                [
                    'source_type' => 'official',
                    'source_name' => 'High Commission of Singapore in Dhaka — Visa Application',
                    'source_url' => 'https://dhaka.mfa.gov.sg/consular-services/visa-information/visa-application/',
                    'is_primary' => true,
                    'verification_notes' => 'Primary official mission source for Bangladesh ordinary-passport entry visa applications. Checked 14 November 2026.',
                ],
                [
                    'source_type' => 'official',
                    'source_name' => 'Immigration & Checkpoints Authority of Singapore — Bangladesh Visa Requirements',
                    'source_url' => 'https://www.ica.gov.sg/enter-transit-depart/entering-singapore/visa_requirements/visa-detail-page/bangladesh',
                    'is_primary' => false,
                    'verification_notes' => 'Official ICA nationality-specific visa requirement cross-check. Checked 14 November 2026.',
                ],
            ],
        ];

        foreach ($sources as $iso2 => $records) {
            $requirement = VisaRequirement::query()
                ->whereHas('country', fn ($query) => $query->where('iso2', $iso2))
                ->where('passport_nationality_iso3', 'BGD')
                ->where('travel_purpose', 'tourism')
                ->where('verification_status', 'verified')
                ->where('is_published', true)
                ->first();

            if (! $requirement) {
                continue;
            }

            foreach ($records as $source) {
                VisaRequirementSource::updateOrCreate(
                    [
                        'visa_requirement_id' => $requirement->id,
                        'source_url' => $source['source_url'],
                    ],
                    [
                        'source_type' => $source['source_type'],
                        'source_name' => $source['source_name'],
                        'source_url' => $source['source_url'],
                        'source_last_checked_at' => $checkedAt,
                        'is_primary' => $source['is_primary'],
                        'verification_notes' => $source['verification_notes'],
                    ]
                );
            }

            $requirement->forceFill(['official_source_checked_at' => $checkedAt])->save();
        }
    }
}
