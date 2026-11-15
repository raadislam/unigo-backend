<?php
require getcwd() . '/vendor/autoload.php';
$app = require getcwd() . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$expected = ['AT','BE','BG','HR','CZ','DK','EE','FI','FR','DE','GR','HU','IS','IT','LV','LI','LT','LU','MT','NL','NO','PL','PT','RO','SK','SI','ES','SE','CH'];
$records = App\Models\VisaRequirement::query()
    ->with('country:id,iso2')
    ->where('passport_nationality_iso3', 'BGD')
    ->where('travel_purpose', 'tourism')
    ->whereHas('country', fn ($query) => $query->whereIn('iso2', $expected))
    ->get()
    ->keyBy(fn ($record) => strtoupper((string) $record->country?->iso2));
if ($records->count() !== 29) {
    throw new RuntimeException('Expected 29 Schengen tourism records; found ' . $records->count());
}
foreach ($expected as $iso2) {
    $record = $records->get($iso2);
    if (! $record || $record->verification_status !== 'verified' || ! $record->is_published) {
        throw new RuntimeException('Missing or unpublished verified Schengen record: ' . $iso2);
    }
    $photo = $record->photo_specifications;
    $checklists = $record->applicant_checklists;
    if (! is_array($photo) || empty($photo['status']) || ! is_array($checklists) || count($checklists) !== 4) {
        throw new RuntimeException('Photo/checklist metadata missing or malformed: ' . $iso2);
    }
    foreach (['employee','self_employed_or_business_owner','student','retired'] as $type) {
        if (! collect($checklists)->contains(fn ($item) => ($item['applicant_type'] ?? null) === $type && ! empty($item['documents']))) {
            throw new RuntimeException('Applicant profile missing for ' . $iso2 . ': ' . $type);
        }
    }
    $allDocs = collect($checklists)->flatMap(fn ($item) => $item['documents'] ?? [])->pluck('name')->implode(' | ');
    if ($iso2 === 'IT') {
        $vfsChecklistUrl = 'https://visa.vfsglobal.com/one-pager/italy/bangladesh/english/pdf/TURISM_updated.pdf';
        $embassyChecklistUrl = 'https://ambdhaka.esteri.it/wp-content/uploads/2026/01/TOURISM_updated.pdf';
        if (($photo['status'] ?? null) !== 'verified' || ($photo['dimensions_mm'] ?? null) !== '40 × 35 mm' || ! str_contains(strtolower((string) ($photo['copies'] ?? '')), 'two') || ! str_contains(strtolower((string) ($photo['background'] ?? '')), 'white') || ! str_contains(strtolower((string) ($photo['photo_age'] ?? '')), '6 months')) {
            throw new RuntimeException('Italy VFS photo specification does not match the current Bangladesh tourism PDF.');
        }
        $requiredItalyFragments = [
            'who will pay travel and stay costs',
            'city corporation-issued trade licence',
            'rjsc-issued certificate of incorporation',
            'personal bank statement issued by any bank operating in bangladesh for the last 6 months',
            'fidejussione bancaria',
            'passenger registration number or reservation number',
            'accommodation for the entire period of stay in all schengen member states',
            'hospitalisation and repatriation for at least eur 30,000',
            'marriage certificate',
            'divorce certificate',
            'spouse death certificate',
            'original consent of both parents or custodian',
            'photocopies of both parents’ passports',
            'proof of financial means of both parents or legal guardian',
            'original birth certificate issued by the relevant authority',
            'competent bangladeshi courts proving legal-guardian status',
            'only one apostille for each submitted document is accepted',
            'valid bangladeshi residence permit',
        ];
        foreach ($checklists as $profile) {
            if (($profile['source_url'] ?? null) !== $vfsChecklistUrl) {
                throw new RuntimeException('Italy applicant profile is not linked to the VFS Bangladesh tourism PDF.');
            }
            $profileDocs = collect($profile['documents'] ?? [])->pluck('name')->map(fn ($name) => strtolower((string) $name))->implode(' | ');
            foreach ($requiredItalyFragments as $fragment) {
                if (! str_contains($profileDocs, strtolower($fragment))) {
                    throw new RuntimeException('Italy VFS checklist item missing from ' . ($profile['applicant_type'] ?? 'unknown') . ': ' . $fragment);
                }
            }
        }
        if (! $record->sources()->where('source_url', $vfsChecklistUrl)->where('source_type', 'provider')->exists()) {
            throw new RuntimeException('Italy VFS Bangladesh tourism PDF source record missing.');
        }
        if (! $record->sources()->where('source_url', $embassyChecklistUrl)->where('source_type', 'official')->exists()) {
            throw new RuntimeException('Italy Embassy Dhaka mirrored tourism checklist source record missing.');
        }
    }
    $countryChecklistAssertions = [
        'DE' => ['original personal bank statements for at least the last 6 months', 'memorandum and articles of association', 'annual tax-payment confirmation', 'nikah nama in bangla and english', 'all photocopies on a4 paper'],
        'DK' => ['applyvisa', 'printed receipt for online payment', 'vu2 online invitation form', 'real-estate ownership', 'not purchase a flight ticket before visa approval'],
        'NL' => ['unique code', '35 × 45 mm', 'caregiver evidence', 'passenger registration number', 'court permission for the minor to travel'],
        'SE' => ['form 119031', 'last 6 months', 'passenger registration number', 'company address, telephone and fax', 'valid at least 3 months beyond intended departure from schengen'],
        'HR' => ['last 3 months', 'last 3 years', 'salary certificates for the last 3 months', 'notarised guardian consent', 'apostille'],
    ];
    if (isset($countryChecklistAssertions[$iso2])) {
        foreach ($countryChecklistAssertions[$iso2] as $fragment) {
            if (! str_contains(strtolower($allDocs), strtolower($fragment))) {
                throw new RuntimeException('Country-specific VFS checklist item missing for ' . $iso2 . ': ' . $fragment);
            }
        }
    }
    if ($iso2 === 'DK' && ! $record->sources()->where('source_url', 'https://visa.vfsglobal.com/one-pager/denmark/Bangladesh/english/pdf/visiting_family_or_friends_tourism.pdf')->exists()) {
        throw new RuntimeException('Denmark VFS tourism PDF source record missing.');
    }
    if ($iso2 === 'SE' && ! $record->sources()->where('source_url', 'https://visa.vfsglobal.com/one-pager/sweden/bangladesh/english/pdf/Tourist-Visa-Checklist-1.pdf')->exists()) {
        throw new RuntimeException('Sweden VFS tourism PDF source record missing.');
    }
    if ($iso2 === 'NL' && ! $record->sources()->where('source_url', 'https://visa.vfsglobal.com/one-pager/netherlands/bangladesh/english/')->exists()) {
        throw new RuntimeException('Netherlands VFS portal source record missing.');
    }
    if ($iso2 === 'FR' && ! $record->sources()->where('source_url', 'https://visa.vfsglobal.com/bgd/en/fra')->exists()) {
        throw new RuntimeException('France VFS Bangladesh portal source record missing.');
    }
    if ($iso2 === 'HR' && ! str_contains(strtolower($allDocs), 'apostill')) {
        throw new RuntimeException('Croatia conditional apostille checklist item missing.');
    }
    if (in_array($iso2, ['LT','RO'], true) && ($photo['status'] ?? null) !== 'not_verified') {
        throw new RuntimeException('Unverified route was incorrectly marked as photo-verified: ' . $iso2);
    }
    if (in_array($iso2, ['MT','SK'], true) && str_contains(strtolower($allDocs), 'apostill')) {
        throw new RuntimeException('Italy apostille rule was incorrectly applied to represented destination: ' . $iso2);
    }
    if (! $record->sources()->where('source_url', 'https://www.eeas.europa.eu/bangladesh/travel-study_en')->exists()) {
        throw new RuntimeException('EEAS representation source missing for ' . $iso2);
    }
    if (! $record->sources()->where('source_url', 'https://www.consilium.europa.eu/en/policies/schengen-area/')->exists()) {
        throw new RuntimeException('Current Schengen membership source missing for ' . $iso2);
    }
    if ($iso2 === 'BG' && (($photo['captured_at_vac'] ?? false) !== true || ($photo['status'] ?? null) !== 'partially_specified')) {
        throw new RuntimeException('Bulgaria VFS photo capture metadata missing.');
    }
    if ($iso2 === 'GR' && ($photo['status'] ?? null) !== 'not_verified') {
        throw new RuntimeException('Greece photo specification must remain unverified until its live checklist is retrieved.');
    }
    if ($iso2 === 'BG' && ! $record->sources()->where('source_url', 'https://visa.vfsglobal.com/bgd/en/bgr/')->exists()) {
        throw new RuntimeException('Bulgaria VFS Bangladesh source missing.');
    }
    if ($iso2 === 'GR' && ! $record->sources()->where('source_url', 'https://visa.vfsglobal.com/bgd/en/grc/')->exists()) {
        throw new RuntimeException('Greece VFS Bangladesh source missing.');
    }
}
echo "SCHENGEN_CHECKLIST_READBACK_OK countries=29 profiles=116 apostille=country-specific".PHP_EOL;
