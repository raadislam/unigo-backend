# Batch 17 — Africa visa discovery and research

**Checked:** 14 November 2026  
**Passport:** Bangladesh ordinary passport  
**Purpose:** Tourism / short visitor stay

## Live-audit boundary

The latest available successful production audit is from the 14 November 2026 backend deployment at 05:37 UTC. The backend repository has no dedicated read-only audit workflow. The only workflow that runs `php artisan visa:requirements-audit` is the production deployment workflow, which also synchronizes files, refreshes application caches, imports fee/research files, seeds records, and imports/publishes earlier batches. It was not triggered for this research run because no fresh backup/restore test was available and a production deployment was not necessary for research-only changes.

Last confirmed production state: 250 master destinations; 249 country rows; 41 verified/published Bangladesh tourism requirements; 0 pending production requirements; public directory 41. This is the last confirmed state, not a fresh same-run database read.

## Run result

- Ten previously unstarted African destinations were progressed.
- Six Bangladesh ordinary-passport tourism requirement records are supported by current official source material and staged as research-verified, ready for controlled import.
- Two records are staged as pending review: Côte d’Ivoire because ONECI and SNEDAI publish conflicting tourist routes, fees, entry counts and stays; Cameroon because two official sources conflict on tourist visa fee and stay/validity.
- Gabon and Madagascar are recorded as research-in-progress without requirement rows because the reviewed official portals did not establish the current Bangladesh ordinary-passport exemption/eligibility position.
- All ten destination country rows already exist in the UniGo master table. No country-table records were added.
- The eight staged rows are research-file classifications only. They have not been imported, read back, verified or published in production.

## Six research-verified records

| Destination | Bangladesh ordinary-passport route | Confirmed details |
|---|---|---|
| Angola | Prior tourism visa; eVisa or diplomatic mission | Bangladesh is absent from the 98-country exemption list under Presidential Decree 189/23. Embassy guidance says non-listed nationalities need a tourism visa, decision within 3 working days. Fee is not published on the reviewed page and must be confirmed with the competent mission. |
| Benin | Tourist eVisa | Official FAQ says all foreigners may apply and Africans are visa-free for 90 days. Bangladesh is not in the African exemption group. eVisa options: EUR50 single 30-day, EUR75 multiple 30-day, EUR100 multiple 90-day. Processing 1–96 hours; apply at least 7 days and no more than 90 days before entry. |
| Botswana | Prior tourist visa/eVisa | Botswana Tourism Organisation explicitly lists Bangladesh among Commonwealth nationalities requiring a visa. Government tourist visa fee: BWP300 single entry up to 1 month; BWP500 multiple entry up to 3 months. Processing estimate 7–14 days. Passport validity at least 6 months; two photos, cover letter, flight itinerary, accommodation booking, trip itinerary, re-entry permit if required, and intent to depart. |
| The Gambia | Short-visit visa plus local security clearance | Gambia Immigration lists Bangladesh among countries needing both visa and clearance. The 2025 Bangladesh–Gambia visa exemption agreement is for diplomatic and official passports only, not ordinary passports. Short visits up to 90 days may be granted subject to clearance; applicant must be a regular resident where applying and show return/onward travel and adequate funds. Fee and processing time are not published. |
| Mozambique | Prior visa from nearest embassy; online eVisa unavailable to Bangladesh | The official National Immigration Service portal expressly lists Bangladesh among nationalities not allowed to apply for a visa through the eVisa portal and directs them to request a visa at the nearest embassy. Bangladesh-specific embassy fee, stay, checklist and processing time are not published on the reviewed portal. |
| Nigeria | F5A tourist eVisa | NIS F5A is a single-entry tourist visa for non-ECOWAS and non-waiver nationals. Bangladesh is listed in the April 2026 NIS fee schedule at USD253 single-entry. Validity 90 days from issuance; stay 30 days; not extendable. Processing target 48 hours if there is no local objection. Documents include 6-month passport, photo, return ticket, hotel/host address and 180-day bank statement. |

## Pending Côte d’Ivoire record\n\nThe ONECI official portal lists Bangladesh among nationalities eligible for tourist visa on arrival and currently describes a single-entry tourist eVisa with 30- or 90-day options, FCFA60,000 or FCFA110,000 fees and a normal three-day processing time. SNEDAI's official procedure instead describes prior pre-enrolment, EUR73 including bank charges, a three-month multiple-entry visa and airport biometric issuance, with a 48-working-hour response. Because these official procedures conflict on route, fee, entry count and stay, the record remains pending review and draft. The current Bangladesh-specific procedure must be confirmed before verification.\n\n## Pending Cameroon record

