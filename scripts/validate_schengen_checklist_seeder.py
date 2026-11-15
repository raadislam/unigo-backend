from pathlib import Path
import re

source = Path("database/seeders/SchengenBangladeshChecklistSupplementSeeder.php").read_text(encoding="utf-8")

expected = {
    "AT", "BE", "BG", "HR", "CZ", "DK", "EE", "FI", "FR", "DE",
    "GR", "HU", "IS", "IT", "LV", "LI", "LT", "LU", "MT", "NL",
    "NO", "PL", "PT", "RO", "SK", "SI", "ES", "SE", "CH",
}

group_blocks = re.findall(r"'countries'\s*=>\s*\[([^\]]+)\]", source)
group_codes = set()
for block in group_blocks:
    group_codes.update(re.findall(r"'([A-Z]{2})'", block))

unverified_match = re.search(r"\$unverified\s*=\s*\[(.*?)\n\s*\];", source, re.S)
if not unverified_match:
    raise SystemExit("Schengen unverified-country map is missing")

unverified_codes = set(re.findall(r"'([A-Z]{2})'\s*=>", unverified_match.group(1)))
actual = group_codes | unverified_codes

if actual != expected:
    raise SystemExit(f"Schengen coverage mismatch: missing={sorted(expected - actual)} extra={sorted(actual - expected)}")

if len(group_codes) + len(unverified_codes) != 29:
    raise SystemExit("Schengen country mapping contains duplicate or missing country entries")

for applicant_type in ("employee", "self_employed_or_business_owner", "student", "retired"):
    if f"'{applicant_type}' => [" not in source:
        raise SystemExit(f"Applicant checklist profile missing: {applicant_type}")

for code in ("BG", "GR", "LT", "RO"):
    if f"'{code}' => 'No Bangladesh-specific" not in source:
        raise SystemExit(f"Unverified country status missing: {code}")

if "if ($iso2 === 'IT')" not in source:
    raise SystemExit("Italy-specific apostille condition is missing")

if "if ($iso2 === 'HR')" not in source:
    raise SystemExit("Croatia conditional apostille condition is missing")

if "in_array($iso2, ['IT', 'MT', 'SK'], true)" in source:
    raise SystemExit("Italy apostille rule must not be assumed for Malta and Slovakia")

if "The Italy VFS apostille rule has not been independently confirmed" not in source:
    raise SystemExit("Malta/Slovakia apostille limitation is not documented")

if "status' => 'not_verified'" not in source or "status' => 'partially_specified'" not in source:
    raise SystemExit("Unverified and partially specified photo statuses must be represented")

if "2026-11-15" not in source:
    raise SystemExit("Checklist research date is missing")

print("SCHENGEN_CHECKLIST_COVERAGE_OK countries=29 verified_routes=25 unverified_routes=4 profiles=4 apostille=country_specific")
