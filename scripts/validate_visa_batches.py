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
    root / "database/data/visa_full_audit_batch_29.csv",
    root / "database/data/visa_full_audit_batch_33.csv",
    root / "database/data/visa_full_audit_batch_36.csv",
    root / "database/data/visa_full_audit_batch_38.csv",
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
    root / "database/data/new_country_visa_batch_29.csv",
    root / "database/data/new_country_visa_batch_33.csv",
    root / "database/data/new_country_visa_batch_36.csv",
    root / "database/data/new_country_visa_batch_38.csv",
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
    if path.name == "visa_full_audit_batch_38.csv":
        codes = {row.get("destination_iso2") for row in rows}
        if len(rows) != 2 or codes != {"AR", "BS"}:
            errors.append(f"{path.name}: batch 38 must contain exactly AR and BS")
    if path.name == "visa_full_audit_batch_12.csv":
        for batch41_row in rows:
            iso2 = batch41_row.get("destination_iso2")
            if iso2 not in {"UG", "ZW"}:
                continue
            if batch41_row.get("verification_status") != "verified" or batch41_row.get("publish_status") != "published" or (batch41_row.get("notes") or "").strip():
                errors.append(f"{path.name}: batch 41 {iso2} must be verified, publish-approved and have no unmapped notes")
            schema_limits = {"visa_requirement_type": 50, "maximum_permitted_stay": 120, "entry_count": 40, "application_channel": 180, "application_fee": 120, "processing_time": 120}
            for field, limit in schema_limits.items():
                if len(batch41_row.get(field, "") or "") > limit:
                    errors.append(f"{path.name}: batch 41 {iso2} {field} exceeds schema limit {limit}")
            if batch41_row.get("official_source_last_checked") != "2026-11-15" or batch41_row.get("cross_check_source_last_checked") != "2026-11-15":
                errors.append(f"{path.name}: batch 41 {iso2} source dates must be 2026-11-15")
            if iso2 == "UG" and (batch41_row.get("visa_required") != "true" or batch41_row.get("visa_free_eligibility") != "false" or batch41_row.get("visa_on_arrival_available") != "false" or batch41_row.get("evisa_available") != "true" or "3 months" not in batch41_row.get("maximum_permitted_stay", "").lower() or "usd 50" not in batch41_row.get("application_fee", "").lower()):
                errors.append("Batch 41 Uganda route or fee fields do not match official NCIC guidance")
            if iso2 == "ZW" and (batch41_row.get("visa_required") != "true" or batch41_row.get("visa_free_eligibility") != "false" or batch41_row.get("visa_on_arrival_available") != "false" or batch41_row.get("evisa_available") != "true" or "3 months" not in batch41_row.get("maximum_permitted_stay", "").lower() or "usd 30" not in batch41_row.get("application_fee", "").lower()):
                errors.append("Batch 41 Zimbabwe route or fee fields do not match official Category C and fee guidance")
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
        if path.name in {"visa_full_audit_batch_33.csv", "visa_full_audit_batch_36.csv", "visa_full_audit_batch_38.csv"}:
            schema_limits = {"visa_requirement_type": 50, "maximum_permitted_stay": 120, "entry_count": 40, "application_channel": 180, "application_fee": 120, "processing_time": 120}
            for field, limit in schema_limits.items():
                if len(row.get(field, "") or "") > limit:
                    errors.append(f"{path.name}:{line}: {field} exceeds actual visa_requirements schema limit {limit}")
            if row.get("notes", "").strip():
                errors.append(f"{path.name}:{line}: free-text notes are not mapped by the importer; use schema-supported fields and review_notes")
        if path.name == "visa_full_audit_batch_33.csv":
            if row.get("visa_required") != "false" or row.get("visa_free_eligibility") != "false" or "not a visa" not in row.get("visa_requirement_type", "").lower():
                errors.append(f"{path.name}:{line}: mandatory entry permit must not be misclassified as a visa or visa-free")
        if len(row.get("review_notes", "") or "") > 5000:
            errors.append(f"{path.name}:{line}: review_notes exceeds 5000 characters")
        if path.name == "visa_full_audit_batch_38.csv":
            if row.get("notes", "").strip() or row.get("verification_status") != "verified" or row.get("publish_status") != "published":
                errors.append(f"{path.name}:{line}: batch 38 rows must be verified, published and have no unmapped notes")
            if row.get("destination_iso2") == "AR" and (row.get("visa_required") != "true" or row.get("visa_free_eligibility") != "false" or row.get("visa_on_arrival_available") != "false" or row.get("evisa_available") != "false" or "90 days" not in row.get("maximum_permitted_stay", "").lower() or "migraciones.gob.ar" not in row.get("official_source_url", "")):
                errors.append(f"{path.name}:{line}: Argentina route flags or source are inconsistent")
            if row.get("destination_iso2") == "BS" and (row.get("visa_required") != "false" or row.get("visa_free_eligibility") != "true" or row.get("visa_on_arrival_available") != "false" or row.get("evisa_available") != "false" or ("3 months" not in row.get("maximum_permitted_stay", "").lower() and "three months" not in row.get("maximum_permitted_stay", "").lower()) or "cdn.bahamas.gov.bs" not in row.get("official_source_url", "")):
                errors.append(f"{path.name}:{line}: Bahamas route flags or source are inconsistent")
        checklist_raw = (row.get("applicant_checklists", "") or "").strip()
        photo_raw = (row.get("photo_specifications", "") or "").strip()
        if (path.name in {"visa_full_audit_batch_28.csv", "visa_full_audit_batch_29.csv", "visa_full_audit_batch_33.csv", "visa_full_audit_batch_36.csv", "visa_full_audit_batch_38.csv"} or (path.name == "visa_full_audit_batch_12.csv" and row.get("destination_iso2") in {"UG", "ZW"})) and not checklist_raw:
            errors.append(f"{path.name}:{line}: applicant_checklists is required for the new publishable batch")
        if (path.name in {"visa_full_audit_batch_28.csv", "visa_full_audit_batch_29.csv", "visa_full_audit_batch_33.csv", "visa_full_audit_batch_36.csv", "visa_full_audit_batch_38.csv"} or (path.name == "visa_full_audit_batch_12.csv" and row.get("destination_iso2") in {"UG", "ZW"})) and not photo_raw:
            errors.append(f"{path.name}:{line}: photo_specifications is required for the new publishable batch")
        if checklist_raw:
            try:
                checklists = json.loads(checklist_raw)
                if not isinstance(checklists, list) or not checklists:
                    raise ValueError("expected a non-empty applicant checklist array")
                applicant_types = set()
                for checklist in checklists:
                    if not isinstance(checklist, dict):
                        raise ValueError("applicant checklist entry must be an object")
                    applicant_type = checklist.get("applicant_type", "")
                    if not applicant_type:
                        raise ValueError("applicant_type is required")
                    applicant_types.add(applicant_type)
                    if path.name == "visa_full_audit_batch_38.csv":
                        if not checklist.get("source_name") or not (checklist.get("source_url") or "").startswith("https://") or checklist.get("checked_at") != "2026-11-15":
                            raise ValueError("batch 38 applicant checklist needs an official source name, HTTPS URL and checked date")
                    if path.name == "visa_full_audit_batch_12.csv" and row.get("destination_iso2") in {"UG", "ZW"}:
                        if not checklist.get("source_name") or not (checklist.get("source_url") or "").startswith("https://") or checklist.get("checked_at") != "2026-11-15":
                            raise ValueError("batch 41 applicant checklist needs official source metadata and checked date")
                    checklist_notes = checklist.get("notes")
                    if checklist_notes is not None and (not isinstance(checklist_notes, str) or len(checklist_notes) > 1000):
                        raise ValueError("applicant checklist notes exceed 1000 characters")
                    documents = checklist.get("documents")
                    if not isinstance(documents, list) or not documents:
                        raise ValueError("each applicant checklist needs documents")
                    for document in documents:
                        if not isinstance(document, dict):
                            raise ValueError("checklist document must be an object")
                        name = document.get("name", "")
                        if not isinstance(name, str) or not name.strip() or len(name) > 200:
                            raise ValueError("checklist document name is required and limited to 200 characters")
                        if document.get("status") not in {"required", "conditional", "if_applicable"}:
                            raise ValueError("checklist document status is invalid")
                        notes = document.get("notes")
                        if notes is not None and (not isinstance(notes, str) or len(notes) > 1000):
                            raise ValueError("checklist document notes exceed 1000 characters")
                if path.name in {"visa_full_audit_batch_28.csv", "visa_full_audit_batch_29.csv", "visa_full_audit_batch_36.csv", "visa_full_audit_batch_38.csv"} or (path.name == "visa_full_audit_batch_12.csv" and row.get("destination_iso2") in {"UG", "ZW"}):
                    expected_applicant_types = {"employee", "self_employed_or_business_owner", "student", "retired"}
                    if applicant_types != expected_applicant_types:
                        raise ValueError(f"expected four applicant types, got {sorted(applicant_types)}")
                elif path.name == "visa_full_audit_batch_33.csv":
                    if applicant_types != {"tourist"}:
                        raise ValueError(f"entry-permit route requires one visitor-only checklist, got {sorted(applicant_types)}")
            except Exception as exc:
                errors.append(f"{path.name}:{line}: invalid applicant_checklists: {exc}")
        if photo_raw:
            try:
                photo = json.loads(photo_raw)
                if not isinstance(photo, dict) or not photo.get("status"):
                    raise ValueError("photo specification needs a status")
                if path.name in {"visa_full_audit_batch_28.csv", "visa_full_audit_batch_29.csv", "visa_full_audit_batch_33.csv", "visa_full_audit_batch_36.csv", "visa_full_audit_batch_38.csv"}:
                    if not photo.get("source_name") or not (photo.get("source_url") or "").startswith("https://"):
                        raise ValueError("new batch photo specification needs an official source name and HTTPS URL")
                    date.fromisoformat(str(photo.get("checked_at", ""))[:10])
                if path.name == "visa_full_audit_batch_12.csv" and row.get("destination_iso2") in {"UG", "ZW"}:
                    if photo.get("status") != "partially_specified" or photo.get("dimensions_mm") or photo.get("digital_dimensions_px"):
                        raise ValueError("batch 41 photo must remain partially specified without invented dimensions")
                    if not photo.get("source_name") or not (photo.get("source_url") or "").startswith("https://") or photo.get("checked_at") != "2026-11-15":
                        raise ValueError("batch 41 photo source metadata is incomplete")
                if path.name == "visa_full_audit_batch_33.csv":
                    if photo.get("status") != "not_specified" or photo.get("dimensions_mm") or photo.get("digital_dimensions_px"):
                        raise ValueError("South Georgia photo status must be not_specified without invented dimensions")
                if path.name == "visa_full_audit_batch_36.csv":
                    if not photo.get("source_name") or photo.get("checked_at") != "2026-11-15":
                        raise ValueError("batch 36 photo source metadata is incomplete")
                    if row.get("destination_iso2") == "AG" and (photo.get("status") != "verified" or photo.get("dimensions_mm") != {"width": 45, "height": 35}):
                        raise ValueError("Antigua photo specification must contain the verified 45x35mm dimensions")
                    if row.get("destination_iso2") == "ET" and (photo.get("status") != "partially_specified" or photo.get("dimensions_mm") or not photo.get("file_size") or not photo.get("file_format")):
                        raise ValueError("Ethiopia photo specification must be partially specified without invented dimensions")
                if path.name == "visa_full_audit_batch_38.csv":
                    if not photo.get("source_name") or photo.get("checked_at") != "2026-11-15":
                        raise ValueError("batch 38 photo source metadata is incomplete")
                    if row.get("destination_iso2") == "AR" and (photo.get("status") != "verified" or photo.get("dimensions_mm") != {"width": 40, "height": 40, "unit": "mm"}):
                        raise ValueError("Argentina photo specification must contain verified 40x40mm dimensions")
                    if row.get("destination_iso2") == "BS" and (photo.get("status") != "not_applicable" or photo.get("dimensions_mm") or photo.get("digital_dimensions_px")):
                        raise ValueError("Bahamas photo status must be not_applicable for visa-free entry")
            except Exception as exc:
                errors.append(f"{path.name}:{line}: invalid photo_specifications: {exc}")
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
            if not isinstance(sources, list) or len(sources) < (4 if path.name == "visa_full_audit_batch_33.csv" else 2):
                raise ValueError("expected at least four official sources for batch 33 and at least two for other batches")
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
                if path.name == "visa_full_audit_batch_33.csv":
                    if source.get("source_type") != "official" or not (notes or "").strip() or source.get("source_last_checked_at") != "2026-11-15":
                        raise ValueError("batch 33 additional sources must be official, dated 2026-11-15 and have verification notes")
                if path.name == "visa_full_audit_batch_36.csv":
                    if source.get("source_type") != "official" or not (notes or "").strip() or source.get("source_last_checked_at") != "2026-11-15":
                        raise ValueError("batch 36 additional sources must be official, dated 2026-11-15 and have verification notes")
                if path.name == "visa_full_audit_batch_38.csv":
                    if source.get("source_type") != "official" or not (notes or "").strip() or source.get("source_last_checked_at") != "2026-11-15":
                        raise ValueError("batch 38 additional sources must be official, dated 2026-11-15 and have verification notes")
                if path.name == "visa_full_audit_batch_12.csv" and row.get("destination_iso2") in {"UG", "ZW"}:
                    if source.get("source_type") != "official" or not (notes or "").strip() or source.get("source_last_checked_at") != "2026-11-15":
                        raise ValueError("batch 41 additional sources must be official, dated 2026-11-15 and have verification notes")
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
