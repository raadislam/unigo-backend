#!/usr/bin/env python3
import argparse
import csv
import json
import os
import re
import sys
from collections import Counter
from datetime import date, timedelta


def fail(message):
    print(f"::error::{message}")
    raise SystemExit(message)


def warn(message):
    print(f"::warning::{message}")


def active(record, today):
    start = (record.get("effective_from") or "")[:10]
    end = (record.get("effective_until") or "")[:10]
    return (not start or start <= today) and (not end or end >= today)


def main():
    parser = argparse.ArgumentParser()
    parser.add_argument("--audit-file", required=True)
    parser.add_argument("--ledger-file", required=True)
    args = parser.parse_args()

    with open(args.audit_file, encoding="utf-8") as handle:
        audit = json.load(handle)
    with open(args.ledger_file, newline="", encoding="utf-8-sig") as handle:
        ledger = list(csv.DictReader(handle))

    records = audit.get("records")
    countries = audit.get("country_table")
    public_countries = audit.get("public_country_directory")
    if not isinstance(records, list) or not isinstance(countries, list) or not isinstance(public_countries, list):
        fail("Production audit is missing records, country_table or public_country_directory arrays.")

    if audit.get("total") != len(records):
        fail("Production audit total does not match its records array.")
    if audit.get("country_table_count") != len(countries):
        fail("Production country_table_count does not match country_table rows.")
    if audit.get("public_country_directory_count") != len(public_countries):
        fail("Production public_country_directory_count does not match directory rows.")
    if audit.get("published_count") != sum(1 for row in records if row.get("is_published") is True):
        fail("Production published_count does not match record readback.")
    if audit.get("country_table_published_count") != sum(1 for row in countries if row.get("is_published") is True):
        fail("Country-table published count does not match readback.")
    if audit.get("country_table_unpublished_count") != sum(1 for row in countries if row.get("is_published") is False):
        fail("Country-table unpublished count does not match readback.")

    master_codes = [str(row.get("iso2") or "").strip().upper() for row in ledger]
    if any(not re.fullmatch(r"[A-Z]{2}", code) for code in master_codes):
        fail("Country discovery ledger contains a missing or malformed ISO2 code.")
    if len(master_codes) != len(set(master_codes)):
        fail("Country discovery ledger contains duplicate ISO2 codes.")
    master_set = set(master_codes)

    db_codes = [str(row.get("iso2") or "").strip().upper() for row in countries]
    if any(not re.fullmatch(r"[A-Z]{2}", code) for code in db_codes):
        fail("Production country table contains a missing or malformed ISO2 code.")
    if len(db_codes) != len(set(db_codes)):
        fail("Production country table contains duplicate ISO2 codes.")
    db_set = set(db_codes)
    missing_country_rows = sorted(master_set - db_set)
    extra_country_rows = sorted(db_set - master_set)

    bgd_tourism = [
        row for row in records
        if row.get("passport_nationality_iso3") == "BGD" and row.get("travel_purpose") == "tourism"
    ]
    today = date.today().isoformat()
    published_bgd = [
        row for row in bgd_tourism
        if row.get("verification_status") == "verified"
        and row.get("is_published") is True
        and active(row, today)
    ]
    pending_bgd = [row for row in bgd_tourism if row not in published_bgd]

    pending_record_diagnostics = []
    for row in pending_bgd:
        country = row.get("country") or {}
        iso2 = str(country.get("iso2") or "").upper()
        sources = row.get("sources") or []
        primary_sources = [
            {
                "url": source.get("source_url"),
                "checked_at": source.get("source_last_checked_at")
            }
            for source in sources
            if source.get("source_type") == "official" and bool(source.get("is_primary"))
        ]
        photo = row.get("photo_specifications") or {}
        checklists = row.get("applicant_checklists") or []
        checklist_types = [
            str(item.get("applicant_type") or "")
            for item in checklists
            if isinstance(item, dict)
        ] if isinstance(checklists, list) else []
        pending_record_diagnostics.append({
            "iso2": iso2,
            "verification_status": row.get("verification_status"),
            "is_published": row.get("is_published"),
            "visa_required": row.get("visa_required"),
            "visa_free_available": row.get("visa_free_available"),
            "visa_on_arrival_available": row.get("visa_on_arrival_available"),
            "evisa_available": row.get("evisa_available"),
            "maximum_permitted_stay": row.get("maximum_permitted_stay"),
            "application_fee": row.get("application_fee"),
            "processing_time": row.get("processing_time"),
            "photo_status": photo.get("status") if isinstance(photo, dict) else None,
            "applicant_checklist_types": checklist_types,
            "source_count": len(sources),
            "primary_official_sources": primary_sources
        })
    verified_bgd = [row for row in bgd_tourism if row.get("verification_status") == "verified"]

    published_codes = []
    missing_primary_sources = []
    for row in published_bgd:
        country = row.get("country") or {}
        iso2 = str(country.get("iso2") or "").upper()
        if not iso2:
            fail("A published BGD tourism record has no destination ISO2.")
        published_codes.append(iso2)
        sources = row.get("sources") or []
        if not any(source.get("source_type") == "official" and bool(source.get("is_primary")) for source in sources):
            missing_primary_sources.append(iso2)

    if len(published_codes) != len(set(published_codes)):
        fail("Production has multiple active published BGD tourism requirements for the same destination.")
    if missing_primary_sources:
        for row in published_bgd:
            country = row.get("country") or {}
            iso2 = str(country.get("iso2") or "").upper()
            if iso2 in missing_primary_sources:
                details = [
                    {
                        "source_type": source.get("source_type"),
                        "is_primary": source.get("is_primary"),
                        "source_url": source.get("source_url"),
                        "source_last_checked_at": source.get("source_last_checked_at")
                    }
                    for source in (row.get("sources") or [])
                ]
                print("PRIMARY_SOURCE_DIAGNOSTIC " + iso2 + " " + json.dumps(details, ensure_ascii=False))
        fail("Published BGD tourism records missing a primary official source: " + ", ".join(sorted(missing_primary_sources)))

    country_by_id = {row.get("id"): row for row in countries}
    expected_public_codes = {
        str(row.get("iso2") or "").upper()
        for row in countries
        if row.get("is_published") is True
    }
    for row in published_bgd:
        country_id = row.get("country_id")
        country = country_by_id.get(country_id) or row.get("country") or {}
        iso2 = str(country.get("iso2") or "").upper()
        if iso2:
            expected_public_codes.add(iso2)

    public_codes = [str(row.get("iso2") or "").upper() for row in public_countries]
    if len(public_codes) != len(set(public_codes)):
        fail("Production public country directory contains duplicate ISO2 codes.")
    if set(public_codes) != expected_public_codes:
        missing_from_directory = sorted(expected_public_codes - set(public_codes))
        unexpected_in_directory = sorted(set(public_codes) - expected_public_codes)
        country_flag_codes = sorted(
            str(row.get("iso2") or "").strip().upper()
            for row in countries
            if row.get("is_published") is True
        )
        print("PUBLIC_DIRECTORY_PARITY_DIAGNOSTIC " + json.dumps({
            "public_directory_count": len(public_codes),
            "expected_public_count": len(expected_public_codes),
            "missing_from_directory": missing_from_directory,
            "unexpected_in_directory": unexpected_in_directory,
            "country_flag_published_codes": country_flag_codes,
            "active_published_requirement_codes": sorted(published_codes),
            "published_requirements_with_empty_effective_dates": sorted(
                str((row.get("country") or {}).get("iso2") or "").upper()
                for row in published_bgd
                if not (row.get("effective_from") or "").strip() or not (row.get("effective_until") or "").strip()
            ),
        }, ensure_ascii=False))
        bahamas_records = [
            row for row in records
            if row.get("passport_nationality_iso3") == "BGD"
            and row.get("travel_purpose") == "tourism"
            and str((row.get("country") or {}).get("iso2") or "").upper() == "BS"
        ]
        print("BS_RECORD_DIAGNOSTIC " + json.dumps([
            {
                key: row.get(key)
                for key in (
                    "id", "country_id", "verification_status", "is_published",
                    "effective_from", "effective_until", "visa_required",
                    "visa_free_available", "visa_on_arrival_available", "evisa_available",
                    "official_source_url", "official_source_last_checked",
                    "created_at", "updated_at", "last_synced_at", "notes"
                )
            }
            for row in bahamas_records
        ], ensure_ascii=False))
        fail("Production public directory does not match published country flags and active verified BGD tourism requirements.")

    ledger_public_codes = {
        str(row.get("iso2") or "").strip().upper()
        for row in ledger
        if row.get("country_row_status") == "public_directory"
    }
    ledger_verified_codes = {
        str(row.get("iso2") or "").strip().upper()
        for row in ledger
        if row.get("visa_record_status") == "verified_published"
    }
    ledger_represented_codes = {
        str(row.get("iso2") or "").strip().upper()
        for row in ledger
        if row.get("discovery_status") == "represented"
    }
    ledger_pending_db_codes = {
        str(row.get("iso2") or "").strip().upper()
        for row in ledger
        if str(row.get("visa_record_status") or "").startswith("pending_db_record_")
    }
    pending_codes = {
        str((row.get("country") or {}).get("iso2") or "").strip().upper()
        for row in pending_bgd
        if (row.get("country") or {}).get("iso2")
    }
    if ledger_public_codes != set(public_codes):
        bahamas_records = [
            row for row in bgd_tourism
            if str((row.get("country") or {}).get("iso2") or "").strip().upper() == "BS"
        ]
        print("LEDGER_PUBLIC_PARITY_DIAGNOSTIC " + json.dumps({
            "ledger_public_only": sorted(ledger_public_codes - set(public_codes)),
            "production_public_only": sorted(set(public_codes) - ledger_public_codes),
            "ledger_verified_only": sorted(ledger_verified_codes - set(published_codes)),
            "production_verified_only": sorted(set(published_codes) - ledger_verified_codes),
        }, ensure_ascii=False))
        print("BS_RECORD_DIAGNOSTIC " + json.dumps([
            {
                key: row.get(key)
                for key in (
                    "id", "country_id", "verification_status", "is_published",
                    "effective_from", "effective_until", "visa_required",
                    "visa_free_available", "visa_on_arrival_available", "evisa_available",
                    "official_source_url", "official_source_last_checked",
                    "created_at", "updated_at", "last_synced_at", "notes"
                )
            }
            for row in bahamas_records
        ], ensure_ascii=False))
        fail("Discovery ledger public_directory codes do not match the production public country directory.")
    if ledger_verified_codes != set(published_codes):
        fail("Discovery ledger verified_published codes do not match active verified BGD tourism records.")
    if ledger_represented_codes != set(published_codes):
        fail("Discovery ledger represented codes do not match active verified BGD tourism records.")
    if ledger_pending_db_codes != pending_codes:
        fail("Discovery ledger pending_db_record codes do not match pending, expired or unpublished BGD tourism records.")

    all_bgd_codes = [
        str((row.get("country") or {}).get("iso2") or "").strip().upper()
        for row in bgd_tourism
        if (row.get("country") or {}).get("iso2")
    ]
    if len(all_bgd_codes) != len(set(all_bgd_codes)):
        fail("Production contains duplicate BGD tourism requirement keys, including unpublished records.")

    required_fields = {
        "requirement_type": lambda value: bool(str(value or "").strip()),
        "visa_required": lambda value: value is not None,
        "visa_free_available": lambda value: value is not None,
        "visa_on_arrival_available": lambda value: value is not None,
        "evisa_available": lambda value: value is not None,
        "maximum_permitted_stay": lambda value: bool(str(value or "").strip()),
        "entry_count": lambda value: bool(str(value or "").strip()),
        "application_fee": lambda value: bool(str(value or "").strip()),
        "processing_time": lambda value: bool(str(value or "").strip()),
        "required_documents": lambda value: bool(value),
        "application_channel": lambda value: bool(str(value or "").strip()),
        "passport_validity_rule": lambda value: bool(str(value or "").strip()),
        "blank_passport_pages_rule": lambda value: bool(str(value or "").strip()),
        "return_ticket_rule": lambda value: bool(str(value or "").strip()),
        "proof_of_funds_rule": lambda value: bool(str(value or "").strip()),
        "accommodation_rule": lambda value: bool(str(value or "").strip()),
        "travel_insurance_rule": lambda value: bool(str(value or "").strip()),
        "health_requirements": lambda value: bool(str(value or "").strip()),
        "transit_requirements": lambda value: bool(str(value or "").strip()),
        "official_source_checked_at": lambda value: bool(value),
        "last_synced_at": lambda value: bool(value),
    }
    missing_field_counts = Counter()
    missing_field_record_codes = []
    source_records_total = 0
    source_records_missing_dates = []
    stale_source_records = []
    primary_source_date_missing = []
    freshness_cutoff = date.today() - timedelta(days=365)
    for row in published_bgd:
        country = row.get("country") or {}
        iso2 = str(country.get("iso2") or "").upper()
        missing = []
        for field, validator in required_fields.items():
            if not validator(row.get(field)):
                missing_field_counts[field] += 1
                missing.append(field)
        if missing:
            missing_field_record_codes.append({"iso2": iso2, "fields": missing})
        sources = row.get("sources") or []
        primary_official = [
            source for source in sources
            if source.get("source_type") == "official" and bool(source.get("is_primary"))
        ]
        if not any(source.get("source_last_checked_at") for source in primary_official):
            primary_source_date_missing.append(iso2)
        for source in sources:
            source_records_total += 1
            raw_checked = source.get("source_last_checked_at")
            if not raw_checked:
                source_records_missing_dates.append({"iso2": iso2, "source_url": source.get("source_url")})
                continue
            try:
                checked_date = date.fromisoformat(str(raw_checked)[:10])
            except ValueError:
                source_records_missing_dates.append({"iso2": iso2, "source_url": source.get("source_url")})
                continue
            if checked_date < freshness_cutoff:
                stale_source_records.append({
                    "iso2": iso2,
                    "source_url": source.get("source_url"),
                    "source_last_checked_at": checked_date.isoformat()
                })

    photo_status_counts = Counter()
    photo_missing_codes = []
    photo_no_fixed_size_codes = []
    photo_not_specified_codes = []
    photo_not_applicable_codes = []
    photo_unclassified_codes = []
    photo_verified_codes = []
    photo_partially_specified_codes = []
    photo_vac_capture_codes = []
    photo_biometric_enrolment_codes = []
    photo_conditional_arrival_capture_codes = []
    for row in published_bgd:
        country = row.get("country") or {}
        iso2 = str(country.get("iso2") or "").upper()
        photo = row.get("photo_specifications")
        if not isinstance(photo, dict) or not photo:
            photo_status_counts["missing_structured_spec"] += 1
            photo_missing_codes.append(iso2)
            continue
        photo_status = str(photo.get("status") or "").strip().lower()
        if photo_status == "verified":
            photo_status_counts["structured_verified"] += 1
            photo_verified_codes.append(iso2)
        elif photo_status == "partially_specified":
            photo_status_counts["partially_specified"] += 1
            photo_partially_specified_codes.append(iso2)
        elif photo_status == "no_fixed_size_published":
            photo_status_counts["no_fixed_size_published"] += 1
            photo_no_fixed_size_codes.append(iso2)
        elif photo_status in {"not_published", "not_specified"}:
            photo_status_counts[photo_status] += 1
            photo_not_specified_codes.append(iso2)
        elif photo_status == "not_applicable":
            photo_status_counts["not_applicable"] += 1
            photo_not_applicable_codes.append(iso2)
        elif photo_status in {"unresearched", "missing", "unverified"}:
            photo_status_counts["unresearched"] += 1
            photo_missing_codes.append(iso2)
        elif photo_status in {"biometric_capture", "captured_at_vac", "captured_at_application_centre"}:
            photo_status_counts["biometric_capture_status"] += 1
        else:
            photo_status_counts["unclassified_status"] += 1
            photo_unclassified_codes.append(iso2)

        photo_capture_text = str(photo.get("photo_capture_method") or photo.get("photo_capture") or "").strip().lower()
        biometric_text = str(photo.get("biometric_capture") or "").strip().lower()
        photo_capture_negative = any(term in photo_capture_text for term in ("not captured", "no photo", "no photograph", "not taken", "not required", "not applicable", "not at vac", "not at the vac"))
        vac_flag = photo.get("captured_at_vac") is True or str(photo.get("captured_at_vac") or "").strip().lower() in {"true", "yes", "1"}
        photo_capture_positive = vac_flag or photo_status in {"biometric_capture", "captured_at_vac", "captured_at_application_centre"}
        if not photo_capture_negative and any(term in photo_capture_text for term in ("photo captured", "photograph captured", "photo taken", "photograph taken", "captured at vac", "photo capture at", "vfs captures the photo", "photo is captured at")):
            photo_capture_positive = True
        if photo_capture_positive and iso2 not in photo_vac_capture_codes:
            photo_vac_capture_codes.append(iso2)
            photo_status_counts["vac_or_photo_capture"] += 1

        biometric_negative = any(term in biometric_text for term in ("no biometric", "not captured", "not required", "not applicable", "no fingerprints", "no iris"))
        if not biometric_negative and any(term in biometric_text for term in ("fingerprint", "iris", "biometric enrolment", "biometric enrollment", "biometric capture")):
            photo_biometric_enrolment_codes.append(iso2)

        if any(term in biometric_text for term in ("photo may be taken on arrival", "photograph may be taken on arrival", "photo may be captured on arrival", "photo can be taken on arrival")):
            photo_conditional_arrival_capture_codes.append(iso2)

    status_counts = Counter(str(row.get("verification_status") or "missing") for row in records)
    summary = [
        "# UniGo read-only production visa audit",
        "",
        f"- Generated at: {audit.get('generated_at', 'not supplied')}",
        f"- Discovery master destinations: **{len(master_set)}**",
        f"- Production country rows: **{len(countries)}**",
        f"- Country rows missing from production: **{len(missing_country_rows)}**" + (f" ({', '.join(missing_country_rows)})" if missing_country_rows else ""),
        f"- Extra production country rows: **{len(extra_country_rows)}**" + (f" ({', '.join(extra_country_rows)})" if extra_country_rows else ""),
        f"- All visa requirement records: **{len(records)}**",
        f"- Verification status counts: **{dict(status_counts)}**",
        f"- Country rows published/unpublished: **{audit.get('country_table_published_count', 0)} / {audit.get('country_table_unpublished_count', 0)}**",
        f"- Public country directory: **{len(public_countries)}**",
        f"- BGD tourism requirement records: **{len(bgd_tourism)}**",
        f"- BGD tourism verified records: **{len(verified_bgd)}**",
        f"- BGD tourism actively published and verified: **{len(published_bgd)}**",
        f"- BGD tourism pending, unverified, expired or unpublished: **{len(pending_bgd)}**",
        f"- Published BGD tourism records missing primary official sources: **{len(missing_primary_sources)}**",
        f"- Published BGD tourism records with all required structured fields present: **{len(published_bgd) - len(missing_field_record_codes)} / {len(published_bgd)}**",
        f"- Missing required fields by field: **{dict(missing_field_counts)}**",
        f"- Records missing a checked date on every primary official source: **{len(primary_source_date_missing)}**" + (f" ({', '.join(sorted(primary_source_date_missing))})" if primary_source_date_missing else ""),
        f"- Source records missing or invalid checked dates: **{len(source_records_missing_dates)} / {source_records_total}**",
        f"- Source records older than 365 days: **{len(stale_source_records)}**",
        f"- Duplicate BGD tourism requirement keys including unpublished records: **0**",
        f"- Published BGD tourism photo status counts: **{dict(photo_status_counts)}**",
        f"- Structured verified photo specifications: **{len(photo_verified_codes)} / {len(published_bgd)}**",
        f"- Partially specified photo requirements: **{len(photo_partially_specified_codes)}**",
        f"- Officially confirmed no fixed-size photo rule: **{len(photo_no_fixed_size_codes)}**" + (f" ({', '.join(sorted(photo_no_fixed_size_codes))})" if photo_no_fixed_size_codes else ""),
        f"- VAC/application-centre or other photo capture confirmed: **{len(photo_vac_capture_codes)}**" + (f" ({', '.join(sorted(photo_vac_capture_codes))})" if photo_vac_capture_codes else ""),
        f"- Biometric enrolment confirmed (not necessarily a photograph): **{len(photo_biometric_enrolment_codes)}**" + (f" ({', '.join(sorted(photo_biometric_enrolment_codes))})" if photo_biometric_enrolment_codes else ""),
        f"- Conditional photo capture on arrival: **{len(photo_conditional_arrival_capture_codes)}**" + (f" ({', '.join(sorted(photo_conditional_arrival_capture_codes))})" if photo_conditional_arrival_capture_codes else ""),
        f"- Missing/unresearched photo specifications: **{len(photo_missing_codes)}**" + (f" ({', '.join(sorted(photo_missing_codes))})" if photo_missing_codes else ""),
        f"- Not specified without confirmed no-fixed-size status: **{len(photo_not_specified_codes)}**" + (f" ({', '.join(sorted(photo_not_specified_codes))})" if photo_not_specified_codes else ""),
        f"- Photo requirements not applicable to the published entry route: **{len(photo_not_applicable_codes)}**" + (f" ({', '.join(sorted(photo_not_applicable_codes))})" if photo_not_applicable_codes else ""),
        f"- Unclassified photo statuses: **{len(photo_unclassified_codes)}**" + (f" ({', '.join(sorted(photo_unclassified_codes))})" if photo_unclassified_codes else ""),
        f"- Ledger/public directory parity: **{len(ledger_public_codes)} / {len(public_codes)} codes matched**",
        f"- Ledger verified/published parity: **{len(ledger_verified_codes)} / {len(published_codes)} codes matched**",
        f"- Ledger pending DB records: **{len(ledger_pending_db_codes)} / {len(pending_codes)} codes matched**",
        "",
        "The command used for this snapshot is read-only. No database rows, caches, migrations or application files were modified.",
    ]
    if missing_field_record_codes:
        warn("Published BGD tourism records with missing required fields: " + json.dumps(missing_field_record_codes[:50], ensure_ascii=False))
    if primary_source_date_missing:
        warn("Published BGD tourism records missing checked dates on primary official sources: " + ", ".join(sorted(primary_source_date_missing)))
    if source_records_missing_dates:
        warn("Official/other source records missing or carrying invalid checked dates: " + str(len(source_records_missing_dates)))
    if stale_source_records:
        warn("Source records with checked dates older than 365 days: " + json.dumps(stale_source_records[:50], ensure_ascii=False))
    if missing_country_rows:
        warn("Country master rows absent from production: " + ", ".join(missing_country_rows))
    if extra_country_rows:
        warn("Production country rows absent from discovery ledger: " + ", ".join(extra_country_rows))
    if pending_record_diagnostics:
        summary.extend(["", "## Pending BGD tourism record diagnostics", ""])
        for diagnostic in pending_record_diagnostics:
            summary.append(f"- **{diagnostic['iso2']}**: " + json.dumps(diagnostic, ensure_ascii=False))
    rendered = "\n".join(summary) + "\n"
    print(rendered)
    for diagnostic in pending_record_diagnostics:
        print("PENDING_RECORD_DIAGNOSTIC " + json.dumps(diagnostic, ensure_ascii=False))
    summary_path = os.environ.get("GITHUB_STEP_SUMMARY")
    if summary_path:
        with open(summary_path, "a", encoding="utf-8") as handle:
            handle.write(rendered)


if __name__ == "__main__":
    main()
