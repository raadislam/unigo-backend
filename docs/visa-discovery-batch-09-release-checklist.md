# UniGo visa discovery batch 09 – research and current release status

Checked: 2026-11-14  
Passport: Bangladesh ordinary passport  
Purpose: short-stay tourism

## Country discovery

The master discovery ledger contains 250 destination rows. The live country table contains 249 rows; Kosovo (XK) is the one master-list destination without a country row. The public directory currently represents 41 destinations. The other 208 country rows are present but not in the public BGD tourism directory, and 182 ledger destinations remain unstarted.

Six additional Asian destinations were researched in this run:

| Destination | Official-source finding | Decision |
|---|---|---|
| Qatar | Visit Qatar includes Bangladesh in the paid tourist-visa group and lists QAR100. Hayya A1 eVisa and fee-based VOA are described. Passport validity and return-ticket rules vary by route; health insurance is described as recommended. | Draft record staged. Exact BGD VOA hotel condition and fixed stay/entry limit remain pending. |
| Jordan | MOI restricted-nationality list includes Bangladesh. MOI FAQ says BGD ordinary passport holders need prior approval through MOI e-services or a Jordanian sponsor; fee/validity options are JOD40/60/120/200/350. | Draft record staged. BGD-specific stay, exact document checklist and prior-approval SLA remain pending. |
| Saudi Arabia | Bangladesh is outside the standard tourist eVisa list. KSA Visa confirms conditional eVisa/VOA routes for qualifying US/UK/Schengen visa holders, permanent residents and GCC residents. Digital fees and processing duration are published. | Draft record staged. Current Bangladesh consular route fee and checklist remain pending. |
| United Arab Emirates | ICP's five-year family-group tourist visa is explicitly available to all nationalities but requires children under 18 and has defined bank, insurance, ticket, accommodation and fee requirements. The separate individual five-year service card has conflicting annual stay clauses. | Family-only draft record staged; general individual tourist route remains pending. |
| India | The generic Indian eVisa list includes Bangladesh for some eVisa services, but the official e-Tourist fee schedule does not list Bangladesh. The Bangladesh-specific IVAC portal lists a regular T-1 tourist sticker visa; current appointment advisory limits slots to urgent/emergency cases. | No e-Tourist record staged. Confirm ordinary tourist sticker-visa intake with IVAC/HCI Dhaka. |
| Oman | A free 14-day tourist visa was announced for nationalities designated by the competent authority, but the public notice does not identify them. The Dhaka embassy confirms the mutual exemption is for diplomatic/official/special/service passports only. | No record staged. BGD designation and ROP tourist visa class remain unconfirmed. |

Four pending-review/draft rows are staged in batch 09: Qatar, Jordan, Saudi Arabia and the UAE family-group route. None is marked verified or published. India and Oman remain ledger-only blockers.

## Existing batch backlog

Batches 06–08 contain 15 destinations:
- 12 are marked verified/publishable in the full-audit CSVs.
- 3 remain pending/draft: Sri Lanka, Bhutan and Kazakhstan.

The current production import workflow's preflight on 2026-11-14 found the 12 ready rows missing from live BGD tourism requirements: batch 06–07 had 8 ready rows missing and batch 08 had 4 ready rows missing. It excluded the three pending-review rows as intended.

If the 12 ready rows are eventually full-audit imported and published, and the seven pending rows (three from batches 06–08 and four from batch 09) are staged without publication, the expected data state would be 60 total records, 53 verified/published and 7 pending/unpublished. This is a future target, not the current live state.

## Live production and frontend status

The last confirmed live state remains 41 verified/published BGD tourism requirements, 0 pending records and 249 country rows. No successful production write occurred after that audit.

Frontend deployment run [43543539981](https://github.com/raadislam/unigo-frontend/actions/runs/43543539981) completed successfully on 2026-11-14:
- The authoritative API snapshot contained 41 eligible country slugs.
- Generated static pages and sitemap had no missing or extra slugs.
- The live sitemap matched the same 41 slugs.
- All 41 live country pages returned HTTP 200 and passed content/SEO checks.
- The directory page rendered all 41 links.
- Japan, Australia, Maldives and Viet Nam passed the live regression checks.

## Production import blocker

The existing backend workflow [43558042949](https://github.com/raadislam/unigo-backend/actions/runs/43558042949) failed at 2026-11-14 04:43 UTC (10:43 Bangladesh time). Its preflight succeeded and found 12 ready records missing, but the privileged MySQL probe required for the isolated backup restore test returned non-zero. The workflow stopped before the database dump, restore test or any production data write. The log does not expose whether the underlying cause is sudo policy or MySQL root authentication; it only reports that a privileged MySQL connection is unavailable.

Do not bypass the restore test or use the application database account to force-create/drop a schema. Resolve the privileged isolated-restore mechanism with the VPS/database administrator, then rerun the existing guarded workflow. Also note that the current staged-import workflow leaves the 12 rows as pending_review/unpublished; a separate full-audit readback and verification/publication pass is still required before any of them can appear publicly. The seven pending rows must remain unpublished until their exact conditions are verified.

No production database rows were added or published in this run. This pull request contains research data, ledger updates and validation only; the new batch files and validation changes are excluded from automatic backend deployment.
