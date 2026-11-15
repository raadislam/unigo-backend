import csv
import json
from pathlib import Path

root = Path(__file__).resolve().parents[1]
path = root / "database/data/visa_full_audit_batch_47.csv"
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
    "verification_status", "publish_status", "notes", "review_notes", "full_audit",
    "additional_sources", "photo_specifications", "applicant_checklists",
}
limits = {
    "visa_requirement_type": 50,
    "maximum_permitted_stay": 120,
    "entry_count": 40,
    "application_channel": 180,
    "application_fee": 120,
    "processing_time": 120,
    "official_immigration_source_name": 180,
    "cross_check_source_name": 180,
    "review_notes": 5000,
}
if not path.is_file():
    raise SystemExit("Batch 47 full-audit CSV is missing.")
with path.open(encoding="utf-8-sig", newline="") as handle:
    reader = csv.DictReader(handle)
    missing = required - set(reader.fieldnames or [])
    if missing:
        raise SystemExit(f"Batch 47 missing columns: {sorted(missing)}")
    rows = list(reader)
if len(rows) != 1:
    raise SystemExit(f"Batch 47 must contain exactly one existing-record update; found {len(rows)}.")
row = rows[0]
if (row["passport_iso3"], row["destination_iso2"], row["travel_purpose"]) != ("BGD", "IM", "tourism"):
    raise SystemExit("Batch 47 allowlist must be exactly BGD / IM / tourism.")
if row["destination_name"] != "Isle of Man" or row["passport_nationality"] != "Bangladesh":
    raise SystemExit("Batch 47 destination or nationality identity mismatch.")
if row["verification_status"] != "verified" or row["publish_status"] != "published" or row["full_audit"].lower() != "true":
    raise SystemExit("Batch 47 must be a fully audited, verified, publication-approved record.")
if row["notes"].strip():
    raise SystemExit("Batch 47 contains an unmapped notes field.")
for field, limit in limits.items():
    if len(row.get(field, "")) > limit:
        raise SystemExit(f"Batch 47 {field} exceeds schema limit {limit}.")
for field in ("official_source_url", "cross_check_source_url"):
    if not row[field].startswith("https://"):
        raise SystemExit(f"Batch 47 {field} must be HTTPS.")
if row["official_source_last_checked"] != "2026-11-15" or row["cross_check_source_last_checked"] != "2026-11-15":
    raise SystemExit("Batch 47 source checked dates must be 2026-11-15.")
if row["official_source_url"] != "https://www.gov.im/categories/travel-traffic-and-motoring/immigration/visit/visiting-the-isle-of-man-if-you-hold-a-valid-visa-for-the-british-isles/":
    raise SystemExit("Batch 47 primary source is not the official Isle of Man qualifying-visa page.")
if row["cross_check_source_url"] != "https://www.gov.uk/guidance/immigration-rules/immigration-rules-appendix-visitor-visa-national-list":
    raise SystemExit("Batch 47 cross-check is not the current UK Home Office visitor visa-national list.")
if row["visa_required"] != "true" or row["visa_free_eligibility"] != "false" or row["visa_on_arrival_available"] != "false" or row["evisa_available"] != "false":
    raise SystemExit("Batch 47 entry-route flags are inconsistent with the verified UKVI-visa route.")
if "6 months" not in row["maximum_permitted_stay"].lower() or "13 weeks" not in row["processing_time"].lower():
    raise SystemExit("Batch 47 stay or IOM processing estimate is missing.")
for fee in ("BDT 22,997", "BDT 86,197", "BDT 153,825", "BDT 192,154"):
    if fee not in row["application_fee"]:
        raise SystemExit(f"Batch 47 fee schedule is missing {fee}.")
if "VFS" not in row["application_channel"] or "UKVI" not in row["application_channel"]:
    raise SystemExit("Batch 47 Bangladesh application channel is incomplete.")
if not row["required_documents"].strip():
    raise SystemExit("Batch 47 required documents are empty.")

try:
    sources = json.loads(row["additional_sources"])
    photo = json.loads(row["photo_specifications"])
    checklists = json.loads(row["applicant_checklists"])
except (json.JSONDecodeError, TypeError) as exc:
    raise SystemExit(f"Batch 47 structured JSON is invalid: {exc}")
if not isinstance(sources, list) or len(sources) < 8:
    raise SystemExit("Batch 47 requires at least eight additional official/provider sources.")
for source in sources:
    if source.get("source_type") not in {"official", "provider", "secondary"}:
        raise SystemExit("Batch 47 has an unsupported additional-source type.")
    if not source.get("source_name") or len(source["source_name"]) > 180 or not source.get("source_url", "").startswith("https://"):
        raise SystemExit("Batch 47 has an invalid additional source name or URL.")
    if source.get("source_last_checked_at") != "2026-11-15":
        raise SystemExit("Batch 47 additional source dates must be 2026-11-15.")
    if len(source.get("verification_notes", "")) > 5000:
        raise SystemExit("Batch 47 additional-source verification notes exceed 5000 characters.")
if photo.get("status") != "no_fixed_size_published" or photo.get("captured_at_vac") is not True or photo.get("biometric_capture") is not True or photo.get("no_fixed_size_published") is not True:
    raise SystemExit("Batch 47 photo status must separately record no fixed size and VAC biometric capture.")
if photo.get("checked_at") != "2026-11-15" or not photo.get("source_url", "").startswith("https://") or not photo.get("source_name"):
    raise SystemExit("Batch 47 photo source metadata is incomplete.")
for field in ("dimensions_mm", "digital_dimensions_px", "digital_pixels", "file_size", "file_format", "head_measurements"):
    if photo.get(field) not in (None, "", []):
        raise SystemExit(f"Batch 47 must not invent {field}.")
if not isinstance(checklists, list) or len(checklists) != 4:
    raise SystemExit("Batch 47 must contain exactly four applicant checklists.")
expected_types = {"employee", "self_employed_or_business_owner", "student", "retired"}
types = {item.get("applicant_type") for item in checklists}
if types != expected_types:
    raise SystemExit(f"Batch 47 applicant checklist types are wrong: {sorted(types)}")
for item in checklists:
    if not item.get("source_name") or not item.get("source_url", "").startswith("https://") or item.get("checked_at") != "2026-11-15":
        raise SystemExit("Batch 47 applicant checklist source metadata is incomplete.")
    docs = item.get("documents")
    if not isinstance(docs, list) or len(docs) < 12:
        raise SystemExit("Batch 47 applicant checklist is incomplete.")
    for doc in docs:
        if not doc.get("name") or doc.get("status") not in {"required", "conditional"} or not doc.get("notes"):
            raise SystemExit("Batch 47 applicant checklist has an invalid document/status/note.")
if "Bangladesh" not in row["review_notes"] or "not an eVisa" not in row["review_notes"] or "13 weeks" not in row["review_notes"] or "403" not in row["review_notes"]:
    raise SystemExit("Batch 47 review notes omit a material route, eVisa, processing or source-access nuance.")
print(json.dumps({
    "batch": 47,
    "allowlist": ["BGD:IM:tourism"],
    "rows": 1,
    "additional_sources": len(sources),
    "photo_status": photo["status"],
    "captured_at_vac": photo["captured_at_vac"],
    "applicant_checklists": sorted(types),
    "verification_status": row["verification_status"],
    "publish_status": row["publish_status"],
}, ensure_ascii=False))