The Cameroon Ministry of Tourism and Leisure says a tourist visa is mandatory, with a 30-day non-renewable multiple-entry visa, a 50,000 XAF fee, return ticket, yellow-fever certificate, two photos, accommodation and application form. The Ministry of External Relations MINREX eVisa page lists an ordinary short-stay visa valid up to six months at 100,000 XAF (express 150,000 XAF), with 72-hour ordinary or 24-hour express processing. The official fee and stay descriptions conflict. The record is pending and unpublished until the competent mission confirms the current tourist category, permitted stay and applicable tariff for Bangladesh ordinary passports.

## Research-in-progress destinations

| Destination | Official evidence | Exact blocker |
|---|---|---|
| Gabon | DGDI tourist eVisa portal and e-DGDI services portal | A current official Bangladesh-specific visa-exemption/nationality list was not established. Do not assume that portal availability means Bangladesh is not exempt. |
| Madagascar | Official eVisa portal describes tourist-only, single-entry eVisa up to 60 days, current fee tiers and ETA issued within 72 hours after validation | The reviewed official portal does not publish a Bangladesh-specific eligibility or exemption schedule. Confirm Bangladesh ordinary-passport route before staging a requirement record. |

## Source register

### Angola
- Embassy of Angola, Visa-exempt countries and tourism visa procedure: https://www.angola.org/en/consulado/paises-isentos-de-visto
- Angola Migration and Foreigners Service: https://www.smi.gov.ao/

### Benin
- Government eVisa FAQ: https://evisa.bj/faqs
- Government eVisa fee schedule: https://evisa.bj/articles/les-couts-des-visas-dentree-au-benin/8c948600-ca08-443e-bac9-33aae6583b54
- Directorate of Emigration and Immigration service page: https://dei.gouv.bj/services/

### Botswana
- Botswana Tourism Organisation, Countries Requiring Visas: https://botswanatourism.co.bw/travel-info/countries-requiring-visas
- Government of Botswana, Tourism Visa Application: https://www.gov.bw/taxonomy/term/114
- Government eVisa portal: https://www.evisa.gov.bw/

### Côte d’Ivoire
- ONECI official tourist visa-on-arrival nationality portal: https://evisa-ivoirien.oneci.ci/
- SNEDAI official eVisa procedure: https://snedai.com/e-visa/

### The Gambia
- Gambia Immigration Department, Visa Services: https://gid.gov.gm/visa/
- Gambia Ministry of Foreign Affairs, Bangladesh visa-exemption agreement announcement: https://mofa.gov.gm/2025/04/
- Bangladesh Ministry of Home Affairs, implementation file for the Bangladesh–Gambia agreement: https://moha.gov.bd/pages/files/6975ec95a347033ad3eec177

### Mozambique
- National Immigration Service eVisa/eTA portal: https://evisa.gov.mz/
- Embassy of Mozambique, Tourist Visa route guidance: https://www.embassymozambique.se/en/tourist-visa/

### Nigeria
- Nigeria Immigration Service, Tourism Visa F5A: https://immigration.gov.ng/info-center/tourism-visa-f5a/
- Nigeria Immigration Service, Service Level Agreement revised April 2026: https://immigration.gov.ng/wp-content/uploads/2026/04/SERVICE-LEVEL-AGREEMENT-2026_.pdf

### Cameroon
- Ministry of Tourism and Leisure, Visa Application: https://mintoul.gov.cm/infos-pratiques/demande-de-visa/
- Ministry of External Relations, eVisa procedure and tariffs: https://www.diplocam.cm/e-visa/

### Gabon
- DGDI tourist eVisa portal: https://evisa.dgdi.ga/
- DGDI e-Services portal: https://edgdi.dgdi.ga/

### Madagascar
- Official Madagascar eVisa portal, About the eVisa: https://evisamada-mg.com/fr/info/about
- Madagascar National Police portal: https://www.pn.gov.mg/

## Import and publication status

- New production records inserted: 0
- Existing production records updated: 0
- Production verification status changed: 0
- Production records published: 0
- Static pages or sitemap changed: 0

The guarded importer remains limited to the earlier 12-country batches 06–08. It does not support batch 17. Its restore-test step requires privileged MySQL access (`sudo -n mysql --protocol=socket`), which previously failed. No production workflow was triggered and no backup/restore safeguard was bypassed.

The six research-verified rows are marked as publishable in the research files only. The Cameroon row remains `pending_review` / `draft`. All require controlled import, production readback and the admin verification process before any publication.
