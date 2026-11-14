#!/usr/bin/env python3
import argparse
import csv
import json
import os
import re
import sys
from collections import Counter
from datetime import date


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
        if not any(source.get("source_type") == "official" and source.get("is_primary") is True for source in sources):
            missing_primary_sources.append(iso2)

    if len(published_codes) != len(set(published_codes)):
        fail("Production has multiple active published BGD tourism requirements for the same destination.")
    if missing_primary_sources:
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
        fail("Production public directory does not match published country flags and active verified BGD tourism requirements.")

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
        "",
        "The command used for this snapshot is read-only. No database rows, caches, migrations or application files were modified.",
    ]
    if missing_country_rows:
        warn("Country master rows absent from production: " + ", ".join(missing_country_rows))
    if extra_country_rows:
        warn("Production country rows absent from discovery ledger: " + ", ".join(extra_country_rows))
    rendered = "\n".join(summary) + "\n"
    print(rendered)
    summary_path = os.environ.get("GITHUB_STEP_SUMMARY")
    if summary_path:
        with open(summary_path, "a", encoding="utf-8") as handle:
            handle.write(rendered)


if __name__ == "__main__":
    main()
