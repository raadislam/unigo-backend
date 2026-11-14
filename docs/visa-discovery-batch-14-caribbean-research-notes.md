# Visa discovery batch 14 — Caribbean and Central America

Checked: 2026-11-14  
Passport: Bangladesh ordinary passport  
Purpose: tourism / short visitor stay

## Production baseline

The latest successful production read-only audit available for this run was generated on 2026-11-14 at 05:37:38 UTC by the backend deployment workflow. It reported 41 visa requirement records in total, all verified; 41 verified/published Bangladesh tourism requirements; 249 country-table rows, all with `is_published=false`; and 41 public country-directory entries. The deployment workflow has not run after the research-only batch merges. The previous guarded import failed before database writes. No production import or publication is performed by this batch.

The discovery ledger contains 250 destinations. The nine destinations in this batch were already present in the master ledger and country table but were still marked not started. This batch changes their research state; it does not add new country-table rows.

## Batch 14 staged research

| Destination | Bangladesh ordinary-passport tourism route | Research classification / blocker |
|---|---|---|
| Barbados | Visa-free for up to six months, per the Barbados MFA visa list revised 9 May 2025. Visitor checklist: passport valid at least six months, return ticket, intended address, evidence of personal support and online Immigration/Customs Form within 72 hours before arrival. | Verified/publishable in research files. Not imported or published in production. |
| Dominica | The Government visa-exemption page says Commonwealth members may enter for up to six months, and Bangladesh is a Commonwealth member. The page is dated 21 December 2012; entry checklist lists passport, return ticket, sufficient funds and local address. | Pending. Obtain current Bangladesh-specific confirmation from Dominica Immigration; do not publish from the 2012 nationality guidance alone. |
| Jamaica | PICA entry-visa table explicitly lists Bangladesh (Commonwealth) as no visa required. PICA requires a valid approved travel document and completed/signed Immigration/Customs C5 form. The table does not state a Bangladesh-specific maximum stay. | Verified/publishable in research files. PICA table's printed update date is 19 July 2023; recheck if the authority issues a newer country-specific schedule. Not imported or published in production. |
| Antigua & Barbuda | Bangladesh is absent from the visa-exempt list and therefore requires an entry visa. eVisa is available. Conditional VOA page lists valid US/Canada/UK/Schengen visa or residence as a condition and USD100 fee, but the eVisa FAQ says arriving without a visa is not permitted. | Pending. Reconcile the conflicting VOA and eVisa FAQ instructions for ordinary Bangladeshi passports. |
| Guatemala | Bangladesh ordinary passports are Category C (visa consultada). The 2026 IGM checklist requires a Guatemalan guarantor and prior IGM review. | Pending. MINEX tariff lists USD50 single / USD150 multiple tourist visa; IGM 2026 checklist separately lists a USD25 visa-service filing charge. Confirm whether fees are separate/additive and confirm Bangladesh application jurisdiction. |
| Costa Rica | Embassy of Costa Rica in the UK lists Bangladesh among nationalities requiring a restricted visa. Approval and maximum stay are decided by the Commission for Restricted Visas and Refuge. Qualifying multiple-entry US/Canada visas or residence may provide an exception under DGME guidance. | Pending. Confirm Bangladesh-specific application route, fee, current checklist and how the exception is applied to ordinary-passport visitors. |

## Additional discovery candidates logged but not staged

- **Dominican Republic (DO):** MIREX consular nationality list dated 12 February 2015 includes Bangladesh among visa-required nationalities. The same guidance describes a tourist-card exception for holders of permanent residence or valid multiple-entry visas for the US, EU/Schengen, Canada or Great Britain. The dynamic MIREX nationality lookup surfaced India rather than Bangladesh in the accessible result; obtain a current Bangladesh-specific lookup and confirm the New Delhi jurisdiction before staging.
- **Grenada (GD):** Official Embassy of Grenada in Beijing visa list, updated February 2021, states Bangladesh — no visa required. This list is too old for a 2026 publication decision; obtain current written confirmation from Grenada Immigration or the responsible mission.
- **Saint Lucia (LC):** Official country visa list marks Bangladesh as requiring a prior visa, but its last complete update is August 2008 and last partial update January 2013. Current generic service information gives EC$125 single-entry / EC$190 multiple-entry, 5 working days and submission at least two weeks before travel; confirm current Bangladesh-specific eligibility and route.

## Source register

### Barbados
- Barbados Ministry of Foreign Affairs, Visa Requirements for Foreign Nationals Entering Barbados (revised 9 May 2025): https://www.foreign.gov.bb/wp-content/uploads/2025/11/Updated-visa-list-May-09-2025-002.pdf
- Barbados Immigration Department, Visitors to Barbados: https://immigration.gov.bb/pages/visitor.aspx
- Barbados Ministry of Foreign Affairs, Planning to Visit: https://www.foreign.gov.bb/planning-to-visit/

