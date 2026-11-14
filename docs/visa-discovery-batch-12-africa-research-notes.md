# Visa discovery batch 12 — Africa research

Checked: 2026-11-14  
Passport: Bangladesh ordinary passport  
Purpose: short-stay tourism and visitor travel

## Live database baseline

The latest production audit available during this run was exported by the backend deployment workflow at 2026-11-14 05:37:38 UTC:

- Visa requirement records: 41
- Verified: 41
- Published: 41
- Public Bangladesh tourism directory: 41
- Country master rows: 249
- Country master rows flagged published: 0
- Country master rows flagged unpublished: 249

The following destinations were present in the country master but had no Bangladesh tourism requirement in the 41-record audit: Uganda, South Africa, Zimbabwe, Zambia, Namibia, Ethiopia, Ghana, Morocco, Egypt and Tunisia.

## Batch 12 — staged records

The three records below have sufficiently supported route research for a pending-review import. They are deliberately staged as `verification_status=pending_review` and `publish_status=draft`. The existing Laravel seeder also forces imported records to pending_review and unpublished. No record is approved for public release by this research-only batch.

| Destination | Bangladesh ordinary-passport rule | Route and verified operational details | Research decision |
|---|---|---|---|
| Uganda | Visa required; Bangladesh is not in NCIC's published visa-exempt list | Single-entry online tourist visa, up to 3 months; USD 50; passport valid at least 6 months; tour plan, itinerary/booking, photo and hotel booking/address. Approval authorisation valid 90 days. NCIC's 2 Oct 2026 notice says yellow-fever certificate is no longer mandatory. | Stage pending admin review |
| South Africa | Visa required for entry and for transit | South African High Commission New Delhi is accredited to Bangladesh and directs all Bangladesh applicants to VFS New Delhi. Holiday visa checklist requires DHA-84, passport valid 30 days beyond departure with 2 blank pages, 3-month bank statement, verifiable hotel booking, employer/school NOC, day-by-day itinerary, flight reservation, two recent photos and cover letter. VFS page lists INR 2,342 visa fee, INR 2,040 New Delhi logistics fee and INR 160 convenience fee; minimum 5 working days, indicative. Bangladesh ordinary-passport transit visa fee is gratis, but logistics fees apply. | Stage pending admin review |
| Zimbabwe | Category C: visa must be obtained before travel | Official Category C list names Bangladesh and requires online application/payment/approval before travel. Holiday checklist: passport photo and biodata scan, proof of residence, destination address, host invitation and host residence/ID if staying with a resident. eVisa FAQ says average 7 working days and apply at least 3 weeks before travel. Ordinary single-entry online visa fee USD 30. | Stage pending admin review |

### Uganda official sources

- NCIC, Uganda Tourist Visa – single entry: https://immigration.go.ug/services/tourist-visa
- NCIC, visa-exempt nationalities: https://immigration.go.ug/faqs/visa-exempt
- NCIC, mandatory online applications for visa-prone travellers/no visas on arrival (14 Oct 2021): https://mail.immigration.go.ug/mandatory-online-visa-applications-all-visa-prone-travellers-uganda
- NCIC, yellow-fever certificate no longer mandatory (2 Oct 2026): https://www.immigration.go.ug/index.php/node/254

### South Africa official/provider sources

- South African High Commission New Delhi, Immigration/Visas (Bangladesh applications must use VFS New Delhi): https://dirco.gov.za/newdelhi/elementor-1431/
- VFS Global, South Africa visa information (New Delhi jurisdiction): https://visa.vfsglobal.com/one-pager/southafrica/india/english/index.html
- South African High Commission Ottawa, Bangladesh ordinary-passport entry and transit rule: https://dirco.gov.za/ottawa/immigration-services/
- DIRCO, countries exempt from visas: https://dirco.gov.za/paris/wp-content/uploads/sites/6/2024/12/COUNTRIES-EXEMPT-FROM-VISAS.pdf

### Zimbabwe official sources

- Department of Immigration, Category C countries: https://www.zimimmigration.gov.zw/?p=7196
- Department of Immigration, Holiday Visa Requirements: https://www.zimimmigration.gov.zw/?p=7259
- Official eVisa FAQs: https://www.evisa.gov.zw/faq.html
- Department of Immigration, visa fees: https://www.zimimmigration.gov.zw/?page_id=6958

## Additional countries researched — no record staged yet

| Destination | Finding | Exact blocker |
|---|---|---|
| Zambia | Bangladesh is explicitly on the prior-visa list; application is online and advance approval is required. | Official pages conflict on the single-entry fee: USD 25 on the visitor page versus USD 50 statutory fee on the current pricing schedule. Do not publish a fee until the live checkout or Immigration confirms it. |
| Namibia | Version 4 fact sheet (28 May 2025) says countries absent from all exemption/VOA lists must obtain eVisa before travel; Bangladesh is absent from that version. | Ministry archive contains a later 23 September 2025 “Visa on arrival additional countries” notice and the current eServices portal has a dynamic VOA list. That later supplement/current list must be inspected before assigning BGD VOA status. |
| Ethiopia | Official eVisa site says citizens of all countries are eligible for a tourist eVisa: single entry, 30 days USD 62 or 90 days USD 152; normal processing 3 days; passport 6 months; photo and passport copy. | The universal eVisa eligibility statement does not establish whether Bangladesh ordinary passports have a visa-free or VOA exemption. Obtain BGD-specific official confirmation. |
| Ghana | Official Ghana Immigration Service eVisa/ETA portal is operational. ETA is free for AU, ECOWAS and bilateral visa-waiver nationalities; a live nationality/passport eligibility check is required. | The Bangladesh ordinary-passport checker result and any bilateral ordinary-passport waiver were not established. Portal eVisa tiers are USD 260/96h, USD 338/48h and USD 442/5h, but these must not be assigned to BGD until the checker confirms the route. |
| Morocco | Official eVisa terms accept ordinary passports and provide conditional Category B/C routes based on qualifying residence permits or qualifying multiple-entry visas. | Official sources reviewed did not establish Bangladesh's base ordinary-passport visa status or whether the applicant qualifies under B/C. Obtain Moroccan consular confirmation. |
| Egypt | Official eVisa portal exists. | The BGD ordinary-passport eVisa result and any conditional visa-on-arrival route could not be confirmed from official sources. Secondary reporting conflicts; obtain Embassy of Egypt in Dhaka confirmation. |
| Tunisia | Official e-Consulat and MFA portals provide generic consular services. | No current Bangladesh-specific ordinary-passport tourist visa list, application channel, checklist or fee was established. Obtain mission confirmation. |

## Import and publication controls

- `new_country_visa_batch_12.csv` and `visa_full_audit_batch_12.csv` contain three matching BGD/tourism keys: UG, ZA and ZW.
- All three are staged as pending_review/draft.
- The batch validator is extended to include batch 12 and will enforce source URLs, field limits, at least two official/provider sources per row, unique keys and seed/full-audit parity.
- The production importer has not been triggered. The guarded import workflow previously failed before any DB write because the VPS could not perform its isolated MySQL restore test. Do not bypass that gate.
- No database record was inserted, verified, or published in this research-only change.
- No frontend build, static export, sitemap change or deployment was performed.
