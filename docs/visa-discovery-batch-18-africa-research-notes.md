# Batch 18 — Africa visa discovery and research

**Checked:** 14 November 2026  
**Passport:** Bangladesh ordinary passport  
**Purpose:** Tourism / short visitor stay  
**Region:** Africa — West Africa, East Africa and Indian Ocean

## Live-audit boundary

The latest available successful production audit is the 14 November 2026 backend deployment at 05:37 UTC. No later backend deployment or production import is present in the repository's recent Actions history. The only workflow that runs `php artisan visa:requirements-audit` is the production deployment workflow, which also synchronizes application files, refreshes Laravel caches, imports fee/research files, seeds records and imports/publishes earlier batches. It was not triggered for this research-only run because no fresh restorable backup was available and the workflow has production side effects.

Last confirmed production state: 250 master destinations; 249 country rows; 41 verified/published Bangladesh tourism requirements; 0 pending production requirements; public directory 41. This is the last confirmed state, not a fresh same-run database read.

## Run result

- Ten previously unstarted African destinations were progressed: Burkina Faso, Burundi, Cabo Verde, Djibouti, Eritrea, Guinea, Guinea-Bissau, Malawi, Seychelles and Togo.
- Four Bangladesh ordinary-passport tourism requirement records are supported by current official evidence and staged as research-verified: Burkina Faso, Cabo Verde, Seychelles and Togo.
- One structured record is staged as pending review: Burundi, because official government sources conflict on the ordinary tourist visa fee and the competent mission for Bangladesh residents is not established.
- Five destinations remain research-in-progress without requirement rows: Djibouti, Eritrea, Guinea, Guinea-Bissau and Malawi. Exact blockers are below.
- All ten destination country rows already exist in the UniGo master table. No country-master records were added.
- The five staged rows are research-file classifications only. They have not been imported, read back, verified or published in production.

## Four research-verified records

| Destination | Bangladesh ordinary-passport tourism route | Confirmed details |
|---|---|---|
| Burkina Faso | Short-stay visa required; eVisa or diplomatic mission route. National Police also describes short visa issuance at land borders and Ouagadougou airport on request. | Government announced fee waiver for African nationals in September 2025 and explicitly stated that free visa does not mean visa exemption. Bangladesh is not African. Current non-African fee is not asserted because the National Police XOF47,000 schedule was not reconciled with the current eVisa tariff. Airport issuance is not recommended without mission confirmation. |
| Cabo Verde | Prior tourist visa required. Bangladesh is explicitly on the Government's visa-required nationality list. | Visa cost CVE4,898; tourist visa use window 60 days after issuance; tourist category validity up to 90 days. Passport validity at least three months beyond stay. All travellers must pre-register through EASE; international Airport Security Tax is separately CVE3,400. Visa applications are currently handled by embassies/consular posts; EASE is not an eVisa approval. |
| Seychelles | Visa-free; visitor permit issued on arrival; mandatory Travel Authorisation before departure. | Initial visitor permit free for three months; extension SCR5,000 per additional three-month period. Standard Travel Authorisation EUR10.90. Return/onward ticket, confirmed accommodation and funds of at least USD150/day required. Passport must remain valid through return to country of origin/residence. |
| Togo | Prior tourist eVisa required. Bangladesh is explicitly listed in the official visa-required nationality table. | Tourist visa tiers: XOF25,000 (1–15 days single), XOF35,000 (1–15 days multiple), XOF45,000 (16–30 days multiple), XOF65,000 (31–90 days multiple). Standard processing up to five working days; submit at least five days before arrival. Visa on arrival and express visa are suspended until further notice. |

## Pending structured record — Burundi

The Burundi Ministry of Tourism states that visas are required for all foreign nationals except East African Community nationals. Bangladesh is not an EAC member. The Ministry's public fee schedule states USD70 per month for an entry visa and USD90 per month for multiple entry. The Embassy of Burundi in Abuja's ordinary-passport checklist instead lists USD90 and asks for a hotel/host invitation, passport copy, yellow card and two recent passport photographs. The Embassy of Burundi in Paris lists EUR65 for a one-month tourist/ordinary visa and a two-week processing estimate for its own jurisdiction. These official fee and mission-specific figures cannot be reconciled into one Bangladesh fee. Keep the record pending and unpublished; confirm the competent mission and current fee for Bangladesh residents.

## Research-in-progress destinations

