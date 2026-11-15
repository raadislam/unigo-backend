import csv
import json
import re
import subprocess
import textwrap
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
SEED = ROOT / "database/data/new_country_visa_batch_45.csv"
AUDIT = ROOT / "database/data/visa_full_audit_batch_45.csv"
SEEDER = ROOT / "database/seeders/TristanDaCunhaCountryBackfillSeeder.php"
IMPORT_SEEDER = ROOT / "database/seeders/ImportMajorVisaRequirementsSeeder.php"
READBACK = ROOT / "scripts/batch45_readback.php"
REPAIR = ROOT / "scripts/batch45_repair_source.php"
WORKFLOW = ROOT / ".github/workflows/import-verified-country-batch-45.yml"

def read_one(path):
    with path.open(encoding="utf-8-sig", newline="") as handle:
        reader = csv.DictReader(handle)
        headers = reader.fieldnames or []
        if len(headers) != len(set(headers)):
            raise SystemExit(f"{path.name}: duplicate CSV headers are not permitted")
        required = {"destination_name", "destination_iso2", "passport_nationality", "passport_iso3", "travel_purpose", "visa_requirement_type", "visa_required", "visa_free_eligibility", "visa_on_arrival_available", "evisa_available", "maximum_permitted_stay", "entry_count", "application_fee", "processing_time", "required_documents", "official_source_url", "official_immigration_source_name", "official_source_last_checked", "cross_check_source_url", "cross_check_source_name", "cross_check_source_last_checked", "verification_status"}
        missing = required - set(headers)
        if missing:
            raise SystemExit(f"{path.name}: missing required columns {sorted(missing)}")
        rows = list(reader)
    if len(rows) != 1:
        raise SystemExit(f"{path.name}: expected exactly one allowlisted row, found {len(rows)}")
    return rows[0]

seed = read_one(SEED)
row = read_one(AUDIT)
for label, item in (("seed", seed), ("audit", row)):
    if item.get("passport_iso3") != "BGD" or item.get("destination_iso2") != "TA" or item.get("travel_purpose") != "tourism":
        raise SystemExit(f"{label}: only the TA / BGD / tourism key is permitted")
    if not item.get("official_source_url", "").startswith("https://") or not item.get("cross_check_source_url", "").startswith("https://"):
        raise SystemExit(f"{label}: primary and cross-check sources must be HTTPS")
    if item.get("official_source_last_checked") != "2026-11-15" or item.get("cross_check_source_last_checked") != "2026-11-15":
        raise SystemExit(f"{label}: source checked dates must be 2026-11-15")

if seed.get("verification_status") != "pending_review":
    raise SystemExit("Seed import must create the record as pending_review before full-audit verification.")
if row.get("verification_status") != "verified" or row.get("publish_status") != "published" or row.get("full_audit") != "true":
    raise SystemExit("The full-audit row must be explicitly verified, publication-approved and full_audit=true.")
if row.get("visa_required") != "false" or row.get("visa_free_eligibility") != "false" or row.get("visa_on_arrival_available") != "false" or row.get("evisa_available") != "false":
    raise SystemExit("The no-visa / prior-permission route must not be misclassified as visa-free, VOA or eVisa.")
if "no visa" not in row.get("visa_requirement_type", "").lower() or "permission" not in row.get("visa_requirement_type", "").lower():
    raise SystemExit("Requirement type must state no visa and prior territorial permission.")
if "no universal maximum" not in row.get("maximum_permitted_stay", "").lower():
    raise SystemExit("Do not invent a universal stay limit.")
if "GBP 30" not in row.get("application_fee", "") or "GBP 15" not in row.get("application_fee", ""):
    raise SystemExit("Main-island landing charges must distinguish cruise and yacht passengers.")
if len(row.get("visa_requirement_type", "")) > 50 or len(row.get("maximum_permitted_stay", "")) > 120 or len(row.get("application_fee", "")) > 120 or len(row.get("processing_time", "")) > 120 or len(row.get("entry_count", "")) > 40 or len(row.get("application_channel", "")) > 180:
    raise SystemExit("A field exceeds its actual database column length.")
if "tdcenquiries@tdc-gov.com" not in row.get("application_channel", ""):
    raise SystemExit("Official advance-approval email channel is missing.")
documents = [part.strip() for part in row.get("required_documents", "").split("|") if part.strip()]
if len(documents) < 6 or any(len(part) > 500 for part in documents):
    raise SystemExit("Required document checklist is incomplete or exceeds the schema limit.")

photo = json.loads(row.get("photo_specifications", ""))
if not isinstance(photo, dict) or photo.get("status") != "not_specified" or photo.get("checked_at") != "2026-11-15" or not photo.get("source_name") or not photo.get("source_url", "").startswith("https://"):
    raise SystemExit("Photo status must remain not_specified with official source metadata; do not invent a photo size.")
if photo.get("dimensions_mm") or photo.get("digital_dimensions_px"):
    raise SystemExit("No photo dimensions may be invented for this route.")

