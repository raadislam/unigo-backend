# UniGo visa discovery batch 09 – research and release controls

Checked: 2026-11-14  
Passport: Bangladesh ordinary passport  
Purpose: short-stay tourism

## New-country discovery

| Destination | Finding | Staging decision |
|---|---|---|
| Qatar | Bangladesh is explicitly included in the paid tourist-visa nationality group. Visit Qatar lists a QAR 100 fee, Hayya A1 eVisa with hotel reservation, and a fee-based visa-on-arrival option. MOI general entry conditions cover passport validity, return/onward ticket and health insurance. | Draft record staged; exact Bangladesh VOA hotel condition and stay/entry limit remain pending. |
| Jordan | MOI FAQ explicitly lists Bangladesh ordinary passport holders as requiring prior approval. The MOI eVisa service lists JOD 40 / 60 / 120 / 200 / 350 categories; a 2026 notice confirms the current fee/validity structure. | Draft record staged; BGD-specific stay, document checklist and exemption mapping remain pending. |
| Saudi Arabia | Bangladesh is outside the standard tourist eVisa nationality list. Conditional eVisa/VOA routes exist for qualifying US/UK/Schengen visa holders, qualifying permanent residents and GCC residents; ordinary applicants use a Saudi mission or approved centre. | Draft record staged; current Dhaka consular fee and processing SLA remain unconfirmed. |
| India | Generic Indian eVisa eligibility page includes Bangladesh, but the e-Tourist list at an Indian mission omits Bangladesh; the Bangladesh-specific portal and IVAC describe regular T-1 tourist applications. IVAC's current advisory limits appointments to urgent/emergency cases. | No record staged; resolve the official category conflict and tourist intake first. |
| United Arab Emirates | General tourist permits are arranged through airlines, hotels or licensed tourism establishments. ICP's individual five-year service lists fees, documents and a two-day service duration, but stay clauses conflict; the family-group five-year route is explicitly all-nationalities but limited to qualifying family groups. | No record staged; confirm BGD ordinary-passport route and resolve the stay-rule conflict. |
| Oman | A free 14-day tourist visa was introduced on 3 August 2026 for nationalities designated by the competent authority; the published announcement does not identify those nationalities. The Dhaka embassy confirms the mutual exemption is for diplomatic/official/special/service passports only. | No record staged; BGD designation and ROP tourist visa class remain unconfirmed. |

The country discovery ledger records all six candidates and their official source URLs and blockers. Discovery, research completion, record staging, verification and publication remain separate stages.

## Existing researched backlog

Batches 06, 07 and 08 contain 15 destination records:
- 12 records are marked verified/publishable in the reviewed full-audit CSVs.
- 3 remain pending/draft: Sri Lanka, Bhutan and Kazakhstan.
- Batch 09 adds 3 pending/draft records: Qatar, Saudi Arabia and Jordan.

Expected database state after a successful gated import:
- 59 total visa-requirement records
- 53 verified and published
- 6 pending_review and unpublished
- 53 public-country-directory entries
- 249 country rows

The import scripts enforce these exact baseline and post-import totals. If the live audit differs, the workflow aborts rather than overwriting or reconciling records automatically.

## Production release controls

The pull request is intentionally staged without importing or deploying production data. The backend deploy workflow has a separate new-country import gate. It skips the new batches unless all of the following are configured:

1. Repository variable `UNIGO_VISA_IMPORT_BACKUP_VERIFIED=true`. Set this only after a complete database backup has been restored successfully into a separate test database.
2. Repository variable `UNIGO_VISA_IMPORT_BACKUP_PATH` pointing to a non-empty `.sql` or `.sql.gz` file under `/var/backups/unigo/`.
3. Repository variable `UNIGO_VISA_IMPORT_BACKUP_SHA256` containing the backup's 64-character SHA-256 digest.
4. Repository secret `UNIGO_FRONTEND_DISPATCH_TOKEN` with permission to dispatch and read workflow runs in `raadislam/unigo-frontend`.

The workflow checks the backup file and checksum, inspects `php artisan migrate:status`, exports a fresh audit, verifies that the target BGD tourism records do not already exist, dry-runs the seed batches and full-audit imports, then imports and reads every changed record and source. It does not run migrations, drop tables, truncate data, reset the database, or overwrite existing target records.

After API readback, the workflow dispatches the existing static frontend deployment and waits for its verification run. That frontend workflow checks the generated static pages, sitemap and deployed live pages. The new import data files and workflow changes are excluded from automatic push deployment; the release must be explicitly triggered after the backup controls are ready.
