import csv
import sys
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
coverage_path = ROOT / "database/data/vfs_bangladesh_destination_coverage.csv"
fees_path = ROOT / "database/data/vfs_fee_processing_snapshot_20261114.csv"

coverage_required = {
    "destination_iso2", "destination_name", "vfs_relationship", "service_scope",
    "submission_location", "centre_address", "destination_specific_vfs_url",
    "official_source_url", "source_checked", "decision_authority_note", "research_notes"
}
fee_required = {
    "destination_iso2", "destination_name", "visa_category", "government_visa_fee",
    "vfs_mandatory_service_fee", "other_mandatory_charge", "optional_services",
    "processing_time_or_handling", "appointment_and_submission_rule", "centre_hours",
    "official_vfs_source_url", "official_government_source_url", "source_checked", "scope_notes"
}

def load(path, required):
    with path.open(encoding="utf-8-sig", newline="") as handle:
        reader = csv.DictReader(handle)
        missing = required - set(reader.fieldnames or [])
        if missing:
            raise ValueError(f"{path.name}: missing columns {sorted(missing)}")
        rows = list(reader)
    if not rows:
        raise ValueError(f"{path.name}: no data rows")
    return rows

errors = []
try:
    coverage = load(coverage_path, coverage_required)
    seen = set()
    for line, row in enumerate(coverage, 2):
        code = row["destination_iso2"].strip().upper()
        if not code or code in seen:
            errors.append(f"coverage:{line}: blank or duplicate ISO2 {code!r}")
        seen.add(code)
        for field in ("destination_name", "vfs_relationship", "service_scope", "submission_location", "decision_authority_note", "research_notes"):
            if not row.get(field, "").strip():
                errors.append(f"coverage:{line}: {field} is empty")
        if not row.get("official_source_url", "").startswith("https://"):
            errors.append(f"coverage:{line}: official_source_url must be HTTPS")
        if row.get("destination_specific_vfs_url") and not row["destination_specific_vfs_url"].startswith("https://"):
            errors.append(f"coverage:{line}: destination_specific_vfs_url must be HTTPS")
        if row.get("source_checked") != "2026-11-14":
            errors.append(f"coverage:{line}: source_checked is not current batch date")
        if "VAC listed;" in row.get("service_scope", "") or "verify current jurisdiction" in row.get("service_scope", ""):
            errors.append(f"coverage:{line}: generic unresearched service-scope placeholder remains")
        if code not in {"OM", "SR"} and not row.get("centre_address", "").strip():
            errors.append(f"coverage:{line}: centre address is missing")
    if len(coverage) != 32:
        errors.append(f"coverage: expected 32 scoped records, found {len(coverage)}")

    fee_rows = load(fees_path, fee_required)
    fee_seen = set()
    for line, row in enumerate(fee_rows, 2):
        code = row["destination_iso2"].strip().upper()
        if not code or code in fee_seen:
            errors.append(f"fees:{line}: blank or duplicate ISO2 {code!r}")
        fee_seen.add(code)
        for field in ("destination_name", "visa_category", "government_visa_fee", "vfs_mandatory_service_fee", "processing_time_or_handling", "appointment_and_submission_rule", "official_vfs_source_url", "official_government_source_url", "scope_notes"):
            if not row.get(field, "").strip():
                errors.append(f"fees:{line}: {field} is empty")
        for field in ("official_vfs_source_url", "official_government_source_url"):
            if not row.get(field, "").startswith("https://"):
                errors.append(f"fees:{line}: {field} must be HTTPS")
        if row.get("source_checked") != "2026-11-14":
            errors.append(f"fees:{line}: source_checked is not current batch date")
    if len(fee_rows) != 13:
        errors.append(f"fees: expected 13 fee/processing snapshots, found {len(fee_rows)}")
except Exception as exc:
    errors.append(str(exc))

if errors:
    print("VFS DATA VALIDATION FAILED")
    print("\\n".join(errors))
    sys.exit(1)

print(f"VFS DATA VALIDATION PASSED: {len(coverage)} destination/service records; {len(fee_rows)} fee/processing snapshots; no duplicate country keys or placeholder scopes.")
