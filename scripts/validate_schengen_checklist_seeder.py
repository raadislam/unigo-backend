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

for code in ("LT", "RO"):
    if f"'{code}' => 'No Bangladesh-specific" not in source:
        raise SystemExit(f"Unverified country status missing: {code}")

if "if ($iso2 === 'IT')" not in source:
    raise SystemExit("Italy-specific checklist condition is missing")

italy_requirements = [
    "40 × 35 mm",
    "Two recent and identical photographs",
    "who will pay travel and stay costs",
    "City Corporation-issued trade licence",
    "RJSC-issued certificate of incorporation",
    "Personal bank statement issued by any bank operating in Bangladesh for the last 6 months",
    "Fidejussione Bancaria",
    "Passenger Registration Number or Reservation Number",
    "accommodation for the entire period of stay in all Schengen Member States",
    "hospitalisation and repatriation for at least EUR 30,000",
    "marriage certificate",
    "divorce certificate",
    "spouse death certificate",
    "original consent of both parents or custodian",
    "photocopies of both parents’ passports",
    "proof of financial means of both parents or legal guardian",
    "original birth certificate issued by the relevant authority",
    "competent Bangladeshi courts proving legal-guardian status",
    "only one apostille for each submitted document is accepted",
    "valid Bangladeshi residence permit",
    "https://visa.vfsglobal.com/one-pager/italy/bangladesh/english/pdf/TURISM_updated.pdf",
    "https://ambdhaka.esteri.it/wp-content/uploads/2026/01/TOURISM_updated.pdf",
]
for requirement in italy_requirements:
    if requirement.lower() not in source.lower():
        raise SystemExit(f"Italy VFS checklist detail or source missing: {requirement}")

if "The VFS English passport-validity wording says three months beyond visa validity" not in source:
    raise SystemExit("Italy passport-validity language discrepancy must be disclosed")

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

if "VFS Global — Bulgaria Visa Application Centre Bangladesh" not in source or "VFS Global — Greece Visa Application Centre Bangladesh" not in source:
    raise SystemExit("Bulgaria or Greece Bangladesh VFS route is missing")

if "captured_at_vac" not in source or "The accessible Bangladesh portal does not state the printed-photo size or count" not in source:
    raise SystemExit("Bulgaria live-photo capture limitation is not documented")

vfs_group_requirements = {
    "DE": [
        "Original personal bank statements for at least the last 6 months",
        "Memorandum and Articles of Association",
        "annual tax-payment confirmation",
        "Nikah Nama in Bangla and English",
        "All photocopies on A4 paper",
    ],
    "DK": [
        "ApplyVisa",
        "printed receipt for online payment",
        "VU2 online invitation form",
        "real-estate ownership",
        "not purchase a flight ticket before visa approval",
    ],
    "NL": [
        "unique code",
        "35 × 45 mm",
        "caregiver evidence",
        "Passenger Registration Number",
        "court permission for the minor to travel",
    ],
    "SE": [
        "form 119031",
        "last 6 months",
        "Passenger Registration Number",
        "company address, telephone and fax",
        "valid at least 3 months beyond intended departure from Schengen",
    ],
    "HR": [
        "last 3 months",
        "last 3 years",
        "salary certificates for the last 3 months",
        "notarised guardian consent",
        "apostille",
    ],
}
for country, fragments in vfs_group_requirements.items():
    for fragment in fragments:
        if fragment.lower() not in source.lower():
            raise SystemExit(f"VFS checklist content missing for {country}: {fragment}")

for source_url in (
    "https://visa.vfsglobal.com/one-pager/denmark/Bangladesh/english/pdf/visiting_family_or_friends_tourism.pdf",
    "https://visa.vfsglobal.com/one-pager/sweden/bangladesh/english/pdf/Tourist-Visa-Checklist-1.pdf",
    "https://visa.vfsglobal.com/one-pager/netherlands/bangladesh/english/",
    "https://visa.vfsglobal.com/bgd/en/fra",
):
    if source_url.lower() not in source.lower():
        raise SystemExit(f"VFS source URL missing: {source_url}")

print("SCHENGEN_CHECKLIST_COVERAGE_OK countries=29 verified_routes=27 unverified_routes=2 profiles=4 apostille=country_specific")