| Destination | Official evidence found | Exact blocker |
|---|---|---|
| Djibouti | Official Immigration Directorate eVisa portal describes online visa issuance replacing the visa previously issued at the port of entry. It asks for passport valid at least six months, flight/transport reservation, accommodation address, invitation letter and card payment. | The public portal does not expose a Bangladesh-specific exemption/nationality result, current fee, stay duration or processing SLA. Obtain the BGD nationality result or written Immigration confirmation before staging a requirement row. |
| Eritrea | No sufficiently current official Bangladesh ordinary-passport tourist visa source was established in this run. | Confirm visa requirement, responsible mission for Bangladesh, application method, fees, permitted stay and tourist checklist from an Eritrean government or diplomatic source. |
| Guinea | Official Guinea PAF eVisa portal states that foreign nationals require a visa except visa-waiver nationalities. The official 2023 National Tourism Office guide lists Bangladesh among countries that may enter without a visa for up to 90 days and then require a visa. | The 2023 Bangladesh-specific statement is stale and the current PAF nationality selector did not expose a public Bangladesh result. Obtain current written confirmation of whether the 90-day exemption remains in force. Do not infer from the generic eVisa portal. |
| Guinea-Bissau | Official government portal is live but states that the citizen portal is still being built and public services are not yet available. | No current official Bangladesh ordinary-passport visa list, tourist fee, stay or application route was established. Obtain confirmation from the competent Guinea-Bissau mission or immigration authority. |
| Malawi | Official eVisa portal's 24 February 2026 notice establishes Category Two visa-on-arrival eligibility and Category One prior-authorization requirement. The portal provides a nationality-specific eligibility wizard. | The public page does not expose the Bangladesh result or downloadable official category lists. A third-party list labels Bangladesh Category One, but it is not accepted as publication evidence. Obtain the Bangladesh result from the official wizard or written Immigration confirmation. |

## Source register

### Burkina Faso
- National Police, Visa services: https://police.gov.bf/index.php/infos-utiles/our-services
- Government of Burkina Faso, Council of Ministers 11 September 2025 — free visa fees for African nationals, not visa exemption: https://gouvernement.gov.bf/actualites/synthese-du-conseils-des-ministres-du-11-septembre-2025-gratuite-de-visa-pour-les-ressortissants-africains/
- Embassy of Burkina Faso in France, visa and eVisa guidance: https://france.diplomatie.gov.bf/index.php/services/visa/

### Burundi
- Ministry of Tourism, Visa: https://tourisme.gov.bi/visa/
- Embassy of Burundi in Abuja, ordinary-passport checklist: https://www.ambabuabuja.mae.gov.bi/visa/
- Embassy of Burundi in Paris, tourist/ordinary visa: https://www.ambabuparis.mae.gov.bi/visa/

### Cabo Verde
- Government of Cabo Verde, Countries requiring a visa: https://www.gov.cv/en/paises-com-visto-obrigatorio-para-cabo-verde
- Government of Cabo Verde, Visa application, fee and validity: https://www.gov.cv/en/pedir-visto
- Government of Cabo Verde, Airport Security Tax: https://www.gov.cv/pedir-tsa
- EASE traveller pre-registration: https://ease.gov.cv/

### Seychelles
- Immigration and Civil Status, Visiting Seychelles: https://ics.gov.sc/visa-and-travel/visiting-seychelles
- Ministry of Foreign Affairs, Travelling to Seychelles: https://mfa.gov.sc/travelling-to-seychelles/
- Immigration, Visitors Permit and extension fees: https://ics.gov.sc/permits/visitors-permit

### Togo
- Togo Voyage, eVisa and tourist visa procedure: https://voyage.gouv.tg/about/
- Togo Voyage official immigration and visa platform: https://voyage.gouv.tg/

### Djibouti
- Official Immigration Directorate eVisa portal: https://www.evisa.gouv.dj/
- Ministry of Interior contact: https://interieur.gouv.dj/contactez-nous/

### Guinea
- Central Directorate of Border Police, eVisa: https://www.paf.gov.gn/visa
- Guinea National Tourism Office, 2023 Tourist Guide: https://mpci.gov.gn/TOURISM/GUIDETOURISTIQUE.html
- Guinea Government Service Public, visa application: https://service-public.gov.gn/ova_sev/demande-de-visa/

### Guinea-Bissau
- Government of Guinea-Bissau portal: https://gov.gw/

### Malawi
- Department of Immigration and Citizenship Services, eVisa eligibility notice and wizard: https://evisa.gov.mw/VisaAssistance
- Department of Immigration, visa guidance: https://www.immigration.gov.mw/visa/

## Import and publication status

- New production records inserted: 0
- Existing production records updated: 0
- Production verification status changed: 0
- Production records published: 0
- Static pages or sitemap changed: 0

The guarded importer remains limited to earlier batches 06–08 and does not support batch 18. Its restore-test step requires privileged MySQL access (`sudo -n mysql --protocol=socket`), which previously failed. No production workflow was triggered and no backup/restore safeguard was bypassed.

Four research-verified rows and one pending row are marked in the research files only. All require controlled import, production readback and the admin verification process before any publication.
