<?php

namespace Database\Seeders;

use App\Models\VisaRequirement;
use App\Models\VisaRequirementSource;
use Illuminate\Database\Seeder;

class VisaPhotoChecklistSupplementSeeder extends Seeder
{
    public function run(): void
    {
        $checkedAt = '2026-11-14 00:00:00';

        $data = [
            'CA' => [
                'photo' => [
                    'status' => 'verified',
                    'route' => 'Paper temporary resident visa application; online applications follow the personalised IRCC portal checklist.',
                    'dimensions_mm' => 'At least 35 × 45 mm',
                    'head_measurements' => 'Chin to crown: 31–36 mm',
                    'background' => 'Plain white or light-coloured background',
                    'photo_age' => 'Taken within the last 6 months',
                    'copies' => '2 identical printed photographs for paper applications',
                    'notes' => 'Do not apply the printed-photo dimensions to the online upload route. Follow the personalised IRCC checklist and photo upload instructions.',
                    'source_name' => 'IRCC — Temporary Resident Visa photograph specifications',
                    'source_url' => 'https://www.canada.ca/en/immigration-refugees-citizenship/services/application/application-forms-guides/temporary-resident-visa-application-photograph-specifications.html',
                ],
            ],
            'GB' => [
                'photo' => [
                    'status' => 'verified',
                    'route' => 'Digital photo for UK visa application; a VAC may capture biometrics separately.',
                    'digital_dimensions_px' => 'Minimum 600 × 750 pixels',
                    'file_size' => '50 KB to 6 MB',
                    'format' => 'JPG or JPEG; portrait orientation',
                    'background' => 'Plain light-coloured background',
                    'photo_age' => 'Use a current, genuine image; do not reuse a passport/ID photo',
                    'notes' => 'Do not use a scan of a printed photograph, filters, retouching or AI edits. The application route will tell you whether to upload a photo or have one captured at the centre.',
                    'source_name' => 'GOV.UK — Take a photo of your face for a visa application',
                    'source_url' => 'https://www.gov.uk/guidance/how-to-take-a-photo-for-a-visa-application-or-permission',
                ],
            ],
            'JP' => [
                'photo' => [
                    'status' => 'verified',
                    'route' => 'Japan short-term visa through VFS Global Bangladesh',
                    'dimensions_mm' => '2 × 1.4 inches (approximately 50.8 × 35.6 mm)',
                    'background' => 'Plain white background',
                    'photo_age' => 'Taken within the last 6 months',
                    'copies' => 'As specified by the current VFS checklist; VFS page lists 4-photo optional service',
                    'notes' => 'The Embassy website contains older 3.5 × 4.5 cm guidance. Use the current VFS Bangladesh instructions for applications lodged through VFS and recheck before submission.',
                    'source_name' => 'VFS Global — Japan visa information, Bangladesh',
                    'source_url' => 'https://www.vfsglobal.com/one-pager/japan/bangladesh/english/index.html',
                ],
            ],
            'KR' => [
                'photo' => [
                    'status' => 'verified',
                    'route' => 'South Korea tourist visa application in Bangladesh',
                    'dimensions_mm' => '35 × 45 mm',
                    'background' => 'Colour photograph; follow the Embassy/VAC current photo sample',
                    'photo_age' => 'Taken within the last 6 months',
                    'copies' => 'One colour passport-size photograph for the tourist checklist',
                    'notes' => 'The Embassy e-form requires a natural-colour 3.5 × 4.5 cm digital image. General visa submissions are handled by the Korea Visa Application Center from 2 September 2026.',
                    'source_name' => 'Embassy of the Republic of Korea in Bangladesh — Tourist visa checklist',
                    'source_url' => 'https://overseas.mofa.go.kr/bd-en/brd/m_23302/view.do?seq=14',
                ],
            ],
            'SG' => [
                'photo' => [
                    'status' => 'verified',
                    'route' => 'Digital visa application for Bangladeshi nationals',
                    'digital_dimensions_px' => '400 × 514 pixels',
                    'file_size' => 'Maximum 60 KB',
                    'format' => 'JPEG',
                    'background' => 'White background; matt or semi-matt finish',
                    'photo_age' => 'Taken within the past 3 months',
                    'head_measurements' => 'Chin to crown: 25–35 mm',
                    'notes' => 'Full face must be visible. Religious or customary headgear is acceptable if facial features are not hidden.',
                    'source_name' => 'High Commission of Singapore in Dhaka — Visa application',
                    'source_url' => 'https://dhaka.mfa.gov.sg/consular-services/visa-information/visa-application/',
                ],
            ],
            'NZ' => [
                'photo' => [
                    'status' => 'verified',
                    'route' => 'Online visa or NZeTA application',
                    'digital_dimensions_px' => '3:4 portrait ratio; 900 × 1200 to 2250 × 3000 pixels accepted by photo validation guidance',
                    'file_size' => '512 KB to 3.14 MB',
                    'format' => 'JPG or JPEG',
                    'photo_age' => 'Taken within the last 6 months',
                    'background' => 'Plain, neutral background with even lighting',
                    'notes' => 'Do not use AI enhancement, filters, retouching or digital alteration. Paper applications use a separate photo rule; the older paper guide specifies approximately 45 × 35 mm.',
                    'source_name' => 'Immigration New Zealand — Acceptable photos for a visa or NZeTA',
                    'source_url' => 'https://www.immigration.govt.nz/process-to-apply/applying-for-a-visa/applying-online/uploading-documents-and-photos/visa-and-nzeta-photos/',
                ],
            ],
            'TH' => [
                'photo' => [
                    'status' => 'verified',
                    'route' => 'Paper visa application form; online Thai e-Visa has a separate upload workflow',
                    'dimensions_mm' => '35 × 45 mm',
                    'copies' => '2 photographs on the VFS-hosted paper application form',
                    'photo_age' => 'Taken within the last 6 months',
                    'notes' => 'Do not assume the paper photo dimensions or copy count are the digital e-Visa upload limits. Follow the upload instructions in the official Thai e-Visa portal.',
                    'source_name' => 'VFS Global — Thailand visa application form, Bangladesh',
                    'source_url' => 'https://visa.vfsglobal.com/one-pager/thailand/bangladesh/english/pdf/paper-application-form-sep-2021.pdf',
                ],
            ],
            'DE' => [
                'photo' => [
                    'status' => 'verified',
                    'route' => 'Germany/Estonia Schengen tourism application in Bangladesh; other Schengen missions may have their own checklist',
                    'dimensions_mm' => '35 × 45 mm',
                    'background' => 'White background',
                    'head_measurements' => 'Face covers 70–80% of the photograph',
                    'notes' => 'The cited checklist is the German Embassy Dhaka tourism checklist dated January 2026 and is valid when Germany or Estonia is the main destination. Apply to the Schengen state responsible for the main destination.',
                    'source_name' => 'German Embassy Dhaka — Schengen tourism information sheet, January 2026',
                    'source_url' => 'https://visa.vfsglobal.com/one-pager/germany/bangladesh/english/pdf/VFS_Requirements_for_Tourist_visa_.pdf',
                ],
            ],
            'AU' => [
                'photo' => [
                    'status' => 'not_published',
                    'route' => 'Australian Visitor visa subclass 600 online application through ImmiAccount',
                    'notes' => 'The official visitor-visa guidance reviewed does not prescribe a universal printed photo size for this online route. A facial image is required for each applicant and the application portal specifies the upload step. Follow the personalised ImmiAccount request; do not reuse another country’s photo dimensions.',
                    'source_name' => 'Australian Department of Home Affairs — Attach documents to your application',
                    'source_url' => 'https://immi.homeaffairs.gov.au/help-support/applying-online-or-on-paper/online/attach-documents-to-your-application',
                ],
            ],
        ];

        $baseDocuments = [
            ['name' => 'Current passport and relevant pages of previous passports, including visas and travel stamps', 'status' => 'required'],
            ['name' => 'Completed application form and appointment or submission confirmation, where applicable', 'status' => 'required'],
            ['name' => 'Recent photograph meeting the destination and application-route specification', 'status' => 'required'],
            ['name' => 'Travel purpose evidence, itinerary and accommodation or host details', 'status' => 'conditional', 'notes' => 'Provide the documents requested for the visa category and actual travel plan.'],
            ['name' => 'Evidence of sufficient funds and source of funds', 'status' => 'conditional', 'notes' => 'The amount and acceptable evidence depend on the destination and applicant circumstances.'],
            ['name' => 'Evidence of intention to leave and ties to country of residence', 'status' => 'conditional', 'notes' => 'Examples include employment, study, family responsibilities or other lawful commitments.'],
            ['name' => 'Travel medical insurance', 'status' => 'conditional', 'notes' => 'Mandatory for some destinations/categories, including Schengen short-stay visas.'],
            ['name' => 'English translation with the original document', 'status' => 'conditional', 'notes' => 'Follow the destination authority’s translation and certification rules.'],
        ];

        $profiles = [
            'employee' => [
                ['name' => 'Employer letter confirming position, salary, employment start date and approved leave', 'status' => 'conditional'],
                ['name' => 'Recent salary slips and evidence of salary deposits', 'status' => 'conditional'],
                ['name' => 'Employee ID card', 'status' => 'if_applicable'],
                ['name' => 'Evidence of approved leave and expected return to work', 'status' => 'conditional'],
            ],
            'self_employed_or_business_owner' => [
                ['name' => 'Current trade licence or business registration', 'status' => 'conditional'],
                ['name' => 'Company bank statements and evidence of business activity', 'status' => 'conditional'],
                ['name' => 'Tax returns, tax certificates and business ownership evidence', 'status' => 'conditional'],
                ['name' => 'Business visiting card and letterhead', 'status' => 'if_applicable'],
                ['name' => 'Memorandum and articles of association for a limited company', 'status' => 'if_applicable'],
            ],
            'student' => [
                ['name' => 'Student ID card and current enrolment evidence', 'status' => 'conditional'],
                ['name' => 'School or university leave/NOC letter', 'status' => 'conditional'],
                ['name' => 'Sponsor letter and sponsor financial evidence, if sponsored', 'status' => 'conditional'],
                ['name' => 'Birth certificate and parental consent for a minor, where applicable', 'status' => 'if_applicable'],
            ],
            'retired' => [
                ['name' => 'Retirement or pension evidence', 'status' => 'conditional'],
                ['name' => 'Pension statements and evidence of other regular income', 'status' => 'conditional'],
                ['name' => 'Evidence of savings, assets and travel funding', 'status' => 'conditional'],
            ],
        ];

        foreach ($data as $iso2 => $details) {
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

            if (! empty($details['photo'])) {
                $requirement->forceFill([
                    'photo_specifications' => array_merge($details['photo'], ['checked_at' => '2026-11-14']),
                ])->save();

                VisaRequirementSource::updateOrCreate(
                    ['visa_requirement_id' => $requirement->id, 'source_url' => $details['photo']['source_url']],
                    [
                        'source_type' => 'official',
                        'source_name' => $details['photo']['source_name'],
                        'source_url' => $details['photo']['source_url'],
                        'source_last_checked_at' => $checkedAt,
                        'is_primary' => false,
                        'verification_notes' => 'Official photo guidance for the stated application route; route limitations are recorded in photo_specifications.',
                    ]
                );
            }

            $checklists = [];
            foreach ($profiles as $type => $documents) {
                $checklists[] = [
                    'applicant_type' => $type,
                    'documents' => array_merge($baseDocuments, $documents),
                    'source_name' => 'UniGo preparation guide based on the uploaded agency checklists; items are conditional unless the destination authority makes them mandatory.',
                    'source_url' => null,
                    'checked_at' => '2026-11-14',
                ];
            }

            $requirement->forceFill(['applicant_checklists' => $checklists])->save();
        }
    }
}
