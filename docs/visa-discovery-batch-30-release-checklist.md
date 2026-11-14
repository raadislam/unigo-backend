# UniGo Global Visa Intelligence — Batch 30

Checked: 15 November 2026 (Bangladesh local time)

## Production baseline before this batch

- Country master rows: 250.
- Bangladesh tourism requirement records: 124.
- Verified and published: 122.
- Pending/unpublished: 2.
- Public directory/API/static pages/sitemap: 122, with zero missing or extra slugs in the latest successful frontend verification.
- The cumulative discovery ledger contains 250 destinations; 29 were still marked not started before this batch.
- Published photo-specification audit from the latest complete production snapshot: 15 structured verified specifications, 1 official no-fixed-printed-size case (Australia), 7 VAC photo-capture cases (overlap with structured specifications), and 106 missing/unresearched. The 7 VAC cases are a subset, not an additional total.
- Applicant checklist audit: 16 records contain all four applicant types, 1 record is incomplete (Malaysia), and 105 published records have no checklist.

## Verified import allowlist

1. **Montserrat (MS)** — Bangladesh is explicitly listed by the Government of Montserrat as not requiring a visa. The Immigration Act provides a visitor permit for up to six months, with extension to a total of 12 months from entry at officer discretion. The Tourism Authority specifies passport validity of six months beyond planned return and an online ED form before arrival. This is visa-exempt entry, not visa-on-arrival.
2. **Puerto Rico (PR)** — CBP states that non-U.S. citizens face the same entry requirements as entering the United States from any foreign destination. No separate Puerto Rico visa; U.S. B-2/B1-B2 rules apply.
3. **U.S. Virgin Islands (VI)** — Same CBP territorial rule; no separate U.S. Virgin Islands visa.
4. **United States (US), existing record update** — Material 2026 change: Bangladesh is on the U.S. B1/B2 visa-bond list. The amount is US$10,000, US$15,000 or US$20,000 as directed by the consular officer. Payment must not be made before direct official instructions; bond holders must enter/exit through commercial-air ports only. The B1/B2 reciprocity schedule remains one entry, three months. The MRV application fee is US$185. The separate statutory Visa Integrity Fee is not treated as currently payable because the official DHS implementation notice deferred its collection to a future action and no later official collection notice was found in this check.

## Research-only candidates excluded from production import

- **Turks & Caicos Islands (TC):** Bangladesh is absent from the visa-exempt list and is visa-required unless a qualifying UK/US/Canada visa or residence status exemption applies. The current visitor-visa web page and its linked 2016 Rev.2 form conflict on whose bank reference is required; the form also lists vaccination proof for applicants over 12 while the current web checklist omits it. The fee currency, processing estimate, permitted stay and Bangladesh receiving mission are not confirmed. Request written clarification.
- **St. Helena (SH):** Bangladesh is listed as visa-required and eVisa is required before travel. The official FAQ says the eVisa application has no cost; the 2024 Immigration Policy lists £50 standard/£100 expedited visa-national fees; the Short Term Entry Permit page lists a separate £20 entry fee. The 2026 amendment proposes a 365-day holiday permit but commencement depends on a Governor's Order; the current eVisa page still describes 183 days. Request written confirmation of the fee structure and commencement date.

Neither pending candidate is included in the verified import allowlist or public directory.

## Official source URLs

### Montserrat
- https://www.immigration.ms/countries/visa_not_required
- https://www.gov.ms/wp-content/uploads/2026/02/13.01-Immigration-Act.pdf
- https://www.visitmontserrat.com/air/
- https://www.gov.ms/2024/08/20/automated-services-to-be-introduced-at-montserrats-ports-of-entry/

### Puerto Rico and U.S. Virgin Islands
- https://www.help.cbp.gov/s/article/Article-1443?language=en_US
- https://travel.state.gov/content/travel/en/us-visas/tourism-visit/visitor.html
- https://travel.state.gov/content/travel/en/us-visas/Visa-Reciprocity-and-Civil-Documents-by-Country/Bangladesh.html
- https://travel.state.gov/en/newsroom/visas/2026/countries-subject-to-visa-bonds.html?pubDate=20260802
- https://travel.state.gov/content/travel/en/us-visas/visa-information-resources/photos.html
- https://travel.state.gov/content/travel/en/us-visas/visa-information-resources/photos/digital-image-requirements.html
- https://travel.state.gov/en/newsroom/visas/2026/countries-subject-to-visa-bonds.html?pubDate=20260802
- https://travel.state.gov/content/travel/en/us-visas/Visa-Reciprocity-and-Civil-Documents-by-Country/Bangladesh.html
- https://www.govinfo.gov/content/pkg/PLAW-119publ21/pdf/PLAW-119publ21.pdf
- https://public-inspection.federalregister.gov/2025-13738.pdf

### U.S. visa integrity fee caveat
- https://public-inspection.federalregister.gov/2025-13738.pdf
- https://uscode.house.gov/us/usc/119-118/t8/s1806/

## Safety and import plan

- Existing country master rows are present for all four allowlisted codes; no country master creation or schema migration is needed.
- The stable key remains Bangladesh nationality + tourism purpose + destination ISO-2.
- The allowlist is exactly MS, PR, VI, US. TC and SH are research-only.
- The existing production importer and audit command are already installed and were exercised by the successful batch 29 workflow; batch 30 does not need to overwrite production application code.
- Batch 30 must create a fresh production backup, verify gzip integrity, restore-test in disposable MySQL, dry-run, import only the four allowlisted verified rows, read back every record/source, and verify public API.
- After backend verification, run the public static export/deploy and verify the complete live API slug set against every generated country page, sitemap, directory link and live page. Verify SVG flag assets for MS, PR, VI and US plus AU, AT and BE.
- No destructive SQL, migration, table/column drop, reset, truncation, or key generation is permitted.
