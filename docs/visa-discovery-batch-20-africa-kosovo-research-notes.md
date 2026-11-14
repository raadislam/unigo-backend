# Batch 20 — Africa discovery and Kosovo country-row backfill

Checked: 14 November 2026  
Passport: Bangladesh ordinary passport  
Purpose: Tourism / short visitor stay

## Live production audit

A dedicated read-only audit succeeded after the SSH key newline correction. Run: https://github.com/raadislam/unigo-backend/actions/runs/43601465430

Snapshot generated at 2026-11-14 13:47:08 UTC:
- Discovery master: 250 destinations
- Production country rows: 249
- Missing country row: XK (Kosovo)
- All visa requirement records: 41; all 41 verified
- BGD tourism records: 41 verified and actively published
- Pending/unverified/expired/unpublished BGD tourism records: 0
- Country rows marked published/unpublished: 0 / 249
- Public country directory: 41
- Published records missing a primary official source: 0

The audit is SELECT-only. No production rows, caches, migrations, imports or publications were changed.

## Discovery result

Ten previously unstarted African destinations were progressed: Chad, Somalia, South Sudan, Democratic Republic of the Congo, Central African Republic, Republic of the Congo, Mali, Niger, Libya and Sudan. All ten already exist in the 250-destination master universe.

- **Research-verified:** Chad and Somalia.
- **Pending review:** South Sudan, Democratic Republic of the Congo, Central African Republic, Republic of the Congo, Mali and Niger.
- **Ledger-only / research in progress:** Libya and Sudan.
- **Separate master-row gap:** Kosovo (XK) is the one destination present in the master ledger but absent from the production country table. An idempotent `firstOrCreate` seeder is staged; it has not been run.

## Research-verified findings

### Chad (TD)
The official eVisa portal says entry requires a visa, lists 19 exempt nationalities, and permits any non-exempt foreigner to apply online. Bangladesh is not on the exemption list. Standard visas have been online-only through `https://evisa.td/` since 11 May 2026, except diplomatic/courtesy visas. Apply at least seven days and no more than 90 days before entry. The portal requires a valid passport biodata page and checks yellow-fever vaccination proof on arrival. The public tariff notice does not expose a numeric fee; no amount is invented. Sources:
- https://evisa.td/
- https://evisa.td/articles/8/nouvelles-tarifications-des-visas-en-republique-du-tchad

### Somalia (SO)
The Immigration and Citizenship Agency's 1 September 2025 announcement says all foreign nationals must obtain entry authorisation online in advance through the official eTAS portal. This universal rule includes Bangladesh ordinary passports. The ICA services page requires a passport valid at least six months, a completed application form and supporting documents according to visa type. The public ICA pages do not publish the fee, permitted stay, entry count or processing SLA. The official portal was linked by ICA but its application details were not retrievable; no third-party portal is used. Sources:
- https://www.immigration.gov.so/news/somalia-launches-e-visa-etas-system
- https://www.immigration.gov.so/services
- https://etas.gov.so

## Pending structured records and exact blockers

- **South Sudan (SS):** MFA nationality table explicitly lists Bangladesh as visa-required and gives USD100 single-entry, USD200 three-month multiple-entry and USD350 six-month multiple-entry fees. MFA visa guidance says in-person applications and at least seven working days; the official eVisa portal advertises online issuance within 72 hours. Keep pending until the current Bangladesh application channel is reconciled.
- **DR Congo (CD):** SADC portal describes a general visa requirement, DGM eVisa route, reported USD300 cost and up to 72 hours. The DGM portal does not expose a retrievable BGD nationality result. The exemption list linked from the SADC page is an old airport website; the general eVisa and consular travel-visa durations also differ. Need current DGM confirmation.
- **Central African Republic (CF):** Embassy in Brussels says applicants must confirm conditions for their nationality; it lists a form, two photos, invitation/mission order, hotel and flight plan. Washington fees are jurisdiction-specific. Need BGD classification and competent mission.
- **Republic of the Congo (CG):** Official embassy pages describe short-stay tourist visas under 90 days, in-person applications, 5–10 working days, passport, photos, accommodation/invitation, return ticket, insurance and funds. No BGD exemption schedule or Bangladesh-responsible mission found.
- **Mali (ML):** New Delhi Embassy lists Bangladesh in its regional coverage and routes visa applications through the MFA digital portal. Checklist includes six-month passport, invitation/hotel, return flight and yellow-fever proof; 5–10 working days. Berlin/UN mission fees are not transferable. Current BGD exemption status and local fee need confirmation.
- **Niger (NE):** Ankara Embassy says visas are compulsory except ECOWAS and certain bilateral-exemption states; complete list not retrieved. Bangladesh is not ECOWAS, but bilateral status must be checked. Ankara and Washington fee schedules are jurisdiction-specific and not transferable.

## Ledger-only candidates

- **Libya (LY):** Official Libyan Passport Authority shows current eVisa system activity, but no BGD nationality result, tourist route, fee or stay was retrievable.
- **Sudan (SD):** The Sudan Embassy in Washington describes business, official, work and humanitarian entry categories, but not a tourist visitor route. Its fee and US-residency conditions cannot be transferred to Bangladesh. Current BGD mission and tourist route remain unconfirmed.

## Kosovo country-row backfill

The 2026 read-only audit confirms that Kosovo is the only country row missing from the production country table. This branch stages:
- `database/seeders/KosovoCountryBackfillSeeder.php`, using `Country::firstOrCreate(['iso2' => 'XK'], ...)` and `is_published=false`.
- A Bangladesh ordinary-passport Visa C research record, supported by Kosovo MFA/Embassy guidance and the Official Gazette. The 2023 Bangladesh–Kosovo exemption agreement is limited to diplomatic and official passports. Ordinary passports require Visa C unless a valid multiple-entry Schengen visa or biometric Schengen residence permit provides a 15-day exemption. The EUR40 fee and Dhaka Embassy listing are recorded.
- The country seeder has not been executed; neither Kosovo nor the visa requirement has been imported or published.

## Validation and production boundary

Batch 20 contains 9 structured research rows: 3 research-verified/publishable in the research files (Kosovo, Chad, Somalia) and 6 pending/draft (South Sudan, DR Congo, Central African Republic, Republic of the Congo, Mali, Niger). Libya and Sudan remain ledger-only.

The cumulative validator now includes batch 20. All source URLs and check dates are recorded. Research-file verification is not production verification or publication.

No production import, migration, country seeder, cache operation, frontend build, sitemap update or deployment was performed. The guarded importer still does not support batch 20 and the isolated backup-restore privilege check remains a separate requirement. The last confirmed frontend/API/static/sitemap parity remains 41/41; no new production pages were expected from this research-only branch.
