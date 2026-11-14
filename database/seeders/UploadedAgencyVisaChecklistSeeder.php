<?php

namespace Database\Seeders;

use App\Models\VisaRequirement;
use Illuminate\Database\Seeder;

class UploadedAgencyVisaChecklistSeeder extends Seeder
{
    public function run(): void
    {
        $source = 'Uploaded UniGo reference: Merged CANADA INVT. and more.pdf and Thailand,Malaysia & Sing.pdf. These are agency preparation checklists, not embassy/VFS mandates. Confirm each item against the current destination authority checklist.';
        $items = [
            'CA' => [
                'common' => [
                    'All pages of current passport and all previous passports, including blank pages where requested',
                    'Birth certificate and National ID card',
                    'Marriage certificate, if married',
                    'Trade licence covering the last 3 years, if self-employed or a business owner',
                    'Employer letter, NOC, salary certificate and recent payslips, if employed',
                    'TIN certificate',
                    'Personal and company income-tax certificates/returns for the last 3 years, where applicable',
                    'Credit-card limit evidence, credit-card copy and sanction letter, if applicable',
                    'Personal and business/current account bank statements for the last 6 months, with bank solvency certificate',
                    'Evidence of property or other significant assets, such as land, flat or vehicle',
                    'Asset valuation report or asset declaration, where available',
                    'Membership certificate of a business association, if applicable',
                    'Passport copies and identifying information for spouse, children and relatives, including non-travelling family members, where relevant',
                    'Family and relatives information',
                    'Proposed hotel and flight itinerary or bookings, where requested',
                    'Invitation letter, if visiting a host',
                ],
                'sponsor' => [
                    'Inviter passport biographical page',
                    'Inviter status or citizenship documents in Canada',
                    'Invitation letter',
                    'House or accommodation agreement',
                    'Inviter tax returns and tax documents',
                    'Inviter bank statements and bank solvency certificate',
                    'Inviter driving licence, if available',
                ],
                'student' => ['Student passport and photograph', 'Birth certificate', 'Student ID card', 'School NOC', 'Program invitation and related program documents'],
            ],
            'NZ' => [
                'common' => [
                    'All pages of current and previous passports',
                    'Birth certificate and National ID card',
                    'Marriage certificate, if married',
                    'Trade licence covering the last 5 years, if self-employed or a business owner',
                    'Employer letter, NOC, salary certificate and payslips, if employed',
                    'TIN certificate',
                    'Income-tax certificates with acknowledgement receipt or tax-return submission receipt',
                    'Credit-card limit evidence, card copy and sanction letter/certificate/statement, if applicable',
                    'Personal and business/current account statements for the last 6 months, with bank solvency certificate',
                    'Evidence of property or other significant assets',
                    'Asset valuation report or asset declaration',
                    'Business association membership certificate, if applicable',
                    'Passport copies and identifying information for spouse, children and relevant relatives, including non-travelling family members',
                    'Family and relatives information',
                    'Proposed hotel and flight itinerary or bookings, where requested',
                    'Invitation letter, if visiting a host',
                ],
            ],
            'DE' => [
                'common' => [
                    'All pages of current passport and expired passports',
                    'Birth certificate and National ID card',
                    'Marriage certificate, if married',
                    'Trade licence covering the last 3 years, if self-employed or a business owner',
                    'Employer letter, NOC, salary certificate and payslips, if employed',
                    'TIN certificate',
                    'Personal and company income-tax certificates/returns for the last 3 years, where applicable',
                    'Credit-card limit evidence, credit-card copy and sanction letter, if applicable',
                    'Personal and business/current account statements for the last 6 months, with bank solvency certificate',
                    'Evidence of property or other significant assets',
                    'Asset valuation report or asset declaration',
                    'Business association membership certificate, if applicable',
                    'Passport copies and identifying information for spouse, children and relevant relatives, including non-travelling family members',
                    'Family and relatives information',
                    'Proposed hotel and flight itinerary or bookings, where requested',
                    'Invitation letter, if visiting a host',
                    'Travel medical insurance',
                ],
                'sponsor' => [
                    'Host passport biographical page',
                    'Host citizenship or residence-status documents',
                    'Invitation letter',
                    'Accommodation or house agreement',
                    'Host tax returns and tax documents',
                    'Host bank statements and solvency certificate',
                    'Host driving licence, if available',
                ],
            ],
            'GB' => [
                'common' => [
                    'All pages of current passport and expired passports',
                    'Birth certificate and National ID card',
                    'Marriage certificate, if married',
                    'Trade licence covering the last 3 years, if self-employed or a business owner',
                    'Employer letter, NOC, salary certificate and payslips, if employed',
                    'TIN certificate',
                    'Personal and company income-tax certificates/returns for the last 3 years, where applicable',
                    'Credit-card limit evidence, credit-card copy and sanction letter, if applicable',
                    'Personal and business/current account statements for the last 6 months, with bank solvency certificate',
                    'Evidence of property or other significant assets',
                    'Asset valuation report or asset declaration',
                    'Business association membership certificate, if applicable',
                    'Passport copies and identifying information for spouse, children and relevant relatives, including non-travelling family members',
                    'Family and relatives information',
                    'Proposed hotel and flight itinerary or bookings, where requested',
                    'Invitation letter, if visiting a host',
                ],
                'student' => [
                    'Current passport and all pages of expired passports',
                    'Recent photograph',
                    'Birth certificate',
                    'Student ID card',
                    'School NOC',
                    'Program invitation and supporting program documents',
                ],
                'sponsor' => [
                    'Sponsor passport biographical page',
                    'For a business sponsor: trade licence (last 5 years), visiting card, business letterhead, TIN, tax returns, bank statements, solvency certificate and National ID',
                    'For an employed sponsor: employer certificate, visiting card, company ID card, TIN, tax returns, bank statements, solvency certificate and National ID',
                ],
                'host' => [
                    'Host passport biographical page',
                    'Host citizenship or residence-status documents',
                    'Invitation letter',
                    'Accommodation or house agreement',
                    'Host tax returns and tax documents',
                    'Host bank statements and solvency certificate',
                    'Host driving licence, if available',
                ],
            ],
            'AU' => [
                'common' => [
                    'All pages of current and previous passports',
                    'Birth certificate and National ID card',
                    'Marriage certificate, if married',
                    'Trade licence covering the last 3 years, if self-employed or a business owner',
                    'Employer letter, NOC, salary certificate and payslips, if employed',
                    'TIN certificate',
                    'Income-tax certificates with acknowledgement receipt or tax-return submission receipt',
                    'Credit-card limit evidence, credit-card copy and sanction letter/certificate/statement, if applicable',
                    'Personal and business/current account statements for the last 6 months, with bank solvency certificate',
                    'Evidence of property or other significant assets',
                    'Asset valuation report or asset declaration',
                    'Business association membership certificate, if applicable',
                    'Passport copies and identifying information for spouse, children and relevant relatives, including non-travelling family members',
                    'Family and relatives information',
                    'Proposed hotel and flight itinerary or bookings, where requested',
                    'Invitation letter, if visiting a host',
                ],
                'host' => [
                    'Host passport biographical page',
                    'Host citizenship or residence-status documents',
                    'Invitation letter',
                    'Accommodation or house agreement',
                    'Host tax returns and tax documents',
                    'Host bank statements and solvency certificate',
                    'Host driving licence, if available',
                ],
            ],
            'TH' => [
                'common' => [
                    'Valid passport and old passport',
                    'Personal bank statement covering 7 months',
                    'Bank solvency certificate',
                    'Business visiting card, if applicable',
                    'TIN certificate',
                    'Recent photograph, 35 × 45 mm as stated in the uploaded agency checklist',
                    'National ID card',
                ],
                'business' => [
                    'Trade licence with English notarisation',
                    'Business visiting card',
                    'Business letterhead or pad',
                    'Memorandum of association for a limited company',
                ],
                'employee' => [
                    'No-objection certificate',
                    'Employee ID card',
                    'Salary certificate',
                    'Government order or office order for official-passport holders, where applicable',
                ],
                'student' => ['Student ID card', 'School/college NOC or forwarding letter'],
            ],
            'MY' => [
                'common' => [
                    'Valid passport and old passport',
                    'Personal bank statement covering 7 months',
                    'Bank solvency certificate',
                    'Business visiting card, if applicable',
                    'TIN certificate',
                    'Recent photograph, 35 × 45 mm as stated in the uploaded agency checklist',
                    'National ID card',
                ],
                'business' => [
                    'Trade licence with English notarisation',
                    'Business visiting card',
                    'Business letterhead or pad',
                    'Memorandum of association for a limited company',
                ],
                'employee' => [
                    'No-objection certificate',
                    'Employee ID card',
                    'Salary certificate',
                    'Government order or office order for official-passport holders, where applicable',
                ],
                'student' => ['Student ID card', 'School/college NOC or forwarding letter'],
            ],
            'SG' => [
                'common' => [
                    'Valid passport and old passport',
                    'Personal bank statement covering 7 months',
                    'Bank solvency certificate',
                    'Business visiting card, if applicable',
                    'TIN certificate',
                    'Recent photograph, 35 × 45 mm as stated in the uploaded agency checklist',
                    'National ID card',
                ],
                'business' => [
                    'Trade licence with English notarisation',
                    'Business visiting card',
                    'Business letterhead or pad',
                    'Memorandum of association for a limited company',
                ],
                'employee' => [
                    'No-objection certificate',
                    'Employee ID card',
                    'Salary certificate',
                    'Government order or office order for official-passport holders, where applicable',
                ],
                'student' => ['Student ID card', 'School/college NOC or forwarding letter'],
            ],
            'KR' => [
                'common' => [
                    'Valid passport',
                    'Personal bank statement for the last 6 months',
                    'Bank solvency certificate',
                    'TIN certificate',
                    'Income-tax certificates for the last 5 years',
                    'Income-tax acknowledgement or tax-return receipt for the last 3 years',
                    'Two colour photographs, 3.5 × 4.5 cm, with white background',
                    'Details/evidence of the applicant’s latest educational qualification',
                ],
                'business' => [
                    'Trade licence',
                    'Business visiting card',
                    'Business letterhead',
                    'Business bank statement for the last 6 months',
                    'Business bank solvency certificate',
                ],
                'employee' => [
                    'Employer NOC',
                    'Company ID card',
                    'Business or employee visiting card',
                    'Salary-account bank statement for the last 6 months',
                    'Salary-account bank solvency certificate',
                ],
                'family' => [
                    'Marriage certificate',
                    'Wife’s National ID card',
                    'Child’s birth certificate',
                    'Child’s student card',
                    'School NOC for a child, where applicable',
                ],
            ],
            'JP' => [
                'common' => [
                    'Valid passport',
                    'Personal bank statement for the last 6 months',
                    'Bank solvency certificate',
                    'TIN certificate',
                    'Income-tax certificates for the last 3 years',
                    'Income-tax acknowledgement or tax-return receipt for the last 3 years',
                    'Two photographs, 2 × 1.4 inches, with white background',
                    'Uploaded agency checklist states a solvency benchmark of BDT 10 lakh for a single applicant or BDT 15 lakh for a family application; this is not presented as an official embassy minimum and must be confirmed before use',
                ],
                'business' => [
                    'Trade licence',
                    'Business visiting card',
                    'Business letterhead',
                    'Business bank statement for the last 6 months',
                    'Business bank solvency certificate',
                ],
                'employee' => [
                    'Employer NOC',
                    'Company ID card',
                    'Employee visiting card',
                    'Salary-account bank statement for the last 6 months',
                    'Salary-account bank solvency certificate',
                ],
                'family' => [
                    'Marriage certificate',
                    'Wife’s National ID card',
                    'Child’s birth certificate',
                    'Child’s student card',
                    'School NOC for a child, where applicable',
                ],
            ],
        ];

        foreach ($items as $iso2 => $groups) {
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

            $checklists = [];
            foreach ($groups as $type => $documents) {
                $checklists[] = [
                    'applicant_type' => $type,
                    'documents' => array_map(fn ($name) => [
                        'name' => $name,
                        'status' => 'conditional',
                        'notes' => 'Listed in the uploaded agency preparation checklist. Confirm whether it is required for the applicant and current application route.',
                    ], $documents),
                    'source_name' => $source,
                    'source_url' => null,
                    'checked_at' => '2026-11-14',
                ];
            }

            $existing = collect($requirement->applicant_checklists ?? []);
            foreach ($checklists as $checklist) {
                $existing = $existing->reject(fn ($item) => ($item['applicant_type'] ?? null) === $checklist['applicant_type']);
                $existing->push($checklist);
            }

            $requirement->forceFill(['applicant_checklists' => $existing->values()->all()])->save();
        }
    }
}
