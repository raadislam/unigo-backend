# UniGo visa discovery batches 09–10 – research and release status

Checked: 2026-11-14  
Passport: Bangladesh ordinary passport  
Purpose: short-stay tourism

## Master universe and live audit

- Master discovery universe / cumulative ledger: **250 destinations**.
- Live country table: **249 rows**. Kosovo (XK) is the one master-list destination without a country-table row.
- Current public BGD tourism directory: **41 destinations**.
- Live BGD tourism requirements: **41 total; 41 verified and published; 0 pending; 0 draft**.
- The remaining **208** country-table rows are not currently represented in the public BGD tourism directory.
- After batches 09–10, **175 ledger destinations remain unstarted**. Candidates marked pending are tracked separately and are not counted as unstarted.

Country discovery, research completion, repository staging, database import, verification and publication are separate states. A staged row is not a live database record and is not a public page.

## Batch 09 discovery

Six Asian destinations were researched in the preceding batch:

| Destination | Official-source finding | Decision |
|---|---|---|
| Qatar | Visit Qatar includes Bangladesh in the paid tourist-visa group and lists QAR100. Hayya A1 eVisa and fee-based VOA are described. | Draft record staged; exact BGD VOA hotel condition and fixed stay/entry limit remain pending. |
| Jordan | MOI restricted-nationality list includes Bangladesh. BGD ordinary passport holders need prior approval through MOI e-services or a Jordanian sponsor. | Draft record staged; BGD stay, exact document checklist and prior-approval SLA remain pending. |
| Saudi Arabia | Bangladesh is outside the standard tourist eVisa list. Conditional eVisa/VOA routes exist for qualifying third-country visas/residence permits and GCC residents. | Draft record staged; Bangladesh consular fee/checklist remain pending. |
| United Arab Emirates | ICP's five-year family-group tourist visa is explicitly available to all nationalities but requires children under 18 and has defined financial, insurance, ticket, accommodation and fee conditions. | Family-only draft record staged; general individual route remains pending. |
| India | Generic Indian eVisa lists Bangladesh for some eVisa services, but the official e-Tourist fee schedule does not list Bangladesh. Bangladesh-specific IVAC portal lists a regular T-1 tourist sticker visa. | No e-Tourist record staged; confirm ordinary tourist sticker-visa intake with IVAC/HCI Dhaka. |
| Oman | A free 14-day tourist visa was announced for nationalities designated by the competent authority, but the public notice does not identify them. The Dhaka embassy confirms mutual exemption only for diplomatic/official/special/service passports. | No record staged; BGD designation and ROP tourist visa class remain unconfirmed. |

Batch 09 contains four pending-review/draft rows: Qatar, Jordan, Saudi Arabia and the UAE family-group route. India and Oman remain ledger-only blockers.

## Batch 10 discovery – Asia and adjacent routes

Seven further destinations were researched using official immigration, foreign-ministry, embassy and authorized-provider sources:

