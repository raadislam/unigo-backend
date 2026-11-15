import csv
import json
import re
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
SEED = ROOT / "database/data/new_country_visa_batch_46.csv"
AUDIT = ROOT / "database/data/visa_full_audit_batch_46.csv"
SEEDER = ROOT / "database/seeders/AscensionIslandCountryBackfillSeeder.php"
READBACK = ROOT / "scripts/batch46_readback.php"
REPAIR = ROOT / "scripts/batch46_repair_source.php"
WORKFLOW = ROOT / ".github/workflows/import-verified-country-batch-46.yml"

def rows(path):
    with path.open(encoding="utf-8-sig", newline="") as handle:
        return list(csv.DictReader(handle))

seed_rows = rows(SEED)
audit_rows = rows(AUDIT)
if len(seed_rows) != 1 or len(audit_rows) != 1:
    raise SystemExit("Batch 46 must contain exactly one AC/BGD/tourism seed row and one full-audit row.")

seed = seed_rows[0]
row = audit_rows[0]
for item in (seed, row):
    if (item.get("destination_name"), item.get("destination_iso2"), item.get("passport_nationality"), item.get("passport_iso3"), item.get("travel_purpose")) != ("Ascension Island", "AC", "Bangladesh", "BGD", "tourism"):
        raise SystemExit("Batch 46 identity must be exactly Ascension Island / AC / Bangladesh / BGD / tourism.")
    if item.get("visa_requirement_type") != "eVisa required" or item.get("visa_required") != "true" or item.get("visa_free_eligibility") != "false" or item.get("visa_on_arrival_available") != "false" or item.get("evisa_available") != "true":
        raise SystemExit("Batch 46 entry flags must describe the official visitor eVisa route.")
    if "3 months" not in item.get("maximum_permitted_stay", "") or "12-month" not in item.get("maximum_permitted_stay", ""):
        raise SystemExit("Batch 46 visitor stay must preserve the single-entry 3-month and cumulative 12-month limits.")
    if "six months" not in item.get("passport_validity_rule", "").lower():
        raise SystemExit("Batch 46 must preserve the six-month passport validity rule.")
    if "GBP 40" not in item.get("application_fee", "") or "GBP 60" not in item.get("application_fee", ""):
        raise SystemExit("Batch 46 must preserve the GBP 40 standard and GBP 60 fast-track visitor fees.")
    if "21 days" not in item.get("processing_time", "") or "6 weeks" not in item.get("processing_time", ""):
        raise SystemExit("Batch 46 must distinguish AIG processing targets from guarantees.")
    if item.get("official_source_url") != "https://www.ascension-visas.com/faq" or item.get("official_source_last_checked") != "2026-11-15":
        raise SystemExit("Batch 46 primary AIG FAQ source metadata is missing or stale.")
    if item.get("cross_check_source_url") != "https://www.ascension-visas.com/useful-information" or item.get("cross_check_source_last_checked") != "2026-11-15":
        raise SystemExit("Batch 46 AIG eligibility cross-check source metadata is missing or stale.")
    if "may be required" not in item.get("transit_rule", "").lower() or "Bangladesh" not in item.get("transit_rule", ""):
        raise SystemExit("Batch 46 must preserve the conditional UK multiple-entry visa rule for Bangladesh.")
    if "500,000" not in item.get("travel_insurance_rule", ""):
        raise SystemExit("Batch 46 must preserve the mandatory GBP 500,000 air-evacuation cover.")
    if not item.get("required_documents"):
        raise SystemExit("Batch 46 required documents must not be empty.")

if seed.get("verification_status") != "pending_review":
    raise SystemExit("The seed row must remain pending until the full-audit importer verifies it.")
if row.get("verification_status") != "verified" or row.get("publish_status") != "published" or row.get("full_audit") != "true" or row.get("effective_from") != "2026-11-15":
    raise SystemExit("The allowlisted full-audit row must be verified, publication-approved and effective from the checked date.")

photo = json.loads(row.get("photo_specifications") or "{}")
if photo.get("status") != "no_fixed_size_published" or photo.get("source_url") != "https://www.ascension-visas.com/faq" or photo.get("checked_at") != "2026-11-15" or photo.get("file_format") != "JPEG":
    raise SystemExit("Batch 46 photo record must confirm the official no-fixed-size JPEG guidance with source metadata.")
if photo.get("dimensions_mm") or photo.get("digital_dimensions_px") or photo.get("file_size"):
    raise SystemExit("Batch 46 must not invent photo dimensions or file-size limits.")
