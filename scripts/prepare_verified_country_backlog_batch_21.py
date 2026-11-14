import csv
import json
from pathlib import Path
import re

ALLOWLIST = {"ID","MU","NP","PH","TR","BN","RW","TZ","HK","MO","UZ","KR","AM","AZ","PK","TL","BB","JM","FJ","KI","NZ","TO","VU","AS","CK","GU","MP","NF","NC","PF","WF","PN","TK","AO","BJ","BW","GM","MZ","NG","AL","IE","MD","ME","RS"}
SIMPLE_FILES = ["database/data/new_country_visa_batch_06_07.csv","database/data/new_country_visa_batch_08.csv","database/data/new_country_visa_batch_10.csv","database/data/new_country_visa_batch_14.csv","database/data/new_country_visa_batch_15.csv","database/data/new_country_visa_batch_16.csv","database/data/new_country_visa_batch_17.csv","database/data/new_country_visa_batch_21.csv"]
AUDIT_FILES = ["database/data/visa_full_audit_batch_06.csv","database/data/visa_full_audit_batch_07.csv","database/data/visa_full_audit_batch_08.csv","database/data/visa_full_audit_batch_10.csv","database/data/visa_full_audit_batch_14.csv","database/data/visa_full_audit_batch_15.csv","database/data/visa_full_audit_batch_16.csv","database/data/visa_full_audit_batch_17.csv","database/data/visa_full_audit_batch_21.csv"]
OVERRIDES = Path("database/data/verified_country_backlog_batch_21_overrides.csv")
LEDGER = Path("database/data/country_discovery_ledger.csv")
PENDING = Path("database/data/pending_country_research_batch_21.csv")
SIMPLE_OUT = Path("database/data/verified_country_backlog_batch_21.csv")
AUDIT_OUT = Path("database/data/verified_country_full_audit_batch_21.csv")
REQUIRED = {"visa_requirement_type","visa_required","visa_free_eligibility","visa_on_arrival_available","evisa_available","maximum_permitted_stay","entry_count","passport_validity_rule","blank_passport_pages_rule","return_ticket_rule","proof_of_funds_rule","accommodation_rule","travel_insurance_rule","yellow_fever_or_health_rule","transit_rule","application_channel","application_fee","processing_time","required_documents","official_source_url","official_immigration_source_name","official_source_last_checked","cross_check_source_url","cross_check_source_name","cross_check_source_last_checked","verification_status","review_notes"}

def read(path):
    with Path(path).open(encoding="utf-8-sig", newline="") as handle:
        return list(csv.DictReader(handle))

def write(path, rows):
    if not rows:
        raise SystemExit(f"No rows to write: {path}")
    with Path(path).open("w", encoding="utf-8", newline="") as handle:
        writer = csv.DictWriter(handle, fieldnames=list(rows[0].keys()), lineterminator="\n")
        writer.writeheader()
        writer.writerows(rows)

def batch_id(path):
    name = Path(path).name
    if "06_07" in name:
        return "06_07"
    match = re.search(r"batch_(\d+)", name)
    return match.group(1) if match else ""

overrides = read(OVERRIDES)
override_map = {}
for item in overrides:
    override_map.setdefault((item["batch"], item["destination_iso2"]), []).append(item)

def collect(paths):
    result = []
    for path in paths:
        batch = batch_id(path)
        for row in read(path):
            code = row.get("destination_iso2","")
            if code not in ALLOWLIST:
                continue
            for item in override_map.get((batch, code), []):
                row[item["field"]] = item["value"]
            result.append(row)
    return result

simple = collect(SIMPLE_FILES)
audit = collect(AUDIT_FILES)
simple_codes = [row.get("destination_iso2","") for row in simple]
audit_codes = [row.get("destination_iso2","") for row in audit]
if len(simple) != 44 or set(simple_codes) != ALLOWLIST or len(set(simple_codes)) != 44:
    raise SystemExit("Simple export does not match the exact 44-country allowlist")
if len(audit) != 44 or set(audit_codes) != ALLOWLIST or len(set(audit_codes)) != 44:
    raise SystemExit("Full-audit export does not match the exact 44-country allowlist")
simple_by_code = {row["destination_iso2"]: row for row in simple}
for row in audit:
    code = row["destination_iso2"]
    if row.get("passport_iso3") != "BGD" or row.get("travel_purpose") != "tourism":
        raise SystemExit(f"{code}: wrong passport or purpose")
    if row.get("verification_status") != "verified" or row.get("publish_status") != "published" or row.get("full_audit") != "true":
        raise SystemExit(f"{code}: not explicitly verified and publish-approved")
    for field in REQUIRED:
        if not str(row.get(field,"")).strip():
            raise SystemExit(f"{code}: missing {field}")
    for field in ("visa_required","visa_free_eligibility","visa_on_arrival_available","evisa_available"):
        if row.get(field) not in ("true","false"):
            raise SystemExit(f"{code}: invalid boolean {field}")
    if row.get("official_source_last_checked") != "2026-11-14" or row.get("cross_check_source_last_checked") != "2026-11-14":
        raise SystemExit(f"{code}: source checked date mismatch")
    if not row["official_source_url"].startswith("https://") or not row["cross_check_source_url"].startswith("https://"):
        raise SystemExit(f"{code}: source URL must use HTTPS")
    extra = json.loads(row.get("additional_sources") or "[]")
    if not isinstance(extra,list) or len(extra) < 2:
        raise SystemExit(f"{code}: fewer than two additional sources")
    for source in extra:
        if not source.get("source_url","").startswith("https://") or source.get("source_type") not in ("official","provider","secondary") or not source.get("source_name"):
            raise SystemExit(f"{code}: invalid additional source record")
    for field in ("passport_iso3","travel_purpose","visa_requirement_type","visa_required","visa_free_eligibility","visa_on_arrival_available","evisa_available","application_fee","processing_time","official_source_url","cross_check_source_url","verification_status"):
        if simple_by_code[code].get(field) != row.get(field):
            raise SystemExit(f"{code}: simple/full-audit mismatch in {field}")
ledger = {row["iso2"]: row for row in read(LEDGER)}
for code in ALLOWLIST:
    row = ledger.get(code)
    if not row or row.get("country_row_status") != "present_in_production" or row.get("visa_record_status") != "verified_ready_for_import_batch_21":
        raise SystemExit(f"{code}: ledger readiness or production country row missing")
new_simple = read("database/data/new_country_visa_batch_21.csv")
new_audit = read("database/data/visa_full_audit_batch_21.csv")
if len(new_simple) != 5 or len(new_audit) != 5 or {r["destination_iso2"] for r in new_simple} != {"AL","IE","MD","ME","RS"}:
    raise SystemExit("New Batch 21 file must contain exactly AL, IE, MD, ME and RS")
pending = read(PENDING)
if {r["destination_iso2"] for r in pending} != {"BA","MK","MM","WS"}:
    raise SystemExit("Pending research batch must contain BA, MK, MM and WS")
samoa = [row for row in read("database/data/visa_full_audit_batch_15.csv") if row.get("destination_iso2") == "WS"]
if len(samoa) != 1 or samoa[0].get("verification_status") != "pending_review" or samoa[0].get("publish_status") != "draft":
    raise SystemExit("Samoa fee-scope conflict must remain pending and unpublished")
print("VALIDATION_PASSED: 44 verified rows; 5 new verified destinations; 3 new pending candidates plus Samoa; no duplicate ISO2 keys.")
write(SIMPLE_OUT, simple)
write(AUDIT_OUT, audit)
