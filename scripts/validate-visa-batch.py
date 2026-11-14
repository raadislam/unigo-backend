import csv
import json
import re
from pathlib import Path

root = Path(__file__).resolve().parents[1]
seed_path = root / "database/data/new_country_visa_batch.csv"
batch_path = root / "database/data/visa_full_audit_batch_06.csv"
ledger_path = root / "database/data/country_discovery_ledger.csv"
seeder_path = root / "database/seeders/ImportKosovoCountrySeeder.php"

def rows(path):
    with path.open("r", encoding="utf-8-sig", newline="") as handle:
        return list(csv.DictReader(handle))

seed = rows(seed_path)
batch = rows(batch_path)
ledger = rows(ledger_path)

expected = {"ID", "MU", "NP", "PH", "LK", "TR", "XK"}
keys = lambda row: (row.get("passport_iso3", ""), row.get("destination_iso2", ""), row.get("travel_purpose", ""))
seed_keys = [keys(row) for row in seed]
batch_keys = [keys(row) for row in batch]

if len(seed) != 9:
    raise SystemExit(f"Expected 9 idempotent seed rows; found {len(seed)}.")
if len(batch) != 7:
    raise SystemExit(f"Expected 7 full-audit batch rows; found {len(batch)}.")
if len(set(seed_keys)) != len(seed_keys):
    raise SystemExit("Seed CSV contains duplicate passport/destination/purpose keys.")
if len(set(batch_keys)) != len(batch_keys):
    raise SystemExit("Full-audit batch contains duplicate passport/destination/purpose keys.")
if {row["destination_iso2"] for row in batch} != expected:
    raise SystemExit("Full-audit batch destinations do not match the seven reviewed destinations.")
if not set(batch_keys).issubset(set(seed_keys)):
    raise SystemExit("Every full-audit batch record must have a country seed row.")

for row in batch:
    slug = row["destination_iso2"]
    if row["passport_iso3"] != "BGD" or row["travel_purpose"] != "tourism":
        raise SystemExit(f"{slug}: wrong passport or travel purpose.")
    if row["verification_status"] != "verified" or row["publish_status"] != "published" or row["full_audit"].lower() != "true":
        raise SystemExit(f"{slug}: full-audit publication status is invalid.")
    if row["visa_required"].lower() != "true" or row["visa_free_eligibility"].lower() != "false":
        raise SystemExit(f"{slug}: ordinary Bangladesh passport visa-required classification is inconsistent.")
    for field in ("visa_on_arrival_available", "evisa_available"):
        if row[field].lower() not in {"true", "false"}:
            raise SystemExit(f"{slug}: {field} must be an explicit boolean.")
    limits = {
        "visa_requirement_type": 255,
        "entry_count": 40,
        "application_fee": 120,
        "processing_time": 120,
        "official_immigration_source_name": 180,
        "cross_check_source_name": 180,
        "review_notes": 5000,
    }
    for field, maximum in limits.items():
        if len(row.get(field, "")) > maximum:
            raise SystemExit(f"{slug}: {field} exceeds {maximum} characters.")
    if not row.get("required_documents"):
        raise SystemExit(f"{slug}: required documents are empty.")
    if any(len(item) > 500 for item in row["required_documents"].split("|")):
        raise SystemExit(f"{slug}: a required-document item exceeds 500 characters.")
    for field in ("official_source_url", "cross_check_source_url"):
        if not re.fullmatch(r"https://\\S+", row.get(field, "")):
            raise SystemExit(f"{slug}: {field} is not a valid HTTPS URL.")
    for field in ("official_source_last_checked", "cross_check_source_last_checked"):
        if row[field] != "2026-11-14":
            raise SystemExit(f"{slug}: {field} is not dated 2026-11-14.")
    try:
        sources = json.loads(row["additional_sources"])
    except json.JSONDecodeError as exc:
        raise SystemExit(f"{slug}: invalid additional_sources JSON: {exc}")
    if not isinstance(sources, list) or not sources:
        raise SystemExit(f"{slug}: additional_sources must be a non-empty array.")
    for source in sources:
        if source.get("source_type") != "official" or not source.get("source_name") or not re.fullmatch(r"https://\\S+", source.get("source_url", "")):
            raise SystemExit(f"{slug}: additional source is incomplete or not official.")

if len(ledger) != 250:
    raise SystemExit(f"Expected 250 master destinations after Antarctica reconciliation; found {len(ledger)}.")
ledger_iso = [row.get("iso2", "") for row in ledger]
if len(set(ledger_iso)) != len(ledger_iso):
    raise SystemExit("Country discovery ledger contains duplicate ISO2 keys.")
ledger_by_iso = {row["iso2"]: row for row in ledger}
for iso in expected:
    item = ledger_by_iso.get(iso)
    if not item or item.get("discovery_status") != "research_complete_ready_for_batch_06":
        raise SystemExit(f"{iso}: ledger is not marked research-complete for batch 06.")
if ledger_by_iso["XK"].get("country_row_status") != "missing_from_country_table_add_in_batch_06":
    raise SystemExit("Kosovo must remain identified as a country-table addition in batch 06.")
for iso in ("KH", "LA"):
    if ledger_by_iso[iso].get("discovery_status") != "pending_official_eligibility_check":
        raise SystemExit(f"{iso}: unresolved country must remain pending.")
if ledger_by_iso["AQ"].get("discovery_status") != "not_applicable_special_destination":
    raise SystemExit("Antarctica must remain a special non-visa destination.")

seeder_text = seeder_path.read_text(encoding="utf-8")
for required in ("'iso2' => 'XK'", "'slug' => 'kosovo'", "'iso3' => null", "'is_published' => false"):
    if required not in seeder_text:
        raise SystemExit(f"Kosovo country seeder is missing {required}.")

print("Visa batch validation passed: 7 new destination records, 9 idempotent seed rows, 250 ledger destinations.")
print("All BGD tourism keys, field limits, official-source metadata, JSON sources and pending blockers passed.")
