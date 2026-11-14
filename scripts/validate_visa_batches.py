import csv
import json
import sys
from datetime import date, timedelta
from pathlib import Path

root = Path(__file__).resolve().parents[1]
files = [
    root / "database/data/visa_full_audit_batch_06.csv",
    root / "database/data/visa_full_audit_batch_07.csv",
    root / "database/data/visa_full_audit_batch_08.csv",
    root / "database/data/visa_full_audit_batch_09.csv",
    root / "database/data/visa_full_audit_batch_10.csv",
    root / "database/data/visa_full_audit_batch_12.csv",
    root / "database/data/visa_full_audit_batch_13.csv",
    root / "database/data/visa_full_audit_batch_14.csv",
    root / "database/data/visa_full_audit_batch_15.csv",
    root / "database/data/visa_full_audit_batch_16.csv",
    root / "database/data/visa_full_audit_batch_17.csv",
    root / "database/data/visa_full_audit_batch_18.csv",
    root / "database/data/visa_full_audit_batch_19.csv",
    root / "database/data/visa_full_audit_batch_20.csv",
    root / "database/data/visa_full_audit_batch_28.csv",
]
seed_files = [
    root / "database/data/new_country_visa_batch_06_07.csv",
    root / "database/data/new_country_visa_batch_08.csv",
    root / "database/data/new_country_visa_batch_09.csv",
    root / "database/data/new_country_visa_batch_10.csv",
    root / "database/data/new_country_visa_batch_12.csv",
    root / "database/data/new_country_visa_batch_13.csv",
    root / "database/data/new_country_visa_batch_14.csv",
    root / "database/data/new_country_visa_batch_15.csv",
    root / "database/data/new_country_visa_batch_16.csv",
    root / "database/data/new_country_visa_batch_17.csv",
    root / "database/data/new_country_visa_batch_18.csv",
    root / "database/data/new_country_visa_batch_19.csv",
    root / "database/data/new_country_visa_batch_20.csv",
    root / "database/data/new_country_visa_batch_28.csv",
]
required = {
    "passport_iso3", "passport_nationality", "destination_iso2", "destination_name",
    "travel_purpose", "visa_requirement_type", "visa_required", "visa_free_eligibility",
    "visa_on_arrival_available", "evisa_available", "maximum_permitted_stay", "entry_count",
    "passport_validity_rule", "blank_passport_pages_rule", "return_ticket_rule",
    "proof_of_funds_rule", "accommodation_rule", "travel_insurance_rule",
    "yellow_fever_or_health_rule", "transit_rule", "application_channel", "application_fee",
    "processing_time", "required_documents", "official_source_url",
    "official_immigration_source_name", "official_source_last_checked",
    "cross_check_source_url", "cross_check_source_name", "cross_check_source_last_checked",
    "verification_status", "publish_status", "review_notes", "full_audit", "additional_sources",
}
limits = {
    "visa_requirement_type": 255,
    "entry_count": 40,
    "maximum_permitted_stay": 60000,
    "passport_validity_rule": 10000,
    "blank_passport_pages_rule": 10000,
    "return_ticket_rule": 10000,
    "proof_of_funds_rule": 10000,
    "accommodation_rule": 10000,
    "travel_insurance_rule": 10000,
    "yellow_fever_or_health_rule": 10000,
    "transit_rule": 10000,
    "application_channel": 60000,
    "application_fee": 120,
    "processing_time": 120,
}
today = date.today()
earliest_checked = today - timedelta(days=3)
latest_checked = today + timedelta(days=1)
seen = set()
all_rows = []
errors = []

