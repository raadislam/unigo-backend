<?php

namespace Database\Seeders;

use App\Models\VisaRequirement;
use App\Models\VisaRequirementSource;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SchengenBangladeshChecklistSupplementSeeder extends Seeder
{
    public function run(): void
    {
        DB::beginTransaction();
        try {
            $checkedAt = '2026-11-15 00:00:00';

        $groups = [
            'DE' => [
                'countries' => ['DE', 'EE'],
                'representation' => 'German Embassy Dhaka / VFS Germany Bangladesh',
                'source_name' => 'German Embassy Dhaka — Schengen tourism information sheet, January 2026',
                'source_url' => 'https://visa.vfsglobal.com/one-pager/germany/bangladesh/english/pdf/VFS_Requirements_for_Tourist_visa_.pdf',
                'source_type' => 'official',
                'photo' => [
                    'status' => 'verified',
                    'route' => 'Schengen tourism paper application through VFS Germany Bangladesh',
                    'dimensions_mm' => '35 × 45 mm',
                    'copies' => 'One recent biometric photograph',
                    'background' => 'White background',
                    'head_measurements' => 'Face covers 70–80% of the photograph',
                    'photo_age' => 'Recent; the information sheet does not state a maximum age',
                    'notes' => 'Do not glue or staple the photograph to the application form. Head coverings are accepted only for religious reasons. Estonia applications through the German Embassy are restricted to specified applicant groups under the Embassy notice dated 22 October 2026; ordinary applicants outside those groups must not rely on this route.',
                ],
                'documents' => [
                    ['name' => 'Declaration of Consent required by the German Embassy', 'status' => 'required'],
                    ['name' => 'Travel medical insurance valid for the entire requested period and all Schengen countries, with at least EUR 30,000 cover and repatriation', 'status' => 'required'],
                    ['name' => 'Flight reservation and hotel reservation with full address and booking details', 'status' => 'required'],
                    ['name' => 'Personal covering letter explaining the purpose of travel and who pays the costs', 'status' => 'required'],
                    ['name' => 'Tour plan covering the intended itinerary and activities in the Schengen area', 'status' => 'required'],
                    ['name' => 'Latest personal tax return certificate and annual tax payment confirmation', 'status' => 'required'],
                    ['name' => 'Civil-status certificates, such as marriage, divorce or death certificates, translated into English or German', 'status' => 'if_applicable'],
                    ['name' => 'For minors: birth/adoption/custody/death documents with English or German translation', 'status' => 'if_applicable'],
                    ['name' => 'For a guardian not attending the appointment: notarised legal-guardian consent and guardian passport copy', 'status' => 'if_applicable'],
                ],
                'notes' => 'The January 2026 information sheet is valid when Germany or Estonia is the main destination. The German Embassy notice of 22 October 2026 restricts Estonia applications to official passport holders/delegations, certain family members, bona fide travellers and holders of properly used prior visas.',
            ],
            'DK' => [
                'countries' => ['DK', 'NO'],
                'representation' => 'Royal Danish Embassy Dhaka / VFS Denmark Bangladesh; Denmark also represents Norway',
                'source_name' => 'VFS Global — Embassy of Denmark Bangladesh, Checklist for Visiting Family/Friends/Tourism',
                'source_url' => 'https://visa.vfsglobal.com/one-pager/denmark/bangladesh/english/pdf/Checklist-tourism.pdf',
                'source_type' => 'provider',
                'photo' => [
                    'status' => 'verified',
                    'route' => 'Schengen short-stay application through VFS Denmark Bangladesh',
                    'dimensions_mm' => '35 × 45 mm',
                    'copies' => 'One colour passport-size photograph',
                    'background' => 'Light / bright background',
                    'head_measurements' => 'Chin to top of hair: 30–36 mm',
                    'photo_age' => 'No older than 6 months',
                    'notes' => 'The tourism checklist specifies one colour photograph, full face and light background. VFS photo guidance confirms 35 × 45 mm and head length 30–36 mm.',
                    'source_name' => 'VFS Global — Denmark Bangladesh photo specifications',
                    'source_url' => 'https://visa.vfsglobal.com/one-pager/denmark/bangladesh/english/',
                ],
                'documents' => [
                    ['name' => 'Completed and signed Schengen visa application form', 'status' => 'required'],
                    ['name' => 'VU2 online invitation form or written invitation, when visiting family/friends', 'status' => 'if_applicable'],
                    ['name' => 'All previous passports and copies of biodata pages and previous Schengen visas', 'status' => 'required'],
                    ['name' => 'Cover letter addressed to the Embassy explaining the purpose of the visit', 'status' => 'required'],
                    ['name' => 'Employer/owner letter', 'status' => 'if_applicable'],
                    ['name' => 'Original personal bank statement for the last 6 months; company statement if self-employed', 'status' => 'required'],
                    ['name' => 'Evidence of establishment and ties in Bangladesh, such as financial documents, partnerships, property, marriage or children documents', 'status' => 'required'],
                    ['name' => 'Schengen travel medical insurance', 'status' => 'required'],
                    ['name' => 'Accommodation or hotel reservations for the destinations', 'status' => 'required'],
                    ['name' => 'Bookings for travel for the entire trip and a travel programme where appropriate', 'status' => 'required'],
                    ['name' => 'Host identity and residence permit copy plus proof of family relationship, when visiting a host', 'status' => 'if_applicable'],
                ],
                'notes' => 'The Embassy of Denmark represents Norway for Schengen visa cases; the VFS page states the same process and document requirements apply.',
            ],
            'NL' => [
                'countries' => ['NL', 'PL'],
                'representation' => 'Embassy of the Netherlands / VFS Netherlands Bangladesh; Netherlands represents Poland',
                'source_name' => 'NetherlandsWorldwide — Schengen tourism checklist for applicants in Bangladesh',
                'source_url' => 'https://www.netherlandsworldwide.nl/visa-the-netherlands/checklist-schengen-visa-tourism/bangladesh',
                'source_type' => 'official',
                'photo' => [
                    'status' => 'verified',
                    'route' => 'Schengen short-stay through VFS Netherlands Bangladesh',
                    'dimensions_mm' => '35 × 45 mm',
                    'copies' => 'A printed photograph is not required when the applicant has a live digital photograph taken at the VAC',
                    'background' => 'White or light-coloured background',
                    'photo_age' => 'No more than 6 months old',
                    'format' => 'Colour photograph',
                    'captured_at_vac' => true,
                    'notes' => 'NetherlandsWorldwide says a photo is required unless applying through the external service provider, where a digital photo is taken at the appointment. VFS also captures the applicant photograph. Follow the appointment-specific instruction.',
                    'source_name' => 'NetherlandsWorldwide — Schengen tourism checklist for Bangladesh',
                    'source_url' => 'https://www.netherlandsworldwide.nl/visa-the-netherlands/checklist-schengen-visa-tourism/bangladesh',
                ],
                'documents' => [
                    ['name' => 'Online completed and signed Schengen application form with unique code', 'status' => 'required'],
                    ['name' => 'Passport issued no more than 10 years ago, with at least 2 empty visa pages and 3 months validity after departure from Schengen', 'status' => 'required'],
                    ['name' => 'Proof of legal residence in Bangladesh, where the applicant is not a Bangladeshi national', 'status' => 'if_applicable'],
                    ['name' => 'Proof of social/economic ties in Bangladesh', 'status' => 'required'],
                    ['name' => 'Employment certificate showing name, job title, salary, service duration, employer contact details, leave approval and signatory details', 'status' => 'if_applicable'],
                    ['name' => 'Company trade licence, incorporation certificate, TIN and last 6 months company bank statements for business owners', 'status' => 'if_applicable'],
                    ['name' => 'Student enrolment evidence and leave certificate; study-trip letter if applicable', 'status' => 'if_applicable'],
                    ['name' => 'Civil-status evidence, such as marriage certificate, Talak Nama or spouse death certificate', 'status' => 'if_applicable'],
                    ['name' => 'Accommodation proof for the entire stay', 'status' => 'required'],
                    ['name' => 'Personal bank statements, credit-card statements or bank balance evidence for the last 6 months', 'status' => 'required'],
                    ['name' => 'Return travel reservation and travel plan; paid ticket is not required', 'status' => 'required'],
                    ['name' => 'Travel medical insurance covering all Schengen countries for the full stay and at least EUR 30,000', 'status' => 'required'],
                    ['name' => 'For minors travelling without one or both parents: birth certificate, parental consent and supporting parent/guardian documents', 'status' => 'if_applicable'],
                ],
                'notes' => 'The Netherlands represents Poland according to the EEAS Bangladesh representation list. Confirm that the Netherlands is the competent mission for the actual itinerary before applying. The Netherlands tourism checklist is country-specific; Poland representation does not mean that a Dutch visa decision is issued for every itinerary.',
            ],
            'SE' => [
                'countries' => ['SE', 'FI', 'IS', 'LV', 'LU', 'SI'],
                'representation' => 'Embassy of Sweden Dhaka / VFS Sweden Bangladesh; represents Finland, Iceland, Latvia, Luxembourg and Slovenia',
                'source_name' => 'VFS Global — Sweden Bangladesh Schengen visa information and checklist',
                'source_url' => 'https://visa.vfsglobal.com/one-pager/sweden/bangladesh/english/index.html',
                'source_type' => 'provider',
                'photo' => [
                    'status' => 'verified',
                    'route' => 'Schengen short-stay through VFS Sweden Bangladesh',
                    'dimensions_mm' => '35 × 45 mm',
                    'copies' => 'One photograph for all applicants, including minors',
                    'background' => 'Plain, evenly lit and light background',
                    'photo_age' => 'No older than 6 months',
                    'format' => 'Colour, full-face photograph showing true likeness',
                    'notes' => 'Head coverings are accepted for religious beliefs only and must not conceal any part of the face.',
                    'source_name' => 'VFS Global — Sweden Bangladesh photo specifications',
                    'source_url' => 'https://visa.vfsglobal.com/one-pager/sweden/bangladesh/english/index.html',
                ],
                'documents' => [
                    ['name' => 'Completed and signed Schengen visa application form', 'status' => 'required'],
                    ['name' => 'Valid passport and copies of current/previous passports and travel history', 'status' => 'required'],
                    ['name' => 'Cover letter explaining purpose, itinerary and funding', 'status' => 'required'],
                    ['name' => 'Proof of employment/business/study/retirement appropriate to applicant status', 'status' => 'if_applicable'],
                    ['name' => 'Proof of financial means and source of funds', 'status' => 'required'],
                    ['name' => 'Travel itinerary, return reservation and accommodation evidence', 'status' => 'required'],
                    ['name' => 'Travel medical insurance covering the entire Schengen area and intended stay', 'status' => 'required'],
                    ['name' => 'Civil-status documents and parental consent for minors, where applicable', 'status' => 'if_applicable'],
                    ['name' => 'Proof of Bangladesh residence status for non-Bangladeshi applicants', 'status' => 'if_applicable'],
                ],
                'notes' => 'Sweden represents Finland, Iceland, Latvia, Luxembourg and Slovenia for Schengen visas. Since 15 October 2026, decisions on applications submitted in Bangladesh are made by the Swedish Migration Agency; the application procedure remains via VFS.',
            ],
            'CH' => [
                'countries' => ['CH', 'HU', 'LI'],
                'representation' => 'Embassy of Switzerland Dhaka; checklist covers Switzerland, Liechtenstein and Hungary',
                'source_name' => 'Embassy of Switzerland in Bangladesh — Checklist for Schengen Visa, Tourism',
                'source_url' => 'https://www.eda.admin.ch/content/dam/countries/countries-content/bangladesh/en/visa-documents/checklist-schengen-tourism-visa_EN.pdf',
                'source_type' => 'official',
                'photo' => [
                    'status' => 'verified',
                    'route' => 'Schengen tourism application at the Embassy of Switzerland in Dhaka',
                    'dimensions_mm' => '35–40 mm in width',
                    'copies' => 'One passport-size picture',
                    'background' => 'White background',
                    'photo_age' => 'Not older than 6 months',
                    'notes' => 'The checklist refers applicants to ICAO photograph guidelines. It does not specify a fixed photo height in the Bangladesh checklist.',
                    'source_name' => 'Embassy of Switzerland in Bangladesh — Schengen tourism checklist',
                    'source_url' => 'https://www.eda.admin.ch/content/dam/countries/countries-content/bangladesh/en/visa-documents/checklist-schengen-tourism-visa_EN.pdf',
                ],
                'documents' => [
                    ['name' => 'Online Schengen application form, printed and signed; appointment required', 'status' => 'required'],
                    ['name' => 'Passport issued within 10 years, valid 3 months beyond planned stay, with at least 2 blank pages', 'status' => 'required'],
                    ['name' => 'Clear passport biodata copy, previous Schengen/US/UK visas and previous passports for the last 20 years, if any', 'status' => 'required'],
                    ['name' => 'Original cover letter with travel plan and self-introduction', 'status' => 'required'],
                    ['name' => 'Civil-status certificate: marriage, divorce, spouse death or unmarried-status certificate as applicable', 'status' => 'if_applicable'],
                    ['name' => 'Occupation evidence: employer NOC/work contract, business registration and tax documents, student enrolment/leave, or retirement evidence', 'status' => 'required'],
                    ['name' => 'Financial evidence: stamped bank statements and occupation-specific financial documents; last 6 months for personal/business accounts where specified', 'status' => 'required'],
                    ['name' => 'Detailed day-to-day itinerary', 'status' => 'required'],
                    ['name' => 'Accommodation reservations for Switzerland and other Schengen states', 'status' => 'required'],
                    ['name' => 'Round-trip flight reservation and intra-Schengen transport reservations where applicable', 'status' => 'required'],
                    ['name' => 'Travel medical insurance with minimum EUR 30,000 cover for the entire Schengen stay', 'status' => 'required'],
                    ['name' => 'For minors: both-parent signatures/consent, parents’ ID copies, birth certificate and minor identity card', 'status' => 'if_applicable'],
                    ['name' => 'English or Swiss national-language translations for documents not in an accepted language', 'status' => 'required'],
                ],
                'notes' => 'The Swiss Embassy Dhaka tourism checklist expressly covers Switzerland, Liechtenstein and Hungary. It requires civil-status evidence but does not state a general apostille requirement in the reviewed tourism checklist.',
            ],
            'IT' => [
                'countries' => ['IT', 'MT', 'SK'],
                'representation' => 'Embassy of Italy Dhaka / VFS Italy Bangladesh; Italy represents Malta and Slovakia for Schengen visas',
                'source_name' => 'VFS Global — Check-list for Tourism Visa, Bangladesh',
                'source_url' => 'https://visa.vfsglobal.com/one-pager/italy/bangladesh/english/pdf/TURISM_updated.pdf',
                'source_type' => 'provider',
                'photo' => [
                    'status' => 'verified',
                    'route' => 'Short-stay Schengen tourism application through VFS Italy Bangladesh',
                    'dimensions_mm' => '40 × 35 mm',
                    'copies' => 'Two recent and identical photographs',
                    'background' => 'White background',
                    'photo_age' => 'Taken within the last 6 months',
                    'format' => 'Colour, ICAO format',
                    'notes' => 'The VFS tourism checklist expressly requires two identical colour photographs, 4.0 × 3.5 cm, white background and no more than six months old.',
                    'source_name' => 'VFS Global — Italy Bangladesh tourism checklist',
                    'source_url' => 'https://visa.vfsglobal.com/one-pager/italy/bangladesh/english/pdf/TURISM_updated.pdf',
                ],
                'documents' => [
                    ['name' => 'Original completed and signed Schengen Type C application form; both parents/legal guardian sign for minors', 'status' => 'required'],
                    ['name' => 'Passport valid at least 3 months beyond visa validity and with at least 2 blank unmarked visa pages; biodata copy', 'status' => 'required'],
                    ['name' => 'For employees: original employer letter in English on official company letterhead, stamped, signed and dated; it must state the employer address, telephone and fax numbers, signatory name and position, applicant name, position, salary and years of service, who will pay travel and stay costs, approved leave, and confirmation that the applicant will retain the position after returning', 'status' => 'if_applicable'],
                    ['name' => 'For self-employed applicants: City Corporation-issued trade licence, RJSC-issued certificate of incorporation, income tax return, TIN certificate and company bank statements for the last 6 months', 'status' => 'if_applicable'],
                    ['name' => 'For students: proof of enrolment at an educational institution in Bangladesh; if travelling during the school year, leave certificate showing the institution’s full address, telephone number, permission of absence and name/function of the authorising person; for a school trip, school letter stating purpose, duration and destination', 'status' => 'if_applicable'],
                    ['name' => 'Personal bank statement issued by any bank operating in Bangladesh for the last 6 months, plus other proof of financial solvency such as credit-card statements; if own funds are insufficient, an Italian-bank-issued bank guarantee (Fidejussione Bancaria) is required', 'status' => 'required'],
                    ['name' => 'Return airline booking showing the Passenger Registration Number or Reservation Number, together with the travel itinerary', 'status' => 'required'],
                    ['name' => 'Hotel confirmation or other documentary proof of accommodation for the entire period of stay in all Schengen Member States', 'status' => 'required'],
                    ['name' => 'Travel insurance valid throughout the entire Schengen area and full stay, covering medical expenses, hospitalisation and repatriation for at least EUR 30,000', 'status' => 'required'],
                                        ['name' => 'For visits to family/friends residing in Italy: invitation letter in the VFS-prescribed format, proof of family relationship/ties, host identity document, and host residence-permit copy if the host is a foreign national', 'status' => 'if_applicable'],
                ],
                'notes' => 'The Bangladesh VFS tourism PDF has 12 numbered items. It specifies two identical ICAO colour photographs, 40 × 35 mm, white background, taken within six months; occupation-specific employee, self-employed and student evidence; six-month personal bank statements; return booking with passenger-registration/reservation number and itinerary; accommodation for the full Schengen stay; travel insurance covering the whole Schengen area for at least EUR 30,000; English-translated and apostilled civil-status documents for married, divorced and widowed applicants; apostilled parental consent, minor birth certificate and court-issued guardianship evidence in the listed minor cases; and a valid Bangladesh residence permit for non-Bangladeshi applicants. Only one apostille per submitted document is accepted. The VFS English passport-validity wording says three months beyond visa validity, while the Bengali wording says three months from application date; applicants should confirm this discrepancy with VFS/Embassy before relying on either interpretation. The Italian Embassy Dhaka separately states that from June 2025 Bangladeshi public documents require an MOFA apostille through MyGov rather than Embassy legalisation. Apostille remains conditional on applicant/document circumstances, not a universal item for every applicant.',
            ],
            'HR' => [
                'countries' => ['HR'],
                'representation' => 'VFS Croatia Bangladesh / competent Croatian mission',
                'source_name' => 'VFS Global — Croatia Bangladesh tourist visa information',
                'source_url' => 'https://visa.vfsglobal.com/one-pager/croatia/bangladesh/english/',
                'source_type' => 'provider',
                'photo' => [
                    'status' => 'verified',
                    'route' => 'Croatian short-stay tourist visa through VFS Croatia Bangladesh',
                    'dimensions_mm' => '35 × 45 mm',
                    'copies' => 'Two photographs',
                    'background' => 'Light or white, plain background with sufficient contrast',
                    'head_measurements' => 'Head occupies about two-thirds of the photo and must not exceed 36 mm',
                    'photo_age' => 'The current VFS page does not state a maximum age',
                    'notes' => 'Neutral facial expression, eyes open, mouth closed, head centred, high-quality glossy/smooth paper. Do not infer a maximum photo age where the source does not state one.',
                    'source_name' => 'VFS Global — Croatia Bangladesh photo specifications',
                    'source_url' => 'https://visa.vfsglobal.com/one-pager/croatia/bangladesh/english/',
                ],
                'documents' => [
                    ['name' => 'Completed and signed Croatian Schengen visa application form', 'status' => 'required'],
                    ['name' => 'Passport issued within the last 10 years, valid at least 3 months beyond requested visa validity and with at least 2 empty pages; copies of current and previous passport pages/visas', 'status' => 'required'],
                    ['name' => 'Two colour photographs, 35 × 45 mm, meeting Croatia photo specifications', 'status' => 'required'],
                    ['name' => 'Travel health insurance covering repatriation, urgent medical assistance and hospitalisation with minimum EUR 30,000 for the entire stay', 'status' => 'required'],
                    ['name' => 'Flight and accommodation reservations only; confirmed bookings are not required', 'status' => 'required'],
                    ['name' => 'Proof of sufficient funds, including original bank statements for the last 3 months, signed and stamped by the bank', 'status' => 'required'],
                    ['name' => 'Additional supporting documents and certified translations requested by the Croatian Embassy', 'status' => 'conditional'],
                    ['name' => 'Apostille / international certification of supporting public documents if requested by the Croatian Embassy', 'status' => 'conditional'],
                    ['name' => 'For minors travelling without a parent/legal guardian: notarised guardian consent containing identity, purpose and travel period details', 'status' => 'if_applicable'],
                    ['name' => 'Reason letter explaining non-use of a previous Croatian visa or refusal by another country, if applicable', 'status' => 'if_applicable'],
                ],
                'notes' => 'The Croatian Ministry confirms that Bangladeshi applicants can submit at the VFS centre in Dhaka and that the Embassy in New Delhi is competent for Bangladesh. The VFS one-pager contains some generic India-oriented wording, so use the Bangladesh VFS portal and confirm the live appointment instructions. The Embassy may request additional documents, certified translations and apostille certification. Flight and hotel reservations are sufficient; confirmed bookings are not required. Apostille is conditional, not an automatic universal tourist requirement.',
            ],
            'BE' => [
                'countries' => ['BE'],
                'representation' => 'VFS Belgium Bangladesh; VFS directs applicants to Belgian Embassy New Delhi for the document list',
                'source_name' => 'VFS Global — Belgium Bangladesh short-stay visa information',
                'source_url' => 'https://visa.vfsglobal.com/one-pager/belgium/bangladesh/english/index.html',
                'source_type' => 'provider',
                'photo' => [
                    'status' => 'verified',
                    'route' => 'Belgium short-stay application through VFS Belgium Bangladesh',
                    'dimensions_mm' => '35–45 mm in width',
                    'copies' => 'Two photographs for short-stay non-VIS; three for short-stay VIS cases, according to the VFS page',
                    'background' => 'Plain white background',
                    'head_measurements' => 'Face occupies 70–80% of the photograph',
                    'photo_age' => 'No more than 6 months old',
                    'notes' => 'VFS also captures a live photograph at the application centre. The supporting-document link points to the Belgian Embassy in New Delhi; do not import India-specific apostille/legalisation instructions into the Bangladesh checklist.',
                    'source_name' => 'VFS Global — Belgium Bangladesh photo specifications',
                    'source_url' => 'https://visa.vfsglobal.com/one-pager/belgium/bangladesh/english/index.html',
                ],
                'documents' => [
                    ['name' => 'Completed and signed Schengen application form and VFS appointment confirmation', 'status' => 'required'],
                    ['name' => 'Passport with at least 2 blank pages and at least 3 months validity after leaving Schengen', 'status' => 'required'],
                    ['name' => 'Travel medical insurance covering all Schengen countries and the full stay, minimum EUR 30,000', 'status' => 'required'],
                    ['name' => 'Evidence of purpose, travel plan, accommodation, funds and intention to return', 'status' => 'required'],
                    ['name' => 'Evidence of family background, employment/business, socio-economic status and property/ties in Bangladesh', 'status' => 'required'],
                    ['name' => 'Country-specific supporting document list from the competent Belgian mission; VFS Bangladesh currently points to the Embassy of Belgium in New Delhi', 'status' => 'required'],
                ],
                'notes' => 'VFS Belgium Bangladesh publishes photo, insurance, biometric and return-ties guidance but refers applicants to the Embassy of Belgium in New Delhi for the category-specific supporting document list. The VFS page contains an apostille paragraph referring to India’s Ministry of External Affairs; it must not be treated as a Bangladesh apostille instruction.',
            ],
            'BG' => [
                'countries' => ['BG'],
                'representation' => 'VFS Global Bangladesh is the official partner of the Embassy of Bulgaria in Bangladesh',
                'source_name' => 'VFS Global — Bulgaria Visa Application Centre Bangladesh',
                'source_url' => 'https://visa.vfsglobal.com/bgd/en/bgr/',
                'source_type' => 'provider',
                'photo' => [
                    'status' => 'partially_specified',
                    'route' => 'Bulgaria short-stay application through VFS Global Bangladesh',
                    'captured_at_vac' => true,
                    'copies' => 'VFS states that a digital photograph is captured at the appointment. The accessible Bangladesh portal does not state the printed-photo size or count.',
                    'notes' => 'The VFS Bangladesh centre page confirms a digital photograph is captured during biometric submission. Exact printed-photo dimensions and copy count were not available in the accessible Bangladesh portal text. Confirm the current Tourism category checklist before attending.',
                    'source_name' => 'VFS Global — Bulgaria Bangladesh, what happens at the centre',
                    'source_url' => 'https://visa.vfsglobal.com/bgd/en/bgr/attend-centre/what-happens-at-centre',
                ],
                'documents' => [],
                'notes' => 'VFS Global confirms it is the official partner of the Embassy of Bulgaria in Bangladesh. The Bangladesh portal dynamically serves category-specific documents; its full tourism checklist was not extractable in this review. The displayed checklist therefore contains the common EU Visa Code baseline and applicant-profile evidence only. Confirm the current Tourism category list with VFS before submission. No Bangladesh-specific apostille rule was verified.',
            ],
            'GR' => [
                'countries' => ['GR'],
                'representation' => 'VFS Global is the official partner of the Embassy of Greece in Bangladesh',
                'source_name' => 'VFS Global — Greece Visa Application Centre Bangladesh',
                'source_url' => 'https://visa.vfsglobal.com/bgd/en/grc/',
                'source_type' => 'provider',
                'photo' => [
                    'status' => 'not_verified',
                    'route' => 'Greece short-stay application through VFS Global Bangladesh',
                    'notes' => 'The VFS Bangladesh portal confirms the official application channel, but the accessible page did not expose the current tourism photo specifications or whether a printed photograph is required. Confirm the live Tourism category checklist before submission.',
                    'source_name' => 'VFS Global — Greece Bangladesh visa information',
                    'source_url' => 'https://visa.vfsglobal.com/bgd/en/grc/',
                ],
                'documents' => [],
                'notes' => 'VFS Global confirms it is the official partner of the Embassy of Greece in Bangladesh. The accessible Bangladesh portal is dynamically served and the current Tourism checklist could not be retrieved in this review. The displayed checklist contains the common EU Visa Code baseline and applicant-profile evidence only; confirm the current category-specific list with VFS before submission. No Bangladesh-specific apostille rule was verified.',
            ],
            'FR' => [
                'countries' => ['FR', 'AT', 'CZ', 'PT'],
                'representation' => 'Embassy of France / VFS France Bangladesh; France represents Austria, Czechia and Portugal for short-stay Schengen visas',
                'source_name' => 'France-Visas — Bangladesh application route and VFS France Bangladesh',
                'source_url' => 'https://france-visas.gouv.fr/en/bangladesh',
                'source_type' => 'official',
                'photo' => [
                    'status' => 'partially_specified',
                    'route' => 'France-Visas online application with personalised document checklist and VFS France Bangladesh appointment',
                    'notes' => 'The Bangladesh VFS static page directs applicants to France-Visas and does not publish a complete local photo-size/copy specification in the reviewed page. Follow the photo requirements generated by France-Visas for the application. Do not substitute another Schengen country’s photo dimensions.',
                    'source_name' => 'France-Visas — Bangladesh',
                    'source_url' => 'https://france-visas.gouv.fr/en/bangladesh',
                ],
                'documents' => [
                    ['name' => 'Completed France-Visas application and personalised supporting-document checklist', 'status' => 'required'],
                    ['name' => 'Passport and supporting documents in the order stated on the personalised checklist', 'status' => 'required'],
                    ['name' => 'Appointment confirmation and in-person submission at VFS France Bangladesh, where required', 'status' => 'required'],
                    ['name' => 'Documents in German, Portuguese or Czech accompanied by French or English translations for represented-country applications', 'status' => 'if_applicable'],
                    ['name' => 'Purpose, itinerary, accommodation, financial means, occupation and travel medical insurance evidence as required by the personalised checklist', 'status' => 'required'],
                ],
                'notes' => 'France represents Austria, Portugal and Czechia for short-stay Schengen visas in Bangladesh. France-Visas generates the applicant-specific checklist. The French Embassy states that documents in German, Portuguese or Czech for represented countries must be translated into French or English.',
            ],
            'ES' => [
                'countries' => ['ES'],
                'representation' => 'Embassy of Spain Dhaka / BLS Spain Bangladesh; not VFS',
                'source_name' => 'Embassy of Spain in Dhaka — Schengen visas',
                'source_url' => 'https://www.exteriores.gob.es/Embajadas/dhaka/en/ServiciosConsulares/Paginas/Consular/Visados-Schengen.aspx',
                'source_type' => 'official',
                'photo' => [
                    'status' => 'partially_specified',
                    'route' => 'Schengen tourism application through BLS Spain Bangladesh',
                    'copies' => 'A recent passport-size colour photograph',
                    'background' => 'Light background',
                    'notes' => 'The Embassy page specifies a recent, passport-size, colour photograph on a light background but does not state exact dimensions or a maximum age in the reviewed guidance. Confirm the exact size/count on the current BLS checklist.',
                    'source_name' => 'Embassy of Spain in Dhaka — Schengen visas',
                    'source_url' => 'https://www.exteriores.gob.es/Embajadas/dhaka/en/ServiciosConsulares/Paginas/Consular/Visados-Schengen.aspx',
                ],
                'documents' => [
                    ['name' => 'Completed and signed Schengen visa application form', 'status' => 'required'],
                    ['name' => 'Recent passport-size colour photograph on a light background; follow current BLS specifications for exact dimensions and count', 'status' => 'required'],
                    ['name' => 'Valid passport and photocopy of biometric data pages, with at least 2 blank pages and 3 months validity beyond planned departure', 'status' => 'required'],
                    ['name' => 'Travel medical insurance covering the Schengen area', 'status' => 'required'],
                    ['name' => 'Purpose-of-travel evidence, itinerary, accommodation, funds and return-travel evidence as required by the Embassy/BLS checklist', 'status' => 'required'],
                    ['name' => 'Occupation, financial, family and socio-economic tie evidence relevant to the applicant', 'status' => 'required'],
                ],
                'notes' => 'Spain applications in Bangladesh are handled through BLS, not VFS. Do not label the BLS checklist as a VFS checklist.',
            ],
        ];

        $groupChecklistSupplements = [
            'DE' => [
                ['name' => 'Original personal bank statements for at least the last 6 months and evidence of other assets such as DPS, FDR or holding-tax receipts; do not substitute property purchase/sale deeds', 'status' => 'required'],
                ['name' => 'Latest personal tax-return certificate with annual tax-payment confirmation and, where applicable, acknowledgement showing the amount of income tax paid', 'status' => 'required'],
                ['name' => 'For employees: employer letter showing company name, postal address and telephone, applicant position, employment start date, salary, leave period, expected date of resuming duties and travel purpose', 'status' => 'if_applicable'],
                ['name' => 'For self-employed applicants: City Corporation or Union Parishad trade licence and RJSC certificate of incorporation with the Memorandum and Articles of Association pages naming the directors', 'status' => 'if_applicable'],
                ['name' => 'For self-employed applicants: original company bank statements for the previous 6 months, including the overdraft limit where applicable', 'status' => 'if_applicable'],
                ['name' => 'For students: Bangladesh educational-enrolment proof and leave-of-absence certificate showing institution address, telephone, permission, and authoriser name/function', 'status' => 'if_applicable'],
                ['name' => 'Civil-status documents where applicable: marriage certificate/Nikah Nama in Bangla and English, divorce/Talak Nama, spouse death certificate and children birth certificates; translate documents into English or German', 'status' => 'if_applicable'],
                ['name' => 'For minors: birth/adoption/custody/death evidence with English or German translation; where the legal guardian is absent from the appointment, notarised guardian consent and guardian passport copy', 'status' => 'if_applicable'],
                ['name' => 'For non-Bangladeshi applicants: valid Bangladesh residence permit/visa meeting the German Embassy validity requirement', 'status' => 'if_applicable'],
                ['name' => 'Copies of previous Schengen, United States, Canada or United Kingdom visas, where applicable', 'status' => 'if_applicable'],
                ['name' => 'All photocopies on A4 paper; documents not in English or German accompanied by English or German translation', 'status' => 'required'],
                ['name' => 'Credit-card travel insurance is not accepted by the German Embassy; submit a separate qualifying policy', 'status' => 'required'],
            ],
            'DK' => [
                ['name' => 'Printed and signed Schengen Visa Application Cover Letter generated through ApplyVisa', 'status' => 'required'],
                ['name' => 'Printed receipt for online payment of the visa fee', 'status' => 'required'],
                ['name' => 'Completed and signed VU2 online invitation form or personal explanatory invitation letter, when relevant', 'status' => 'if_applicable'],
                ['name' => 'All previous passports, including photocopies of biodata pages and previous Schengen, United Kingdom, Australian or United States visas', 'status' => 'required'],
                ['name' => 'Original personal bank statement for the last 6 months; self-employed applicants also submit the company statement, with original and photocopy', 'status' => 'required'],
                ['name' => 'Evidence of establishment and ties in Bangladesh, such as financial documents, business partnerships, real-estate ownership, marriage certificate or children birth certificates', 'status' => 'required'],
                ['name' => 'Original and photocopy of Schengen health and travel insurance valid for the duration of the stay', 'status' => 'required'],
                ['name' => 'Accommodation or hotel reservations for all destinations and travel reservations for the entire journey, including flight, train or boat where relevant', 'status' => 'required'],
                ['name' => 'Travel programme or draft tour programme where appropriate', 'status' => 'if_applicable'],
                ['name' => 'For visits to family or friends: evidence of family/other ties with the host and a copy of the host identity document, passport, ID card or residence card', 'status' => 'if_applicable'],
                ['name' => 'Do not purchase a flight ticket before visa approval; the Danish mission recommends reservations only', 'status' => 'conditional'],
            ],
            'NL' => [
                ['name' => 'Online completed and signed Schengen application form with its unique code; handwritten forms are not accepted', 'status' => 'required'],
                ['name' => 'Original travel document for inspection and colour photocopies of all submitted supporting documents; passport must be no older than 10 years and have at least 2 empty visa pages', 'status' => 'required'],
                ['name' => 'Dutch-compliant colour photograph, 35 × 45 mm, white or light background, no older than 6 months; printed photograph is not required when the live photograph is taken at the VAC', 'status' => 'required'],
                ['name' => 'Proof of legal residence in Bangladesh or the country where the application is lodged; residence permit must remain valid for at least 3 months after leaving Schengen', 'status' => 'required'],
                ['name' => 'Evidence of social/economic ties in country of residence, such as employer/company introduction, business purpose and duration, financial cover, company profile, trade licence or Memorandum of Association', 'status' => 'required'],
                ['name' => 'Additional ties where relevant: caregiver evidence, home or other immovable property evidence, and evidence that children attend school in the country of residence', 'status' => 'if_applicable'],
                ['name' => 'For self-employed applicants: City Corporation trade licence, RJSC incorporation certificate where applicable, income-tax return and company bank statement for 6 months', 'status' => 'if_applicable'],
                ['name' => 'For students: educational-enrolment proof, leave certificate with institution address, telephone, permission and authoriser details; school-trip letter stating purpose, duration and destination', 'status' => 'if_applicable'],
                ['name' => 'Civil-status document applicable to the applicant: marriage certificate, divorce/Talak Nama or spouse death certificate', 'status' => 'if_applicable'],
                ['name' => 'Proof of financial means, including personal bank statements, credit-card statements or balance evidence covering the last 6 months and other solvency evidence', 'status' => 'required'],
                ['name' => 'Round-trip airline reservation with Passenger Registration Number or reservation number, travel itinerary and onward-destination entry permits where relevant', 'status' => 'required'],
                ['name' => 'Travel medical insurance in the applicant name covering all Schengen countries for the full stay and at least EUR 30,000, including hospital treatment and repatriation', 'status' => 'required'],
                ['name' => 'For a minor travelling without one or both parents/guardians: recent birth-certificate extract, consent signed by non-travelling parent/guardian, their valid travel-document copy and proof of their financial means', 'status' => 'if_applicable'],
                ['name' => 'For a minor: where a parent/guardian has died, death-certificate copy; where a guardian is appointed, official guardianship proof; where a court order exists, court permission for the minor to travel', 'status' => 'if_applicable'],
                ['name' => 'Original hotel confirmation or voucher for the whole stay, or other accommodation evidence', 'status' => 'required'],
            ],
            'SE' => [
                ['name' => 'Completed, signed and dated Swedish Schengen application form 119031; all supporting documents must be printed on A4 paper', 'status' => 'required'],
                ['name' => 'All previous passports and colour copies of the recent passport biodata page and all Schengen visas', 'status' => 'required'],
                ['name' => 'Applicant cover letter addressed to the Embassy of Sweden explaining the detailed purpose of visit', 'status' => 'required'],
                ['name' => 'Personal bank statements, credit-card statements or balance covering the last 6 months, together with other proof of financial solvency', 'status' => 'required'],
                ['name' => 'Round-trip airline reservation showing Passenger Registration Number or reservation number, travel itinerary and onward-destination entry permits where relevant', 'status' => 'required'],
                ['name' => 'For visits to family or friends: invitation letter, proof of sponsorship and evidence of family/ties with the sponsor', 'status' => 'if_applicable'],
                ['name' => 'Hotel confirmation or voucher for the whole stay, or other accommodation evidence', 'status' => 'required'],
                ['name' => 'For employees: employer certificate showing company address, telephone and fax, countersigning officer name/position, applicant name/position, salary and years of service', 'status' => 'if_applicable'],
                ['name' => 'For self-employed applicants: City Corporation trade licence, RJSC incorporation certificate where applicable, TIN certificate and company bank statement for 6 months', 'status' => 'if_applicable'],
                ['name' => 'For students: Bangladesh enrolment proof and leave certificate with institution address, telephone, permission and authoriser name/function; school-trip letter with purpose, duration and destination where relevant', 'status' => 'if_applicable'],
                ['name' => 'For applicants travelling with spouse and/or children: marriage certificate issued by a responsible authority', 'status' => 'if_applicable'],
                ['name' => 'For minors not travelling with both parents: parental/custodian consent, parent or guardian passport copy, parent/guardian financial evidence and original birth certificate', 'status' => 'if_applicable'],
                ['name' => 'For non-Bangladeshi applicants: Bangladesh residence permit valid at least 3 months beyond intended departure from Schengen, onward-journey reservation and evidence of activity on return to Bangladesh', 'status' => 'if_applicable'],
            ],
            'HR' => [
                ['name' => 'For sufficient funds: original bank statements for savings/current accounts covering the last 3 months, signed and stamped by an authorised bank official', 'status' => 'required'],
                ['name' => 'Financial support evidence where relevant: personal/company income-tax returns for the last 3 years, international credit-card copy and 3-month statement, and passport foreign-exchange endorsement or purchase receipt', 'status' => 'if_applicable'],
                ['name' => 'For employees: employer letter on official letterhead confirming position, service duration and approved leave, plus salary certificates for the last 3 months', 'status' => 'if_applicable'],
                ['name' => 'For business/self-employed applicants: visa request letter on official company letterhead with company registration papers', 'status' => 'if_applicable'],
                ['name' => 'For students: school/university leave approval and valid student identity-card copy', 'status' => 'if_applicable'],
                ['name' => 'For pensioners or scholarship recipients: pension slip or proof of scholarship/income, where relevant', 'status' => 'if_applicable'],
                ['name' => 'Accommodation evidence may include hotel booking, private accommodation, property title/lease or boat ownership/lease; guarantor accommodation may be used when stated in the guarantee/invitation', 'status' => 'required'],
                ['name' => 'For minors/persons lacking legal capacity: birth certificate and decision of the competent authority appointing the legal guardian; notarised guardian consent with identity, purpose and travel dates when travelling without the guardian', 'status' => 'if_applicable'],
                ['name' => 'Only flight-ticket and hotel reservations are required; confirmed bookings are not required', 'status' => 'required'],
                ['name' => 'Reason letter explaining non-use of a previous Croatian visa or a previous refusal by another country, where applicable', 'status' => 'if_applicable'],
            ],
        ];

        foreach ($groupChecklistSupplements as $groupCode => $supplementDocuments) {
            $groups[$groupCode]['documents'] = array_merge($groups[$groupCode]['documents'] ?? [], $supplementDocuments);
        }

        $groups['DK']['source_url'] = 'https://visa.vfsglobal.com/one-pager/denmark/Bangladesh/english/pdf/visiting_family_or_friends_tourism.pdf';
        $groups['DK']['source_name'] = 'VFS Global — Embassy of Denmark Bangladesh, Visiting Family/Friends/Tourism checklist';
        $groups['SE']['source_url'] = 'https://visa.vfsglobal.com/one-pager/sweden/bangladesh/english/pdf/Tourist-Visa-Checklist-1.pdf';
        $groups['SE']['source_name'] = 'VFS Global — Sweden Bangladesh Tourist Visa checklist';

        $groups['DK']['extra_sources'] = [
            ['source_name' => 'VFS Global — Denmark Bangladesh visa information and Norway representation', 'source_url' => 'https://visa.vfsglobal.com/one-pager/denmark/Bangladesh/english/', 'source_type' => 'provider', 'verification_notes' => 'Confirms the Bangladesh application route, Denmark representation of Norway and tourism checklist link.'],
        ];
        $groups['SE']['extra_sources'] = [
            ['source_name' => 'VFS Global — Sweden Bangladesh visa information and represented countries', 'source_url' => 'https://visa.vfsglobal.com/one-pager/sweden/bangladesh/english/index.html', 'source_type' => 'provider', 'verification_notes' => 'Confirms the Bangladesh application route and representation of Finland, Iceland, Latvia, Luxembourg and Slovenia; links the tourism checklist.'],
        ];
        $groups['DE']['extra_sources'] = [
            ['source_name' => 'German Embassy Dhaka — Estonia application restrictions, 22 October 2026', 'source_url' => 'https://dhaka.diplo.de/bd-en/service/2682922-2682922', 'source_type' => 'official', 'verification_notes' => 'Estonia applications through the German Embassy are restricted to specified applicant groups; do not imply ordinary unrestricted access.'],
        ];
        $groups['HR']['extra_sources'] = [
            ['source_name' => 'Croatian Ministry of Foreign Affairs — Bangladesh applicants and competent mission', 'source_url' => 'https://mvep.gov.hr/services-consular-portal/consular-information-22802/visas-22807/visa-requirements-overview-22879/22879?country=159', 'source_type' => 'official', 'verification_notes' => 'Confirms the competent Croatian mission and Bangladesh submission route; VFS page includes tourist checklist and conditional apostille/translation language.'],
        ];

        $groups['NL']['extra_sources'] = [
            ['source_name' => 'VFS Global — Netherlands Bangladesh visa information and tourism checklist link', 'source_url' => 'https://visa.vfsglobal.com/one-pager/netherlands/bangladesh/english/', 'source_type' => 'provider', 'verification_notes' => 'VFS Bangladesh directs tourism applicants to the official NetherlandsWorldwide Bangladesh tourism checklist and confirms live photograph/biometric capture at the VAC.'],
        ];
        $groups['FR']['extra_sources'] = [
            ['source_name' => 'VFS Global — France Bangladesh visa information', 'source_url' => 'https://visa.vfsglobal.com/bgd/en/fra', 'source_type' => 'provider', 'verification_notes' => 'Confirms the Bangladesh application channel; France-Visas generates the personalised Type C supporting-document checklist for France and represented Austria, Czechia and Portugal.'],
        ];
        $unverified = [
            'LT' => 'No Bangladesh-specific VFS or competent-mission tourism checklist was located in this review. Do not infer photo dimensions or apostille requirements. Check the competent Lithuanian mission before advising applicants.',
            'RO' => 'No Bangladesh-specific VFS or competent-mission tourism checklist was located in this review. Do not infer photo dimensions or apostille requirements. Check the competent Romanian mission before advising applicants.',
        ];

        $baseDocuments = [
            ['name' => 'Completed and signed short-stay Schengen application form', 'status' => 'required'],
            ['name' => 'Valid passport issued within the previous 10 years, with at least 2 blank visa pages and validity extending at least 3 months beyond planned departure from Schengen', 'status' => 'required'],
            ['name' => 'Recent photograph meeting the country-specific photo specification or live-photo-at-VAC instruction', 'status' => 'required'],
            ['name' => 'Cover letter explaining the purpose, itinerary and funding of the trip', 'status' => 'conditional'],
            ['name' => 'Return/onward travel reservation and itinerary for the full trip', 'status' => 'required'],
            ['name' => 'Accommodation evidence for the full stay or invitation/host evidence where applicable', 'status' => 'required'],
            ['name' => 'Proof of sufficient financial means and source of funds', 'status' => 'required'],
            ['name' => 'Travel medical insurance for all Schengen countries and the full intended stay, with minimum EUR 30,000 cover', 'status' => 'required'],
            ['name' => 'Proof of legal residence in Bangladesh for applicants who are not Bangladeshi nationals', 'status' => 'if_applicable'],
            ['name' => 'Previous passports, Schengen visas and relevant travel history', 'status' => 'if_applicable'],
            ['name' => 'Civil-status evidence and parental consent/birth documents for minors where applicable', 'status' => 'if_applicable'],
        ];

        $profiles = [
            'employee' => [
                ['name' => 'Employer certificate confirming position, salary, length of service, leave approval and expected return to work', 'status' => 'if_applicable'],
                ['name' => 'Recent salary slips and personal salary bank statements', 'status' => 'if_applicable'],
                ['name' => 'Employee identity card or work contract', 'status' => 'if_applicable'],
            ],
            'self_employed_or_business_owner' => [
                ['name' => 'Trade licence/business registration and certificate of incorporation, where applicable', 'status' => 'if_applicable'],
                ['name' => 'Company bank statements, personal bank statements and evidence of business activity', 'status' => 'if_applicable'],
                ['name' => 'TIN, income tax return and Memorandum and Articles of Association, where applicable', 'status' => 'if_applicable'],
            ],
            'student' => [
                ['name' => 'Current enrolment/student status evidence and student identity card', 'status' => 'if_applicable'],
                ['name' => 'School/college/university leave approval and sponsor financial evidence where applicable', 'status' => 'if_applicable'],
            ],
            'retired' => [
                ['name' => 'Retirement/pension certificate and pension or regular income statements', 'status' => 'if_applicable'],
            ],
        ];

        $sourceDirectory = 'https://www.eeas.europa.eu/bangladesh/travel-study_en';
        $visaCodeSource = 'https://eur-lex.europa.eu/eli/reg/2009/810/oj/eng';
        $schengenMembershipSource = 'https://www.consilium.europa.eu/en/policies/schengen-area/';

        foreach ($groups as $group) {
            foreach ($group['countries'] as $iso2) {
                $data[$iso2] = $group;
            }
        }

        foreach (array_merge(array_keys($data), array_keys($unverified)) as $iso2) {
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

            if (isset($unverified[$iso2])) {
                $photo = [
                    'status' => 'not_verified',
                    'route' => 'Bangladesh short-stay Schengen tourism application route not verified in this review',
                    'notes' => $unverified[$iso2],
                    'source_name' => 'EEAS Bangladesh — Schengen visa issuing missions and representation information',
                    'source_url' => $sourceDirectory,
                    'checked_at' => '2026-11-15',
                ];
                $documents = [];
                $sourceName = 'UniGo research status — Bangladesh-specific checklist not verified';
                $sourceUrl = $sourceDirectory;
                $sourceType = 'official';
                $notes = $unverified[$iso2];
            } else {
                $details = $data[$iso2];
                $photo = array_merge($details['photo'], ['representation' => $details['representation'], 'checked_at' => '2026-11-15']);
                $documents = array_merge($baseDocuments, $details['documents'] ?? []);
                $sourceName = $details['source_name'];
                $sourceUrl = $details['source_url'];
                $sourceType = $details['source_type'];
                $notes = $details['representation'] . '. ' . $details['notes'];
            }

            $countryBaseDocuments = $baseDocuments;
            if (($photo['status'] ?? '') === 'not_verified') {
                $countryBaseDocuments[2]['status'] = 'conditional';
                $countryBaseDocuments[2]['notes'] = 'The Bangladesh-route photo requirement and specifications have not been verified in the reviewed mission/provider sources. Confirm the current competent-mission checklist before submission.';
            }

            if (in_array($iso2, ['MT', 'SK'], true)) {
                $notes .= ' The Italy VFS apostille rule has not been independently confirmed for this represented destination; do not assume it applies without checking the competent mission.';
            }

            $requirement->forceFill([
                'photo_specifications' => $photo,
                'required_documents' => collect($requirement->required_documents ?? [])
                    ->when($iso2 === 'NL', fn ($items) => $items->merge(['Live digital photograph captured at the VFS appointment; a printed photo is not required when this live-photo route is used']))
                    ->when($iso2 === 'BG', fn ($items) => $items->merge(['Digital photograph captured at the VFS Bangladesh appointment; the accessible portal does not state printed-photo size/count, so verify the live Tourism checklist']))
                    ->when(in_array($photo['status'] ?? '', ['verified', 'partially_specified'], true) && ! in_array($iso2, ['NL', 'BG'], true), fn ($items) => $items->merge(['Recent colour photograph meeting the destination-specific Schengen photo rules or live-photo capture instruction']))
                    ->when($iso2 === 'IT', fn ($items) => $items->merge(['Apostilled English translations of civil-status documents and specified minor-related documents, only where applicable under the Italy VFS checklist']))
                    ->when($iso2 === 'HR', fn ($items) => $items->merge(['Apostille/international certification of supporting public documents if specifically requested by the Croatian Embassy']))
                    ->unique()
                    ->values()
                    ->all(),
                'applicant_checklists' => collect($profiles)->map(function ($profileDocs, $type) use ($countryBaseDocuments, $documents, $sourceName, $sourceUrl, $notes, $iso2) {
                    $checklistBaseDocuments = $countryBaseDocuments;

                    if ($iso2 === 'IT') {
                        $repeatedItalyBaseItems = [
                            'Valid passport issued within the previous 10 years, with at least 2 blank visa pages and validity extending at least 3 months beyond planned departure from Schengen',
                            'Recent photograph meeting the country-specific photo specification or live-photo-at-VAC instruction',
                            'Return/onward travel reservation and itinerary for the full trip',
                            'Accommodation evidence for the full stay or invitation/host evidence where applicable',
                            'Proof of sufficient financial means and source of funds',
                            'Travel medical insurance for all Schengen countries and the full intended stay, with minimum EUR 30,000 cover',
                        ];

                        $checklistBaseDocuments = collect($checklistBaseDocuments)
                            ->reject(fn ($item) => in_array($item['name'], $repeatedItalyBaseItems, true))
                            ->values()
                            ->all();
                    }

                    $combined = collect($checklistBaseDocuments)
                        ->merge($profileDocs)
                        ->merge($documents)
                        ->unique(fn ($item) => $item['name'])
                        ->values()
                        ->all();

                    if ($iso2 === 'IT') {
                        $combined = array_merge($combined, [
                            ['name' => 'For married applicants: marriage certificate, including Muslim Nikah Nama or Hindu marriage act, translated into English and apostilled; only one apostille for each submitted document is accepted', 'status' => 'if_applicable'],
                            ['name' => 'For divorced applicants: divorce certificate, including Muslim Talaq Nama, translated into English and apostilled; only one apostille for each submitted document is accepted', 'status' => 'if_applicable'],
                            ['name' => 'For widowed applicants: spouse death certificate translated into English and apostilled; only one apostille for each submitted document is accepted', 'status' => 'if_applicable'],
                            ['name' => 'For minors not travelling with both parents: original consent of both parents or custodian, apostilled', 'status' => 'if_applicable'],
                            ['name' => 'For minors: photocopies of both parents’ passports or the legal guardian’s passport', 'status' => 'if_applicable'],
                            ['name' => 'For minors: proof of financial means of both parents or legal guardian', 'status' => 'if_applicable'],
                            ['name' => 'For minors: original birth certificate issued by the relevant authority, apostilled', 'status' => 'if_applicable'],
                            ['name' => 'For minors under legal guardianship: original documents issued by the competent Bangladeshi courts proving legal-guardian status, apostilled', 'status' => 'if_applicable'],
                            ['name' => 'For minors: only one apostille for each submitted document is accepted', 'status' => 'if_applicable'],
                            ['name' => 'For non-Bangladeshi applicants: valid Bangladeshi residence permit in addition to all other checklist documents', 'status' => 'if_applicable'],
                            ['name' => 'The Embassy may request supplementary documents not listed in the VFS tourism checklist', 'status' => 'conditional'],
                        ]);
                    }

                    if ($iso2 === 'HR') {
                        $combined[] = ['name' => 'Apostille/international certification of supporting public documents if the Croatian Embassy requests it', 'status' => 'conditional'];
                    }

                    return [
                        'applicant_type' => $type,
                        'documents' => $combined,
                        'source_name' => $sourceName,
                        'source_url' => $sourceUrl,
                        'checked_at' => '2026-11-15',
                        'notes' => $notes,
                    ];
                })->values()->all(),
            ])->save();

            VisaRequirementSource::updateOrCreate(
                ['visa_requirement_id' => $requirement->id, 'source_url' => $sourceUrl],
                [
                    'source_type' => $sourceType,
                    'source_name' => $sourceName,
                    'source_url' => $sourceUrl,
                    'source_last_checked_at' => $checkedAt,
                    'is_primary' => false,
                    'verification_notes' => $notes,
                ]
            );

            foreach (($details['extra_sources'] ?? []) as $extraSource) {
                VisaRequirementSource::updateOrCreate(
                    ['visa_requirement_id' => $requirement->id, 'source_url' => $extraSource['source_url']],
                    [
                        'source_type' => $extraSource['source_type'],
                        'source_name' => $extraSource['source_name'],
                        'source_url' => $extraSource['source_url'],
                        'source_last_checked_at' => $checkedAt,
                        'is_primary' => false,
                        'verification_notes' => $extraSource['verification_notes'],
                    ]
                );
            }

            VisaRequirementSource::updateOrCreate(
                ['visa_requirement_id' => $requirement->id, 'source_url' => $sourceDirectory],
                [
                    'source_type' => 'official',
                    'source_name' => 'European External Action Service — Schengen visa issuing embassies and representation in Bangladesh',
                    'source_url' => $sourceDirectory,
                    'source_last_checked_at' => $checkedAt,
                    'is_primary' => false,
                    'verification_notes' => 'Used only as a starting point for legacy Bangladesh representation arrangements. This EEAS page still lists 26 Schengen states and is outdated for membership: Croatia, Bulgaria and Romania must be treated using current EU Council membership and each country’s own mission guidance. It is not a substitute for a destination-specific supporting-document checklist.',
                ]
            );

            VisaRequirementSource::updateOrCreate(
                ['visa_requirement_id' => $requirement->id, 'source_url' => $visaCodeSource],
                [
                    'source_type' => 'official',
                    'source_name' => 'European Union — Visa Code, Regulation (EC) No 810/2009',
                    'source_url' => $visaCodeSource,
                    'source_last_checked_at' => $checkedAt,
                    'is_primary' => false,
                    'verification_notes' => 'General common framework for Schengen visa applications and supporting documents. It does not replace a destination-specific Bangladesh checklist or a mission request for additional documents.',
                ]
            );

            VisaRequirementSource::updateOrCreate(
                ['visa_requirement_id' => $requirement->id, 'source_url' => $schengenMembershipSource],
                [
                    'source_type' => 'official',
                    'source_name' => 'Council of the European Union — Current 29-country Schengen membership',
                    'source_url' => $schengenMembershipSource,
                    'source_last_checked_at' => $checkedAt,
                    'is_primary' => false,
                    'verification_notes' => 'Authoritative current membership source. Confirms 29 countries and Bulgaria/Romania full membership from 1 January 2025; used instead of the outdated EEAS Bangladesh page for membership.',
                ]
            );

            if ($iso2 === 'IT') {
                $vfsChecklistUrl = 'https://visa.vfsglobal.com/one-pager/italy/bangladesh/english/pdf/TURISM_updated.pdf';
                $embassyChecklistUrl = 'https://ambdhaka.esteri.it/wp-content/uploads/2026/01/TOURISM_updated.pdf';
                VisaRequirementSource::updateOrCreate(
                    ['visa_requirement_id' => $requirement->id, 'source_url' => $embassyChecklistUrl],
                    [
                        'source_type' => 'official',
                        'source_name' => 'Embassy of Italy in Dhaka — Check-list for Tourism Visa',
                        'source_url' => $embassyChecklistUrl,
                        'source_last_checked_at' => $checkedAt,
                        'is_primary' => false,
                        'verification_notes' => 'Official Embassy-hosted copy of the Bangladesh tourism checklist. Cross-checked against the VFS Global Bangladesh PDF; both expose the same 12 numbered requirements, including civil-status apostilles, minor-specific apostilles and the residence-permit requirement for non-Bangladeshi applicants.',
                    ]
                );
                VisaRequirementSource::updateOrCreate(
                    ['visa_requirement_id' => $requirement->id, 'source_url' => 'https://visa.vfsglobal.com/one-pager/italy/bangladesh/english/index.html'],
                    [
                        'source_type' => 'provider',
                        'source_name' => 'VFS Global — Italy Bangladesh Visa Information',
                        'source_url' => 'https://visa.vfsglobal.com/one-pager/italy/bangladesh/english/index.html',
                        'source_last_checked_at' => $checkedAt,
                        'is_primary' => false,
                        'verification_notes' => 'VFS Bangladesh tourism category page identifies the Italy tourism route, links to the downloadable checklist and states appointment/submission guidance. The downloadable checklist PDF is the document-level checklist source.',
                    ]
                );
                $apostilleUrl = 'https://ambdhaka.esteri.it/en/servizi-consolari-e-visti/';
                VisaRequirementSource::updateOrCreate(
                    ['visa_requirement_id' => $requirement->id, 'source_url' => $apostilleUrl],
                    [
                        'source_type' => 'official',
                        'source_name' => 'Embassy of Italy in Dhaka — Apostille of Bangladeshi public documents',
                        'source_url' => $apostilleUrl,
                        'source_last_checked_at' => $checkedAt,
                        'is_primary' => false,
                        'verification_notes' => 'The Embassy states that from June 2025 Bangladeshi public documents no longer require legalisation at the Italian Consular Office but require an apostille from the Bangladesh Ministry of Foreign Affairs through MyGov. Apply this to relevant public documents, not as a blanket requirement for every applicant.',
                    ]
                );
            }

            if ($iso2 === 'HR') {
                VisaRequirementSource::updateOrCreate(
                    ['visa_requirement_id' => $requirement->id, 'source_url' => 'https://mvep.gov.hr/services-consular-portal/consular-information-22802/visas-22807/visa-requirements-overview-22879/22879?country=159'],
                    [
                        'source_type' => 'official',
                        'source_name' => 'Croatian Ministry of Foreign Affairs — Visa requirements for Bangladesh',
                        'source_url' => 'https://mvep.gov.hr/services-consular-portal/consular-information-22802/visas-22807/visa-requirements-overview-22879/22879?country=159',
                        'source_last_checked_at' => $checkedAt,
                        'is_primary' => false,
                        'verification_notes' => 'Confirms Bangladeshi ordinary passport holders require a visa and may submit at the VFS application centre in Dhaka; the Croatian Embassy in New Delhi is the competent mission.',
                    ]
                );
            }

            if ($iso2 === 'EE') {
                VisaRequirementSource::updateOrCreate(
                    ['visa_requirement_id' => $requirement->id, 'source_url' => 'https://dhaka.diplo.de/bd-en/service/2682922-2682922'],
                    [
                        'source_type' => 'official',
                        'source_name' => 'German Embassy Dhaka — Estonia application restrictions, 22 October 2026',
                        'source_url' => 'https://dhaka.diplo.de/bd-en/service/2682922-2682922',
                        'source_last_checked_at' => $checkedAt,
                        'is_primary' => false,
                        'verification_notes' => 'The German Embassy limits Estonia applications through Dhaka to specified applicant groups. Applicants outside those groups must verify the competent route before booking.',
                    ]
                );
            }

            if (in_array($iso2, ['FR', 'AT', 'CZ', 'PT'], true)) {
                VisaRequirementSource::updateOrCreate(
                    ['visa_requirement_id' => $requirement->id, 'source_url' => 'https://visa.vfsglobal.com/bgd/en/fra'],
                    [
                        'source_type' => 'provider',
                        'source_name' => 'VFS Global — France Visa Application Centre Bangladesh',
                        'source_url' => 'https://visa.vfsglobal.com/bgd/en/fra',
                        'source_last_checked_at' => $checkedAt,
                        'is_primary' => false,
                        'verification_notes' => 'VFS Global is the exclusive administrative service provider for the Embassy of France in Bangladesh. The France-Visas portal generates the applicant-specific checklist.',
                    ]
                );
            }

            if (in_array($iso2, ['SE', 'FI', 'IS', 'LV', 'LU', 'SI'], true)) {
                VisaRequirementSource::updateOrCreate(
                    ['visa_requirement_id' => $requirement->id, 'source_url' => 'https://www.swedenabroad.se/en/about-sweden-non-swedish-citizens/bangladesh/going-to-sweden/visiting-sweden/how-to-apply/'],
                    [
                        'source_type' => 'official',
                        'source_name' => 'Sweden Abroad — Schengen applications and representation in Bangladesh',
                        'source_url' => 'https://www.swedenabroad.se/en/about-sweden-non-swedish-citizens/bangladesh/going-to-sweden/visiting-sweden/how-to-apply/',
                        'source_last_checked_at' => $checkedAt,
                        'is_primary' => false,
                        'verification_notes' => 'Confirms VFS Dhaka submission and Swedish representation for Finland, Iceland, Latvia, Luxembourg and Slovenia; decisions have been made by the Swedish Migration Agency since 15 October 2026.',
                    ]
                );
            }

            if ($iso2 === 'ES') {
                VisaRequirementSource::updateOrCreate(
                    ['visa_requirement_id' => $requirement->id, 'source_url' => 'https://bgd.blsspainvisa.com/'],
                    [
                        'source_type' => 'provider',
                        'source_name' => 'BLS International — Spain Visa Application Centre Bangladesh',
                        'source_url' => 'https://bgd.blsspainvisa.com/',
                        'source_last_checked_at' => $checkedAt,
                        'is_primary' => false,
                        'verification_notes' => 'Spain applications in Bangladesh are handled by BLS International, not VFS Global.',
                    ]
                );
            }
        }
            DB::commit();
        } catch (\Throwable $exception) {
            DB::rollBack();
            throw $exception;
        }
    }
}