### Dominica
- Government of Dominica, Do I Need a Visa to Enter Dominica (article dated 21 December 2012): https://dominica.gov.dm/services/passports-and-travel-documents-non-nationals/do-i-need-a-visa-to-enter-into-dominica
- Government of Dominica, Entry Requirements for Non-Nationals: https://dominica.gov.dm/services/passports-and-travel-documents-non-nationals/what-are-the-entry-requirements-for-the-commonwealth-of-dominica
- The Commonwealth, Bangladesh member profile: https://thecommonwealth.org/our-member-countries/bangladesh
- Government of Dominica, Immigration (Passport Exemption) Regulations: https://www.dominica.gov.dm/laws/chapters/chap18-01.pdf

The visa-exemption webpage is dated 21 December 2012. It is an official lead but not sufficient on its own for 2026 publication; obtain current Bangladesh-specific confirmation from Dominica Immigration.

### Jamaica
- Jamaica Passport, Immigration and Citizenship Agency, Entry Visa Requirements: https://www.pica.gov.jm/immigration/entry-visa-requirements/
- Jamaica Passport, Immigration and Citizenship Agency, Travel Documents Required: https://www.pica.gov.jm/node/96

### Antigua & Barbuda
- Department of Immigration, Countries Exempt from Visa: https://immigration.gov.ag/visa-services/countries-exempt-from-visa/
- Department of Immigration, Visa on Arrival: https://immigration.gov.ag/visa-services/visa-on-arrival/
- Department of Immigration, Visa FAQs: https://immigration.gov.ag/frequently-asked-questions/visa-faqs/
- Department of Immigration, Visa Application Process: https://immigration.gov.ag/visa-services/visa-application-process/

### Guatemala
- Ministry of Foreign Affairs, Visas for Foreigners (Category C): https://www.minex.gob.gt/din/26-visas-para-extranjeros
- Institute of Migration, Category C Tourist/Visitor Visa Checklist (IGM 01-2026): https://igm.gob.gt/wp-content/uploads/2026/01/Visa-de-Turista-o-Viajero-para-paises-Categoria-C-VTV-IGM-01-2026.pdf
- Institute of Migration, Category C Tourist/Visitor Visa Application Form (English, IGM 01-2026): https://igm.gob.gt/wp-content/uploads/2026/01/Visa-de-Turista-o-Viajero-para-paises-Categoria-C-VTV-IGM-01-2026-INGLES.pdf

### Costa Rica
- Embassy of Costa Rica in the United Kingdom, Restricted Visas: https://costaricanembassy.uk/restricted-visas/
- Directorate General of Migration and Foreigners, Visas and Entry Guidelines: https://migracion.go.cr/visas/

### Dominican Republic — ledger only
- Dominican Republic Consulate General in The Hague, List of Countries and Visa Conditions (12 February 2015): https://nld.mirex.gob.do/consular/amsterdam/listado-de-paises-y-condiciones-de-visado/
- MIREX, Conditions for Foreigners Entering the Dominican Republic: https://consultas.mirex.gob.do/servicios/visas/consulta-de-requisitos-para-extranjeros-ingresar-a-la-republica-dominicana

### Grenada — ledger only
- Embassy of Grenada in Beijing, Visa Requirements for Traveling to Grenada (updated February 2021): https://gndembassyprc.mofa.gov.gd/wp-content/uploads/2023/08/Visa-Requirements-for-Grenada-final-UPDATED-February-2021-Approved1.pdf
- Embassy of Grenada in Beijing, Consular Services: https://gndembassyprc.mofa.gov.gd/consular-services/

### Saint Lucia — ledger only
- Saint Lucia Ministry of External Affairs, Country Visa Requirements (last complete update August 2008; partial update January 2013): https://www.govt.lc/media.govt.lc/www/resources/publications/CountryVisaRequirements.pdf
- Saint Lucia Ministry of External Affairs, Apply for Saint Lucia Non-Immigrant Visa: https://externalaffairs.govt.lc/services/apply-for-saint-lucia-non-immigrant-visa
- Saint Lucia Department of External Affairs, Visiting Saint Lucia: https://stlucia.gov.lc/visiting-saint-lucia

## Import, verification and publication controls

- The seed and full-audit CSVs contain six records: three research-verified/publishable and three pending/draft.
- All three pending records remain `pending_review` and `draft`.
- The three research-verified records are classified as publishable in the research audit file only; this is not production publication.
- The validator checks unique (passport, destination, purpose) keys, required fields, HTTPS official sources, same-day check dates and matching seed/full-audit keys.
- The deployment workflow ignores the batch CSVs, discovery ledger, validator and batch research notes. No deployment, migration, import or publication is performed by this research PR.
- No production data is changed.