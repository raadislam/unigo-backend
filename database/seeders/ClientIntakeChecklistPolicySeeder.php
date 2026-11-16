<?php

namespace Database\\Seeders;

use App\\Models\\VisaRequirement;
use Illuminate\\Database\\Seeder;

class ClientIntakeChecklistPolicySeeder extends Seeder
{
    public function run(): void
    {
        $requirement = VisaRequirement::query()
            ->where('passport_nationality_iso3', 'BGD')
            ->where('travel_purpose', 'tourism')
            ->whereHas('country', fn ($query) => $query->where('iso2', 'IT'))
            ->first();

        if (! $requirement) {
            return;
        }

        $common = [
            ['name' => 'Original passport with the required validity and at least two blank, unmarked visa pages, plus a clear copy of the biodata page', 'status' => 'required'],
            ['name' => 'Two recent identical colour photographs, 40 × 35 mm, white background, taken within the last six months', 'status' => 'required'],
            ['name' => 'Personal bank statement for the last six months from a bank operating in Bangladesh', 'status' => 'required'],
        ];

        $occupationDocuments = [
            'employee' => [
                ['name' => 'Original employer letter in English on official company letterhead, signed, stamped and dated, confirming employment, position, salary, service duration, approved leave, travel funding and return to work', 'status' => 'required'],
            ],
            'self_employed_or_business_owner' => [
                ['name' => 'City Corporation-issued trade licence', 'status' => 'required'],
                ['name' => 'RJSC certificate of incorporation, where the business is incorporated', 'status' => 'required'],
                ['name' => 'Income tax return and TIN certificate', 'status' => 'required'],
                ['name' => 'Company bank statements for the last six months', 'status' => 'required'],
            ],
            'student' => [
                ['name' => 'Proof of enrolment at an educational institution in Bangladesh', 'status' => 'required'],
                ['name' => 'If travelling during the academic year, an institution leave certificate showing its full address, telephone number, approved absence and authorising person', 'status' => 'required'],
            ],
            'retired' => [],
        ];

        $profiles = $requirement->applicant_checklists ?? [];

        foreach ($profiles as &$profile) {
            if (! is_array($profile)) {
                continue;
            }

            $type = $profile['applicant_type'] ?? '';
            if (! array_key_exists($type, $occupationDocuments)) {
                continue;
            }

            $profile['client_documents'] = array_merge(
                $common,
                $occupationDocuments[$type]
            );
            $profile['checklist_purpose'] = 'documents_client_must_submit_to_unigo';
            $profile['agency_managed_items'] = [
                'Visa application form preparation',
                'Cover letter drafting',
                'Travel itinerary preparation',
                'Flight and accommodation reservations',
                'Travel medical insurance arrangement',
                'Visa-centre appointment and submission guidance',
            ];
            $profile['conditional_documents_notice'] = 'If your marital status, age, host arrangements or nationality require additional documents, UniGo will request them separately after reviewing your profile.';
        }
        unset($profile);

        $requirement->forceFill(['applicant_checklists' => $profiles])->save();
    }
}