checklists = json.loads(row.get("applicant_checklists", ""))
expected_types = {"employee", "self_employed_or_business_owner", "student", "retired"}
if not isinstance(checklists, list) or {item.get("applicant_type") for item in checklists} != expected_types:
    raise SystemExit("Four applicant-type checklists are required.")
for checklist in checklists:
    if not checklist.get("source_name") or not checklist.get("source_url", "").startswith("https://") or checklist.get("checked_at") != "2026-11-15":
        raise SystemExit("Applicant checklist source metadata is incomplete.")
    if not isinstance(checklist.get("documents"), list) or len(checklist["documents"]) < 6:
        raise SystemExit("Each applicant checklist must contain the official visitor document set.")
    for document in checklist["documents"]:
        if not document.get("name") or document.get("status") not in {"required", "conditional", "if_applicable"}:
            raise SystemExit("Applicant checklist document is invalid.")

extra = json.loads(row.get("additional_sources", ""))
if not isinstance(extra, list) or len(extra) < 2:
    raise SystemExit("At least two additional official sources are required.")
for source in extra:
    if source.get("source_type") != "official" or not source.get("source_name") or not source.get("source_url", "").startswith("https://") or source.get("source_last_checked_at") != "2026-11-15" or not source.get("verification_notes"):
        raise SystemExit("Additional source metadata must be official, dated and explained.")

seeder = SEEDER.read_text(encoding="utf-8")
import_seeder = IMPORT_SEEDER.read_text(encoding="utf-8")
readback = READBACK.read_text(encoding="utf-8")
repair = REPAIR.read_text(encoding="utf-8")
for marker in ("['iso2' => 'TA']", "'slug' => 'tristan-da-cunha'", "'iso3' => null", "'is_published' => false"):
    if marker not in seeder:
        raise SystemExit(f"Country backfill seeder is missing safe territory handling: {marker}")

if "$row['cross_check_source_name']" not in import_seeder or "$row['cross_check_source_last_checked']" not in import_seeder:
    raise SystemExit("Country seed importer must preserve the named cross-check source and its checked date.")
if "APP_ROOT" not in readback or "/api/visa-requirements/TA" not in readback or "BATCH45_READBACK_ERROR" not in readback:
    raise SystemExit("Batch 45 readback script is missing the scoped TA database/API checks.")
if "$source->source_type === 'official'" not in readback or "data_get($source, 'source_type') === 'official'" not in readback or "official')->count() >= 4" not in readback:
    raise SystemExit("Batch 45 readback must require the government cross-check to be public as an official source.")
if "https://www.tristandc.com/visitsadmin.php" not in repair or "'source_type' => 'official'" not in repair or "TRISTAN_CROSS_CHECK_SOURCE_REPAIRED" not in repair or "where('passport_nationality_iso3', 'BGD')" not in repair or "where('iso2', 'TA')" not in repair:
    raise SystemExit("Batch 45 source repair is not restricted to the official TA / BGD / tourism cross-check.")
if any(token in repair.lower() for token in ("truncate table", "drop table", "delete from visa_requirements")):
    raise SystemExit("Batch 45 source repair must only update the one reviewed source classification.")
if any(token in readback.lower() for token in ("truncate table", "drop table", "delete from visa_requirements")):
    raise SystemExit("Batch 45 readback script must remain read-only.")

workflow = WORKFLOW.read_text(encoding="utf-8")
for forbidden in ("migrate:fresh", "migrate:reset", "key:generate", "DROP TABLE", "TRUNCATE TABLE"):
    if forbidden.lower() in workflow.lower():
        raise SystemExit(f"Batch 45 workflow contains a forbidden destructive operation: {forbidden}")
for required in ("mysqldump", "gzip -t", "docker run", "--dry-run", "--unpublish-only", "visa:requirements-audit", "batch45-readback.php", "batch45-repair-source.php", "TRISTAN_CROSS_CHECK_SOURCE_REPAIRED", "staging-source-repair.json", "TA_PARTIAL_RECOVERY_ALLOWED", "requirementPending", "BATCH45_REIMPORT_MARKER_CLEARED", "PRODUCTION_PREWRITE_COUNTS_RECONFIRMED", "file=sys.stderr", "fwrite(STDERR"):
    if required not in workflow:
        raise SystemExit(f"Batch 45 workflow is missing a required safety stage: {required}")

for stage in ("preflight", "backup"):
    pattern = rf"(?ms)^\s*cat > /tmp/batch45-{stage}\.php <<'PHP'\n(.*?)^\s*PHP$"
    match = re.search(pattern, workflow)
    if not match:
        raise SystemExit(f"Batch 45 workflow is missing the embedded {stage} PHP script.")
    source = textwrap.dedent(match.group(1)) + "\n"
    target = Path(f"/tmp/unigo-batch45-{stage}.php")
    target.write_text(source, encoding="utf-8")
    subprocess.run(["php", "-l", str(target)], check=True)

print("BATCH45_ALLOWLIST_VALIDATED TA")
print("BATCH45_ROWS 1")
print("BATCH45_SEED_ROWS 1")
print("BATCH45_SOURCE_CHECKED 2026-11-15")