if not photo.get("biometric_capture_may_be_required"):
    raise SystemExit("Batch 46 must distinguish possible biometrics from VAC photo capture.")

checklists = json.loads(row.get("applicant_checklists") or "[]")
expected_types = {"employee", "self_employed_or_business_owner", "student", "retired"}
if len(checklists) != 4 or {x.get("applicant_type") for x in checklists} != expected_types:
    raise SystemExit("Batch 46 must include all four supported applicant checklist types.")
for checklist in checklists:
    if checklist.get("source_url") != "https://www.ascension-visas.com/faq" or checklist.get("checked_at") != "2026-11-15" or len(checklist.get("documents", [])) < 6:
        raise SystemExit("Batch 46 applicant checklists need source metadata and route-specific document entries.")
    if not any(d.get("status") == "conditional" and "multiple-entry UK visa" in d.get("name", "") for d in checklist.get("documents", [])):
        raise SystemExit("The UK multiple-entry visa caveat must remain conditional, not universally mandatory.")

additional = json.loads(row.get("additional_sources") or "[]")
if len(additional) != 2 or any(x.get("source_type") != "official" or not x.get("source_url", "").startswith("https://") or x.get("source_last_checked_at") != "2026-11-15" for x in additional):
    raise SystemExit("Batch 46 must include two dated official additional sources.")
if not any("gov.uk/guidance/immigration-rules/immigration-rules-appendix-visitor-visa-national-list" in x.get("source_url", "") for x in additional):
    raise SystemExit("Batch 46 must include the UK Home Office visa-national list supporting the conditional caveat.")

seeder = SEEDER.read_text(encoding="utf-8")
readback = READBACK.read_text(encoding="utf-8")
repair = REPAIR.read_text(encoding="utf-8")
workflow = WORKFLOW.read_text(encoding="utf-8")

for marker in ("'iso2' => 'AC'", "'iso3' => null", "'slug' => 'ascension-island'", "'is_published' => false"):
    if marker not in seeder:
        raise SystemExit(f"Ascension country seeder is missing {marker}.")
for marker in ("https://www.ascension-visas.com/useful-information", "Ascension Island Government — eVisa Eligibility Information", "'source_type' => 'official'", "ASCENSION_CROSS_CHECK_SOURCE_REPAIRED", "where('passport_nationality_iso3', 'BGD')", "where('iso2', 'AC')"):
    if marker not in repair:
        raise SystemExit(f"Ascension source repair is missing {marker}.")
for marker in ("Country::query()->count() === 252", "$records->count() === 137", "$published === 135", "$pending === 2", "'AC'", "'eVisa required'", "no_fixed_size_published", "official')->count() >= 3", "public_list_total", "meta.total') === 135"):
    if marker not in readback:
        raise SystemExit(f"Ascension readback is missing {marker}.")
if "source_type' => 'official'" not in repair or "TRUNCATE TABLE" in repair.upper() or "DROP TABLE" in repair.upper() or "DELETE FROM" in repair.upper():
    raise SystemExit("Ascension source repair must only perform the exact source classification update.")
for marker in ("mysqldump", "gzip -t", "docker run", "--dry-run", "--unpublish-only", "visa:requirements-audit", "batch46_readback.php", "batch46_repair_source.php", "ASCENSION_CROSS_CHECK_SOURCE_REPAIRED", "staging-source-repair.json", "AC_PARTIAL_RECOVERY_ALLOWED", "requirementPending", "BATCH46_REIMPORT_MARKER_CLEARED", "file=sys.stderr", "fwrite(STDERR"):
    if marker not in workflow:
        raise SystemExit(f"Batch 46 workflow is missing safety marker {marker}.")
for marker in ("AscensionIslandCountryBackfillSeeder.php", "verified_seed_batch_46.csv", "verified_full_audit_batch_46.csv", "batch46_readback.php", "batch46_repair_source.php"):
    if marker not in workflow:
        raise SystemExit(f"Batch 46 immutable artifact allowlist is missing {marker}.")
for forbidden in ("migrate:fresh", "migrate:reset", "key:generate", "DROP TABLE", "TRUNCATE TABLE", "DELETE FROM"):
    if forbidden.lower() in workflow.lower():
        raise SystemExit(f"Batch 46 workflow contains a forbidden destructive operation: {forbidden}.")
if re.search(r"source_count\)\s*>=\s*4", workflow):
    raise SystemExit("Batch 46 source count must match its three independently checked official sources.")

print("BATCH46_VALIDATION_OK: one AC/BGD/tourism row, 3 official sources, 4 applicant checklists, no-fixed-size JPEG photo status, scoped backup/restore/dry-run/import/readback workflow.")
