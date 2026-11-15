#!/usr/bin/env python3
import argparse
import csv
import json
from datetime import date
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
TODAY = date.today().isoformat()
ALLOWLIST = {"AC", "TA"}

def fail(message):
    raise SystemExit(f"BATCH45_VALIDATION_FAILED: {message}")

def read_csv(path):
    if not path.is_file():
        fail(f"missing file: {path}")
    with path.open(newline="", encoding="utf-8-sig") as handle:
        reader = csv.DictReader(handle)
        if not reader.fieldnames:
            fail(f"missing header: {path.name}")
        return reader.fieldnames, list(reader)

def parse_json(value, label):
    try:
        return json.loads(value)
    except Exception as exc:
        fail(f"{label} invalid JSON: {exc}")

def check(condition, message):
    if not condition:
        fail(message)

def main():
    parser = argparse.ArgumentParser()
    parser.add_argument("--output-dir", default="/tmp/verified-batch-45")
    output = Path(parser.parse_args().output_dir)
    output.mkdir(parents=True, exist_ok=True)

    seed_path = ROOT / "database/data/new_country_visa_batch_45.csv"
    audit_path = ROOT / "database/data/visa_full_audit_batch_45.csv"
    master_path = ROOT / "database/data/country_master_backfill_batch_21.csv"
    seed_headers, seeds = read_csv(seed_path)
    audit_headers, audits = read_csv(audit_path)
    master_headers, masters = read_csv(master_path)

    def key(row):
        return (row["passport_iso3"].strip(), row["destination_iso2"].strip().upper(), row["travel_purpose"].strip().lower())

    seed_map = {key(row): row for row in seeds}
    audit_map = {key(row): row for row in audits}
    expected = {("BGD","AC","tourism"),("BGD","TA","tourism")}
    check(len(seeds) == 2 and set(seed_map) == expected, "seed allowlist must be exactly BGD tourism AC and TA")
    check(len(audits) == 2 and set(audit_map) == expected, "audit allowlist must be exactly BGD tourism AC and TA")
    check(len(seed_map) == len(seeds) and len(audit_map) == len(audits), "duplicate seed or audit key")

    master_codes = [row["iso2"].strip().upper() for row in masters]
    check(len(masters) == 2 and set(master_codes) == ALLOWLIST and len(set(master_codes)) == 2, "country master allowlist must be exactly AC and TA")
    slugs = set()
    for row in masters:
        code = row["iso2"].strip().upper()
        check(not row["iso3"].strip(), f"{code} must not receive a fabricated alpha-3 code")
        check(not row["flag_emoji"].strip(), f"{code} must use SVG flags, not emoji")
        check(row["is_published"].strip().lower() in {"false","0","no"}, f"{code} master row must remain unpublished")
        check("exceptionally reserved" in row["code_status"].lower(), f"{code} exceptional code status missing")
        check(row["checked_date"] == TODAY, f"{code} country source date must be {TODAY}")
        check(row["parent_jurisdiction"] == "Saint Helena, Ascension and Tristan da Cunha (British Overseas Territory)", f"{code} parent jurisdiction missing")
        check(row["slug"] not in slugs and row["slug"], f"{code} slug missing or duplicate")
        slugs.add(row["slug"])
        urls = [u for u in row["official_source_urls"].split(";") if u.startswith("https://")]
        check(len(urls) >= 2, f"{code} needs at least two official source URLs")
        keywords = parse_json(row["seo_keywords"], f"{code} seo_keywords")
        check(isinstance(keywords, list) and keywords, f"{code} SEO keywords missing")

    for code in ALLOWLIST:
        seed = seed_map[("BGD",code,"tourism")]
        audit = audit_map[("BGD",code,"tourism")]
        check(seed["verification_status"] == "pending_review", f"{code} seed must be pending before full import")
        check(seed["official_source_last_checked"] == TODAY, f"{code} seed checked date mismatch")
        for field in ("cross_check_source_url","cross_check_source_name","cross_check_source_last_checked"):
            check(not seed.get(field,"").strip(), f"{code} seed {field} must be blank to avoid duplicate source creation")
        check(audit["verification_status"] == "verified" and audit["publish_status"] == "published" and audit["full_audit"].lower() == "true", f"{code} audit row is not approved")
        check(audit["passport_iso3"] == "BGD" and audit["travel_purpose"] == "tourism", f"{code} is not BGD tourism")
        check(audit["official_source_last_checked"] == TODAY and audit["cross_check_source_last_checked"] == TODAY, f"{code} source checked dates mismatch")
        check(audit["notes"].strip() == "", f"{code} notes field must be empty")
        for field, limit in {"visa_requirement_type":50,"maximum_permitted_stay":120,"entry_count":40,"application_channel":180,"application_fee":120,"processing_time":120}.items():
            check(len(audit[field]) <= limit, f"{code} {field} exceeds {limit} characters")
        check(audit["official_source_url"].startswith("https://") and audit["cross_check_source_url"].startswith("https://"), f"{code} official source URLs must use HTTPS")
        check(audit["required_documents"].strip(), f"{code} required documents missing")

        sources = parse_json(audit["additional_sources"], f"{code} additional_sources")
        check(isinstance(sources, list) and len(sources) >= (3 if code == "AC" else 2), f"{code} additional official sources insufficient")
        for source in sources:
            check(source.get("source_type") == "official" and source.get("source_name") and source.get("source_url","").startswith("https://"), f"{code} additional source metadata invalid")
            check(source.get("source_last_checked_at") == TODAY and source.get("verification_notes"), f"{code} additional source checked date/notes missing")

        photo = parse_json(audit["photo_specifications"], f"{code} photo_specifications")
        check(photo.get("source_name") and photo.get("source_url","").startswith("https://") and photo.get("checked_at") == TODAY, f"{code} photo source metadata missing")
        checklists = parse_json(audit["applicant_checklists"], f"{code} applicant_checklists")
        check(isinstance(checklists, list) and checklists, f"{code} applicant checklist missing")
        for checklist in checklists:
            check(checklist.get("source_name") and checklist.get("source_url","").startswith("https://") and checklist.get("checked_at") == TODAY, f"{code} checklist source metadata missing")
            check(isinstance(checklist.get("documents"), list) and checklist["documents"], f"{code} checklist documents missing")
            for document in checklist["documents"]:
                check(document.get("name") and document.get("status") in {"required","conditional","recommended"}, f"{code} checklist document status invalid")

        if code == "AC":
            check(audit["visa_required"] == "true" and audit["visa_free_eligibility"] == "false" and audit["visa_on_arrival_available"] == "false" and audit["evisa_available"] == "true", "AC eVisa flags invalid")
            check("GBP40" in audit["application_fee"] and "GBP60" in audit["application_fee"] and "GBP10" in audit["application_fee"], "AC fee schedule incomplete")
            check("21 days" in audit["processing_time"] and "6 weeks" in audit["processing_time"], "AC processing estimates incomplete")
            check("multiple-entry UK visa" in audit["transit_rule"] and "Bangladesh" in audit["transit_rule"], "AC discretionary UK condition missing")
            check(photo.get("status") == "partially_specified" and photo.get("file_format") == "JPEG" and photo.get("captured_at_vac") is False, "AC photo status/format invalid")
            check(not photo.get("dimensions_mm") and not photo.get("digital_dimensions_px") and not photo.get("max_file_size_bytes"), "AC photo dimensions/file limit must remain unspecified")
            check({c.get("applicant_type") for c in checklists} == {"employee","self_employed_or_business_owner","student","retired"}, "AC applicant checklists incomplete")
            check(any("immigration-rules-appendix-visitor-visa-national-list" in s.get("source_url","") for s in sources), "AC UK Home Office source missing")
        else:
            check(audit["visa_required"] == "false" and audit["visa_free_eligibility"] == "false" and audit["visa_on_arrival_available"] == "false" and audit["evisa_available"] == "false", "TA prior-permission flags invalid")
            check("not a visa" in audit["visa_requirement_type"].lower(), "TA must be classified as prior permission, not visa-free")
            check("GBP30" in audit["application_fee"] and "GBP15" in audit["application_fee"] and "GBP25" in audit["application_fee"], "TA landing charges/caveat incomplete")
            check("No universal maximum" in audit["maximum_permitted_stay"] and "No published SLA" in audit["processing_time"], "TA unpublished stay/processing terms missing")
            check(photo.get("status") == "not_specified" and photo.get("captured_at_vac") is False, "TA photo status/capture invalid")
            check(not photo.get("dimensions_mm") and not photo.get("digital_dimensions_px"), "TA photo dimensions must remain unspecified")
            check({c.get("applicant_type") for c in checklists} == {"tourist"}, "TA must have a tourist-only checklist")

    seed_out = output / "verified_seed_batch_45.csv"
    with seed_out.open("w", newline="", encoding="utf-8") as handle:
        writer = csv.DictWriter(handle, fieldnames=seed_headers)
        writer.writeheader()
        for key in sorted(seed_map):
            row = dict(seed_map[key])
            for field in ("cross_check_source_url","cross_check_source_name","cross_check_source_last_checked","additional_sources","publish_status"):
                if field in row:
                    row[field] = ""
            writer.writerow(row)

    audit_out = output / "verified_full_audit_batch_45.csv"
    with audit_out.open("w", newline="", encoding="utf-8") as handle:
        writer = csv.DictWriter(handle, fieldnames=audit_headers)
        writer.writeheader()
        for key in sorted(audit_map):
            writer.writerow(audit_map[key])

    (output / "country_master_backfill_batch_21.csv").write_text(master_path.read_text(encoding="utf-8-sig"), encoding="utf-8")
    print("BATCH45_ALLOWLIST_VALIDATED=AC,TA")
    print("BATCH45_COUNTRY_MASTER_ROWS=2")
    print("BATCH45_VERIFIED_AUDIT_ROWS=2")
    print("BATCH45_PENDING_SEED_ROWS=2")
    print(f"BATCH45_CHECKED_DATE={TODAY}")

if __name__ == "__main__":
    main()