for path in files:
    if not path.exists():
        errors.append(f"{path.name}: required audit file is missing")
        continue
    with path.open(encoding="utf-8-sig", newline="") as handle:
        reader = csv.DictReader(handle)
        missing = required - set(reader.fieldnames or [])
        if missing:
            errors.append(f"{path.name}: missing columns {sorted(missing)}")
        rows = list(reader)
    if not rows:
        errors.append(f"{path.name}: no rows")
    for line, row in enumerate(rows, 2):
        key = (row.get("passport_iso3"), row.get("destination_iso2"), row.get("travel_purpose"))
        if key in seen:
            errors.append(f"{path.name}:{line}: duplicate key {key}")
        seen.add(key)
        all_rows.append(row)
        if row.get("passport_iso3") != "BGD" or row.get("travel_purpose") != "tourism":
            errors.append(f"{path.name}:{line}: not BGD tourism")
        if row.get("full_audit", "").lower() != "true":
            errors.append(f"{path.name}:{line}: full_audit must be true")
        if row.get("verification_status") not in {"verified", "pending_review"}:
            errors.append(f"{path.name}:{line}: invalid verification_status")
        if row.get("publish_status") not in {"published", "draft"}:
            errors.append(f"{path.name}:{line}: invalid publish_status")
        if row.get("publish_status") == "published" and row.get("verification_status") != "verified":
            errors.append(f"{path.name}:{line}: published record is not verified")
        if row.get("verification_status") == "pending_review" and row.get("publish_status") != "draft":
            errors.append(f"{path.name}:{line}: pending record must remain draft")
        if not row.get("required_documents", "").strip():
            errors.append(f"{path.name}:{line}: required_documents is empty")
        for field, limit in limits.items():
            if len(row.get(field, "") or "") > limit:
                errors.append(f"{path.name}:{line}: {field} exceeds {limit} characters")
        if len(row.get("review_notes", "") or "") > 5000:
            errors.append(f"{path.name}:{line}: review_notes exceeds 5000 characters")
        for field in ("official_source_url", "cross_check_source_url"):
            value = row.get(field, "") or ""
            if not value.startswith("https://") or len(value) > 2000:
                errors.append(f"{path.name}:{line}: {field} must be a valid HTTPS URL no longer than 2000 characters")
        for field in ("official_immigration_source_name", "cross_check_source_name"):
            if len(row.get(field, "") or "") > 180:
                errors.append(f"{path.name}:{line}: {field} exceeds 180 characters")
        for field in ("official_source_last_checked", "cross_check_source_last_checked"):
            value = (row.get(field, "") or "")[:10]
            try:
                checked = date.fromisoformat(value)
                if checked < earliest_checked or checked > latest_checked:
                    errors.append(f"{path.name}:{line}: {field} is stale or unexpectedly future-dated")
            except ValueError:
                errors.append(f"{path.name}:{line}: {field} is not an ISO date")
        docs = [item.strip() for item in row.get("required_documents", "").split("|") if item.strip()]
        if not docs or any(len(item) > 500 for item in docs):
            errors.append(f"{path.name}:{line}: invalid required_documents")
        try:
            sources = json.loads(row.get("additional_sources", ""))
            if not isinstance(sources, list) or len(sources) < 2:
                raise ValueError("expected at least two sources")
            for source in sources:
                if not isinstance(source, dict):
                    raise ValueError("source must be an object")
                if source.get("source_type") not in {"official", "provider"}:
                    raise ValueError("source_type must be official or provider")
                if not source.get("source_name", "").strip() or len(source["source_name"]) > 180:
                    raise ValueError("source_name is missing or exceeds 180 characters")
                if not source.get("source_url", "").startswith("https://") or len(source["source_url"]) > 2000:
                    raise ValueError("source_url must be HTTPS and no longer than 2000 characters")
                notes = source.get("verification_notes")
                if notes is not None and len(notes) > 5000:
                    raise ValueError("verification_notes exceeds 5000 characters")
                if path.name == "visa_full_audit_batch_28.csv" and not (notes or "").strip():
                    raise ValueError("batch 28 verification_notes is required")
        except Exception as exc:
            errors.append(f"{path.name}:{line}: invalid additional_sources: {exc}")

seed_keys = set()
for seed in seed_files:
    if not seed.exists():
        errors.append(f"{seed.name}: required seed file is missing")
        continue
    with seed.open(encoding="utf-8-sig", newline="") as handle:
        current = list(csv.DictReader(handle))
    for line, row in enumerate(current, 2):
        key = (row.get("passport_iso3"), row.get("destination_iso2"), row.get("travel_purpose"))
        if key in seed_keys:
            errors.append(f"{seed.name}:{line}: duplicate seed key {key}")
        seed_keys.add(key)

audit_keys = {
    (row.get("passport_iso3"), row.get("destination_iso2"), row.get("travel_purpose"))
    for row in all_rows
}
if seed_keys != audit_keys:
    errors.append(
        f"seed/full-audit key mismatch; seed-only={sorted(seed_keys - audit_keys)} "
        f"audit-only={sorted(audit_keys - seed_keys)}"
    )

if errors:
    print("VALIDATION FAILED")
    print("\n".join(errors))
    sys.exit(1)

print(
    f"VALIDATION PASSED: {len(all_rows)} unique BGD tourism records; "
    f"{sum(row['publish_status'] == 'published' for row in all_rows)} verified/publishable; "
    f"{sum(row['publish_status'] == 'draft' for row in all_rows)} pending; seed/full-audit keys match."
)
