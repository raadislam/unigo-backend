# UniGo visa discovery batch 09 – research and current release status

Checked: 2026-11-14  
Passport: Bangladesh ordinary passport  
Purpose: short-stay tourism

## Country discovery

The master discovery ledger contains 250 destination rows. The live country table contains 249 rows; Kosovo (XK) is the one master-list destination without a country row. The public directory currently represents 41 destinations. The other 208 country rows are present but not in the public BGD tourism directory, and 182 ledger destinations remain unstarted.

Six additional Asian destinations were researched in this run:

| Destination | Official-source finding | Decision |
|---|---|---|
| Qatar | Visit Qatar includes Bangladesh in the paid tourist-visa nationality group and lists QAR 100. It describes the Hayya A1 tourist eVisa and a fee-based visa-on-arrival route. MOI general entry conditions cover passport validity, return/onward ticket and health insurance. | Draft BGD tourism record staged. Exact BGD VOA accommodation condition and fixed stay/entry limit are not explicit; remain pending. |
| Jordan | Jordan MOI FAQ explicitly lists Bangladesh ordinary passport holders as requiring prior approval. MOI eVisa service lists JOD 40 / 60 / 120 / 200 / 350 categories, and a 2026 notice confirms the current fee/validity structure. | Draft BGD tourism record staged. BGD-specific stay, document checklist and conditional-exemption mapping remain pending. |
| Saudi Arabia | Bangladesh is outside the standard tourist eVisa eligible-nationality list. Conditional eVisa/VOA routes exist for qualifying US/UK/Schengen visa holders, qualifying permanent residents and GCC residents; ordinary applicants use a Saudi mission or approved centre. | Draft BGD tourism record staged. Current Dhaka consular fee and processing SLA remain unconfirmed. |
| India | Generic Indian eVisa eligibility includes Bangladesh, but an Indian mission's e-Tourist list omits Bangladesh; the Bangladesh-specific visa portal and IVAC describe regular T-1 tourist applications. IVAC's current advisory limits appointments to urgent/emergency cases. | No record staged. Resolve the category conflict and tourist intake first. |
| United Arab Emirates | General tourist permits are arranged through airlines, hotels or licensed tourism establishments. ICP's individual five-year service lists fees, documents and a two-day service duration, but stay clauses conflict; the all-nationalities family-group route is restricted to qualifying families. | No record staged. Confirm BGD ordinary-passport route and resolve the stay-rule conflict. |
| Oman | A free 14-day tourist visa was introduced on 3 August 2026 for nationalities designated by the competent authority; the published announcement does not identify those nationalities. The Dhaka embassy says the mutual exemption is for diplomatic/official/special/service passports only. | No record staged. BGD designation and ROP tourist visa class remain unconfirmed. |

Three pending-review/draft rows are staged in batch 09: Qatar, Jordan and Saudi Arabia. They are not marked verified and are not publishable. India, UAE and Oman remain ledger-only research blockers.

## Existing batch backlog

Batches 06–08 contain 15 destinations:
- 12 are marked verified/publishable in the full-audit CSVs.
- 3 remain pending/draft: Sri Lanka, Bhutan and Kazakhstan.

The current production import workflow's preflight on 2026-11-14 found the 12 ready rows missing from the live BGD tourism requirements: batch 06–07 had 8 ready rows missing and batch 08 had 4 ready rows missing. It excluded the three pending-review rows as intended.

## Live production and frontend status

The live public directory remains at 41 destinations. Frontend deployment run [43543539981](https://github.com/raadislam/unigo-frontend/actions/runs/43543539981) completed successfully on 2026-11-14:
- The authoritative API snapshot contained 41 eligible country slugs.
- Generated static pages and sitemap had no missing or extra slugs.
- The live sitemap matched the same 41 slugs.
- All 41 live country pages returned HTTP 200 and passed content/SEO checks.
- The directory page rendered all 41 links.
- Japan, Australia, Maldives and Viet Nam passed the live regression checks.

## Production import blocker

The existing backend workflow [43558042949](https://github.com/raadislam/unigo-backend/actions/runs/43558042949) failed at 2026-11-14 04:43 UTC (10:43 Bangladesh time). Its preflight succeeded and found 12 ready records missing, but the privileged MySQL probe required for the isolated backup restore test returned non-zero. The workflow stopped before the database dump, restore test or any production data write. The log does not expose whether the underlying cause is sudo policy or MySQL root authentication; it only reports that a privileged MySQL connection is unavailable.

Do not bypass the restore test or use the application database account to force-create/drop a schema. Resolve the privileged isolated-restore mechanism with the VPS/database administrator, then rerun the existing guarded workflow. Also note that the current staged-import workflow leaves the 12 rows as pending_review/unpublished; a separate full-audit readback and verification/publication pass is still required before any of them can appear publicly. The three pending rows in batches 06–08 and the three draft rows in batch 09 must remain unpublished.

No production database rows were added or published in this run. This pull request contains research data, ledger updates and validation only; the new batch files and validation changes are excluded from automatic backend deployment.