| Destination | Bangladesh ordinary-passport finding | Decision |
|---|---|---|
| Armenia | Visitor visa is invitation-only and must be issued at an Armenian diplomatic representation. No BGD eVisa/VOA. Maximum visa duration 120 days, extendable 60; AMD5,000 consular fee. Bangladesh is not in the temporary 111-nationality residence-permit exemption list. | **Verified research staged**; not imported to production. |
| Azerbaijan | Ordinary passports are not covered by the September 2026 diplomatic/official/service exemption. Bangladesh is not in the ASAN eVisa or airport VOA lists; sticker visa required through the New Delhi mission. Mission estimate 5–10 working days. | **Verified research staged**; local fee and full BGD checklist remain unconfirmed. |
| Pakistan | Bangladesh appears in the official eVisa country list. Tourist visa is an online prior-application route; first-time tourist visa is single entry for under three months. Dhaka High Commission lists sponsor/tour-operator, passport and bank-statement documents. | **Verified research staged**; fee is portal-calculated; Bangladesh eligibility for separate tourist VOA remains unset. |
| Georgia | Bangladesh is not visa-free. The eVisa country page requires a valid Schengen/OECD visa or residence permit, while the official FAQ says applicants without a supporting document may continue with that section blank. | **Pending review** because the two official eVisa instructions conflict. VFS Global is the Bangladesh application-centre partner; service fees are not government visa fees. |
| Bahrain | NPRA describes a one-year multi-entry eVisa for 212 nationalities and a separate VOA list, but the accessible official pages did not confirm Bangladesh's inclusion. | Ledger-only pending nationality-list confirmation. |
| Kuwait | The MOI tourist eVisa nationality list does not include Bangladesh. A conditional GCC-resident route exists for eligible professions, but the ordinary BGD tourist route without GCC residence was not established. | Ledger-only pending sponsor/embassy route confirmation. |
| Timor-Leste | Current Customs Authority guidance expressly permits nationals of countries other than Indonesia, Portugal and the United States to apply for VOA at Dili International Airport or Dili Sea Port. Bangladesh is in this general group; land-border entry requires prior authorization. | **Verified research staged**; USD30 cash, six-month passport validity, one blank page, onward ticket/departure funds, sufficient funds and accommodation. Standard stay duration is not stated. |

Batch 10 adds **four verified/publishable research rows** (Armenia, Azerbaijan, Pakistan and Timor-Leste) and one pending/draft row (Georgia). Bahrain and Kuwait remain ledger-only because Bangladesh-specific eligibility or the ordinary non-GCC route is not established.

## Staged backlog and import state

| Batch | Staged rows | Verified/publishable | Pending/draft |
|---|---:|---:|---:|
| 06–08 | 15 | 12 | 3 |
| 09 | 4 | 0 | 4 |
| 10 | 5 | 4 | 1 |
| **Total** | **24** | **16** | **8** |

These are repository-staged research rows, not production records. The 12 verified rows from batches 06–08 are missing from production. The current guarded import workflow intentionally inserts them as `pending_review` and unpublished; a separate database readback, verification and publication pass is still required. The four verified batch-10 rows have not been imported and need a reviewed import path. The eight pending/draft rows must remain unpublished.

## Live production and frontend parity

The latest confirmed backend audit remains 41 verified/published BGD tourism requirements, 0 pending, and 249 country rows. No successful production write occurred after that audit.

Frontend deployment run [43543539981](https://github.com/raadislam/unigo-frontend/actions/runs/43543539981) completed successfully on 2026-11-14:
- Public API snapshot returned HTTP 200 and 41 eligible country slugs.
- Public BGD tourism requirements API returned HTTP 200 and 41 verified/published records.
- All 41 country-detail API endpoints returned HTTP 200 and matched the directory API.
- Generated sitemap and static country pages had no missing or extra slugs.
- Generated directory links, page content and SEO metadata passed for all 41 countries.
- Live homepage, sitemap and directory returned HTTP 200; live sitemap matched all 41 slugs.
- Every one of the 41 live country pages returned HTTP 200 and passed content/SEO checks.
- Japan, Australia, Maldives and Viet Nam passed the live regression checks.

No new country page can be considered live until its database record is imported, verified and published, the public API includes it, and a static export is deployed with matching sitemap and live-page checks.

## Production import blocker

The guarded import run [43558042949](https://github.com/raadislam/unigo-backend/actions/runs/43558042949) failed on 2026-11-14 at 04:43 UTC. Its read-only preflight succeeded and confirmed that all 12 ready rows were missing (8 from batch 06–07 and 4 from batch 08). It stopped before database writes because the VPS did not provide the privileged MySQL connection required for an isolated restore test. No database dump, restore test or import occurred.

Do not bypass the restore test or weaken the backup gate. The remaining blocker is a supported privileged/isolated MySQL restore-test path on the VPS. Once that is available, rerun the guarded workflow, read back all imported records and their sources, then perform a separate verified publication pass.

This pull request contains research CSVs, ledger updates, validation and release documentation only. Batch 09–10 files and validation changes are excluded from automatic backend deployment. No production migration or production database write is part of this pull request.
