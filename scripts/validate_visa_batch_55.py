import csv
import json
import sys
from pathlib import Path
from urllib.parse import urlparse

path = Path("database/data/visa_full_audit_batch_55.csv")
if not path.is_file():
    raise SystemExit("BATCH55_VALIDATION_ERROR missing CSV file")

with path.open(newline="", encoding="utf-8-sig") as handle:
    rows = list(csv.DictReader(handle))

if len(rows) != 1:
    raise SystemExit(f"BATCH55_VALIDATION_ERROR expected exactly one row, found {len(rows)}")

row = rows[0]
expected = {
    "passport_iso3": "BGD",
    "passport_nationality": "Bangladesh",
    "destination_iso2": "MY",
    "destination_name": "Malaysia",
    "travel_purpose": "tourism",
    "verification_status": "verified",
    "publish_status": "published",
    "full_audit": "true",
    "visa_required": "true",
    "visa_free_eligibility": "false",
    "visa_on_arrival_available": "false",
    "evisa_available": "true",
}
for key, value in expected.items():
    if row.get(key, "").strip().lower() != value.lower():
        raise SystemExit(f"BATCH55_VALIDATION_ERROR {key} expected {value!r}")

for key, limit in {
    "application_fee": 120,
    "processing_time": 120,
    "official_immigration_source_name": 180,
    "cross_check_source_name": 180,
    "review_notes": 5000,
    "entry_count": 40,
}.items():
    value = row.get(key, "").strip()
    if not value or len(value) > limit:
        raise SystemExit(f"BATCH55_VALIDATION_ERROR {key} is empty or exceeds {limit} characters")

for key in ("official_source_url", "cross_check_source_url"):
    parsed = urlparse(row.get(key, ""))
    if parsed.scheme != "https" or not parsed.netloc:
        raise SystemExit(f"BATCH55_VALIDATION_ERROR invalid {key}")

if row.get("official_source_url") != "https://malaysiavisa.imi.gov.my/evisa/assets/pdf/tnc_en.pdf":
    raise SystemExit("BATCH55_VALIDATION_ERROR unexpected primary source URL")
if row.get("cross_check_source_url") != "https://www.kln.gov.my/en_US/web/bgd_dhaka/faqs":
    raise SystemExit("BATCH55_VALIDATION_ERROR unexpected Bangladesh cross-check source URL")

documents = [item.strip() for item in row.get("required_documents", "").split("|") if item.strip()]
if len(documents) < 8 or any(len(item) > 500 for item in documents):
    raise SystemExit("BATCH55_VALIDATION_ERROR required_documents must contain at least eight valid items")

def decode_array(field):
    try:
        value = json.loads(row.get(field, ""))
    except json.JSONDecodeError as exc:
        raise SystemExit(f"BATCH55_VALIDATION_ERROR invalid {field} JSON: {exc}")
    if not isinstance(value, (dict, list)):
        raise SystemExit(f"BATCH55_VALIDATION_ERROR {field} must decode to an object or array")
    return value

photo = decode_array("photo_specifications")
if photo.get("status") != "partially_specified":
    raise SystemExit("BATCH55_VALIDATION_ERROR photo status must remain partially_specified")
if photo.get("dimensions_mm") != "35 × 50 mm (official MYVISA eVISA FAQ)":
    raise SystemExit("BATCH55_VALIDATION_ERROR official eVISA photo dimension mismatch")
if photo.get("checked_at") != "2026-11-15" or not photo.get("source_name"):
    raise SystemExit("BATCH55_VALIDATION_ERROR photo source metadata missing")
if photo.get("source_url") != "https://malaysiavisa.imi.gov.my/evisa/FAQ/PDF/FAQ/Support/FAQ_en.pdf?version=3.8.1":
    raise SystemExit("BATCH55_VALIDATION_ERROR photo source URL mismatch")
if photo.get("digital_dimensions_px") or photo.get("file_size"):
    raise SystemExit("BATCH55_VALIDATION_ERROR unsupported pixel/file-size values must be omitted")

checklists = decode_array("applicant_checklists")
if not isinstance(checklists, list):
    raise SystemExit("BATCH55_VALIDATION_ERROR applicant_checklists must be an array")
expected_types = {"employee", "self_employed_or_business_owner", "student", "retired"}
types = [item.get("applicant_type") for item in checklists if isinstance(item, dict)]
if len(types) != 4 or set(types) != expected_types:
    raise SystemExit(f"BATCH55_VALIDATION_ERROR applicant types mismatch: {types}")

for item in checklists:
    if not item.get("source_name") or item.get("source_url") != row.get("official_source_url") or item.get("checked_at") != "2026-11-15":
        raise SystemExit(f"BATCH55_VALIDATION_ERROR checklist source metadata missing for {item.get('applicant_type')}")
    docs = item.get("documents")
    if not isinstance(docs, list) or len(docs) < 8:
        raise SystemExit(f"BATCH55_VALIDATION_ERROR insufficient documents for {item.get('applicant_type')}")
    for document in docs:
        if not isinstance(document, dict) or not document.get("name"):
            raise SystemExit(f"BATCH55_VALIDATION_ERROR malformed document in {item.get('applicant_type')}")
        if document.get("status") not in {"required", "conditional", "if_applicable"}:
            raise SystemExit(f"BATCH55_VALIDATION_ERROR invalid document status in {item.get('applicant_type')}")

sources = decode_array("additional_sources")
if not isinstance(sources, list) or len(sources) < 7:
    raise SystemExit("BATCH55_VALIDATION_ERROR at least seven additional official sources are required")
source_urls = set()
for source in sources:
    parsed = urlparse(source.get("source_url", ""))
    if source.get("source_type") != "official" or not source.get("source_name") or parsed.scheme != "https" or not parsed.netloc:
        raise SystemExit("BATCH55_VALIDATION_ERROR invalid additional official source")
    if source["source_url"] in source_urls:
        raise SystemExit("BATCH55_VALIDATION_ERROR duplicate additional source URL")
    source_urls.add(source["source_url"])

if "3 versus 6 months" not in row.get("review_notes", ""):
    raise SystemExit("BATCH55_VALIDATION_ERROR bank-statement route conflict must be documented")
if "35 × 45 mm" not in photo.get("notes", "") or "not an official" not in photo.get("notes", ""):
    raise SystemExit("BATCH55_VALIDATION_ERROR agency photo-size conflict must be documented")

print(json.dumps({
    "batch": 55,
    "rows": 1,
    "allowlist": ["MY"],
    "passport": "BGD ordinary",
    "purpose": "tourism",
    "photo_status": photo["status"],
    "photo_dimensions_mm": photo["dimensions_mm"],
    "checklist_profiles": sorted(types),
    "documents_per_profile": {item["applicant_type"]: len(item["documents"]) for item in checklists},
    "additional_official_sources": len(sources),
    "validation": "passed"
}, ensure_ascii=False))
