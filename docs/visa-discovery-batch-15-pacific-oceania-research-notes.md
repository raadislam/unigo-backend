# Visa discovery batch 15 — Pacific and Oceania

Checked: 2026-11-14  
Passport: Bangladesh ordinary passport  
Purpose: tourism / short visitor stay

## Production baseline

The latest successful production read-only audit available during this research run was generated on 2026-11-14 at 05:37:38 UTC. It reported 41 visa requirement records in total, all verified; 41 verified/published Bangladesh tourism requirements; 249 country-table rows, all with `is_published=false`; and 41 public country-directory entries. At the time of the original batch-15 research, no subsequent production deployment run was recorded. A later automatic backend deployment triggered by the Kosovo backfill seeder in batch 20 is documented in `visa-discovery-batch-20-africa-kosovo-research-notes.md`. This PR changes only research CSVs, the cumulative ledger, validator and research notes; these paths are excluded from the production deployment trigger. No production import or publication is performed by this batch.

The discovery universe contains 250 destinations. All eight destinations staged in this batch were already present in the country master table and ledger but were still marked not started. They are research progressions, not new country-table rows. Solomon Islands is logged as a research candidate only because the Bangladesh-specific exemption status remains unconfirmed.

## Batch 15 staged research

| Destination | Bangladesh ordinary-passport tourism route | Research classification / blocker |
|---|---|---|
| Fiji | Bangladesh is explicitly on the Fiji High Commission's visa-exempt list. Visa-exempt nationals receive a visitor permit for up to four months on arrival; a further two months may be granted on application. Passport must be valid six months from entry; return/outbound ticket, accommodation confirmation and latest bank statement are required. | Verified/publishable in research files. The High Commission list is marked as of 20 September 2023 and is corroborated by the ICAO APAC 2025 information bulletin. Not imported or published in production. |
| Vanuatu | Bangladesh appears on the Department of Immigration's exempted-country list. Exempt-country travellers may obtain a tourist visa on arrival, for up to 120 days, with no extension. Passport must exceed six months validity; return ticket, accommodation/local address and financial means are required. | Verified/publishable in research files. Separate transit visa requirements must be checked independently; Bangladesh appears in the transit visa charge schedule. Not imported or published in production. |
| Kiribati | The Immigration Entry and Visa Exemption Order 2023 explicitly lists Bangladesh, applies to ordinary/normal passports, waives visitor and transit visa requirements, requires an ICAO-compliant passport valid at least six months on departure, and limits stay to 90 days in any 12-month period. The 2023 order repealed the older 2007 order. | Verified/publishable in research files. Not imported or published in production. This corrects the older 2007-list finding that Bangladesh was not exempt. |
| New Zealand | Bangladesh is absent from the current Immigration New Zealand visitor visa-waiver and transit visa-waiver lists. A Visitor Visa is required for tourism. Fee from NZD441; 80% within two weeks; multiple-entry stay up to six months in 12 months or single-entry up to nine months in 18 months. Passport must remain valid at least three months after planned departure. | Verified/publishable in research files. Current official guidance sets funds at NZD1,000/month or NZD400/month if accommodation is prepaid. INZ's 13 November 2026 outage notice says systems are back up and applications can be submitted again. Not imported or published in production. |
| Samoa | Bangladesh ordinary-passport holders are covered by the universal rule: foreign nationals receive a **free 90-day Visitor Permit on arrival** using the arrival card, provided entry conditions are met. Passport must remain valid six months after departure; return/onward ticket, next-country visa/permit, accommodation address and sufficient funds are required. | **Verified/publishable in research files.** The separate permit-application fee schedule lists SAT$200 for a Visitor entry permit and 10-working-day processing; Border Services explicitly says the on-arrival Visitor Permit is free. The fee page does not explicitly say the SAT$200 applies to the free on-arrival route, so the record discloses it separately with a scope caveat. Sources: [Border Services](https://immigration.gov.ws/border-services/), [Permits](https://immigration.gov.ws/permits/), [Fees](https://immigration.gov.ws/fees/), [22 September 2026 passport notice](https://immigration.gov.ws/check-your-passport-validity-before-travelling-to-samoa/). Not imported or published in production. |
| Tonga | Bangladesh is not in the current government visa-on-arrival/visa-free nationality schedule. Invest in Tonga states all countries not listed must obtain a visa before arrival. The 2025 Ministry of Foreign Affairs form has a separate non-waiver-country checklist and says the entry visa is issued in a letter format before arrival. | Verified/publishable in research files. The official form lists the application channel and checklist; fee and processing SLA are not stated and remain unspecified. Not imported or published in production. |
| Papua New Guinea | Visa on arrival is limited to selected nationalities and was extended to Pacific Island States and Territories in December 2023; Bangladesh is not listed. A visitor visa is required before travel. The usual visitor visa is single entry for up to 60 days. | Pending. ICA's Visitor Visa page directs non-VOA nationals to the paper Entry Permit Form, while its eVisa class page also lists Tourist Own Itinerary online. Confirm whether Bangladeshi ordinary-passport tourists may apply online or must lodge paper, and confirm the applicable fee. |
| Palau | Executive Order 478, signed 10 February 2023 expressly states that Executive Order 274 remains in force for passports issued by excluded countries, including Bangladesh: no visa of any nature may be issued at a port of entry, and a valid Palau visa must be issued before arrival. | Pending. The prior-visa requirement is confirmed, but tourist visa application channel, fee, permitted stay and checklist were not established. Obtain details from the Palau Bureau of Immigration before completing the record. |

## Additional discovery candidate logged but not staged

- **Solomon Islands (SB):** The official Immigration Division lists a Tourist Visa with USD50 visa fee plus USD50 online lodgement fee, three months permitted stay per entry, visa validity up to one year, multiple entries, six-month passport validity, passport biodata, cover letter, return/onward ticket, hotel booking and sufficient funds. The published mutual-exemption pages reviewed cover China, UAE, EU, India, Indonesia and Hong Kong; no exhaustive Bangladesh ordinary-passport exemption list was found. Keep as research-in-progress and obtain explicit BGD ordinary-passport confirmation before staging a requirement record.

## Sources

### Fiji
- Fiji High Commission Wellington, Visa/Permits/Waivers (visa-exempt list marked 20 September 2023): https://www.foreignaffairs.gov.fj/fiji-high-commission-new-zealand/visa-permits-waivers/
- ICAO APAC, 2025 Information Bulletin: https://www.icao.int/sites/default/files/APAC/Meetings/2025/2025%20SAP%20PSIDs/2-General%20Information/ICAO-INFORMATION-BULLETIN-2025.pdf

### Vanuatu
- Department of Immigration and Passport Services, Visa Exempted Countries: https://immigration.gov.vu/visa-exempted-countries/
- Department of Immigration and Passport Services, Visit Vanuatu: https://immigration.gov.vu/visit/
- Department of Immigration and Passport Services, Entry and Departure Requirements: https://immigration.gov.vu/entry-and-departure-requirements-for-vanuatu/

### Kiribati
- Republic of Kiribati, Immigration Entry and Visa Exemption Order 2023 (effective 1 September 2023): https://www.mfa.gov.ki/wp-content/uploads/2023/09/VISA-EXEMPTION-ORDER-2023-1.pdf
- Kiribati Immigration Division, Visas & Entry: https://immigration.mfai.gov.ki/visas-entry/
- Kiribati Immigration Division, official home and 2023 Order notice: https://immigration.mfai.gov.ki/

### New Zealand
- Immigration New Zealand, Visa Waiver Countries and Territories: https://www.immigration.govt.nz/visit/what-you-need-to-visit-new-zealand/visa-waiver-countries-and-territories/
- Immigration New Zealand, Visitor Visa: https://www.immigration.govt.nz/visas/visitor-visa/
- Immigration New Zealand, Transit Visa Waiver Countries and Territories: https://www.immigration.govt.nz/visit/transiting-through-new-zealand/transit-visa-waiver-countries-and-territories/
- Immigration New Zealand, Impact of Microsoft Outage (13 November 2026): https://www.immigration.govt.nz/about-us/news-centre/impact-of-microsoft-outage/

### Samoa
- Samoa Immigration, Border Services: https://immigration.gov.ws/border-services/
- Samoa Immigration, Permits and Fees: https://immigration.gov.ws/permits/

### Tonga
- Invest in Tonga, Visas (government nationality schedule and prior-visa rule): https://www.investtonga.gov.to/articles/visas
- Tonga Ministry of Foreign Affairs, Visitor Visa Application Form (2025): https://www.gov.to/wp-content/uploads/2025/01/Visitors-VISA.pdf
- Tonga Ministry of Revenue and Customs, Immigration and General Services: https://www.revenue.gov.to/immigration-and-general-services

### Papua New Guinea
- Papua New Guinea Immigration and Citizenship Services Authority, Visitor Visa: https://ica.gov.pg/visa/visit/visitor
- Papua New Guinea ICA, VOA extended to Pacific Island States and Territories (20 December 2023): https://ica.gov.pg/press-releases/2023/papua-new-guinea-opens-visa-on-arrival-voa-to-all-pacific-island-states-and-territories
- Papua New Guinea ICA, 2025 Visa Information Booklet: https://ica.gov.pg/uploads/media/post_file_3195072-visa-information-booklet-2025.pdf
- Papua New Guinea ICA, Visa on Arrival Eligible Country List: https://ica.gov.pg/uploads/media/post_file_6166280-voa-eligible-country-list-public-notice-31032014-v3-new.pdf

### Palau
- Republic of Palau Office of the President, Executive Order 478 (signed 10 February 2023): https://www.palaugov.pw/wp-content/uploads/EO-478-Removing-the-restriction-on-the-number-of-work-visas-issued-by-the-BH-to-citizens-of-the-Peoples-Republic-of-Bangladesh.pdf
- Republic of Palau National Government: https://www.palaugov.pw/

### Solomon Islands — ledger only
- Solomon Islands Immigration Division, Visas: https://immigration.gov.sb/visas/
- Solomon Islands Immigration Division, Tourist Visa: https://immigration.gov.sb/visitor-visa-tourist/
- Solomon Islands Immigration Division, Mutual Visa – China: https://immigration.gov.sb/mutual-visa-peoples-republic-of-china/
- Solomon Islands Immigration Division, Mutual Visa – UAE: https://immigration.gov.sb/mutual-visa-uae/
- Solomon Islands Immigration Division, Mutual Visa – EU: https://immigration.gov.sb/zh-hant/mutual-visa-eu/
- Solomon Islands Immigration Division, Mutual Visa – India: https://immigration.gov.sb/mutual-visa-india/
- Solomon Islands Immigration Division, Mutual Visa – Indonesia: https://immigration.gov.sb/mutual-visa-indonesia/
- Solomon Islands Immigration Division, Mutual Visa – Hong Kong: https://immigration.gov.sb/mutual-visa-hong-kong/

## Import, verification and publication controls

- The seed and full-audit CSVs contain eight records: six research-verified/publishable and two pending/draft.
- Both remaining pending records remain `pending_review` and `draft`.
- The six research-verified records are classified as publishable in the research audit file only; this is not production publication.
- Solomon Islands remains ledger-only until the Bangladesh ordinary-passport exemption status is confirmed.
- The validator checks unique (passport, destination, purpose) keys, required fields, field lengths, HTTPS official sources, same-day check dates, at least two official sources per record, and matching seed/full-audit keys.
- The production deployment workflow excludes the batch CSVs, discovery ledger, validator and batch research notes. No deployment, migration, import or publication is performed by this research PR.
- No production data is changed.
