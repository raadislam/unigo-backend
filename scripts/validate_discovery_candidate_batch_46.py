import csv
from pathlib import Path
from urllib.parse import urlparse

root = Path(__file__).resolve().parents[1]
candidate_path = root / "database/data/destination_discovery_batch_46.csv"
research_path = root / "docs/visa-discovery-batch-46-ascension-island-research.md"

with candidate_path.open(encoding="utf-8-sig", newline="") as handle:
    rows = list(csv.DictReader(handle))

if len(rows) != 1:
    raise SystemExit("Batch 46 must contain exactly one destination candidate.")

row = rows[0]
expected = {
    "iso2": "AC",
    "iso3": "",
    "destination_name": "Ascension Island",
    "slug": "ascension-island",
    "region": "Africa",
    "subregion": "South Atlantic & Polar Territories",
    "discovery_status": "research_in_progress_batch_46",
    "production_status": "candidate_not_imported",
    "visa_record_status": "pending_live_BGD_nationality_selector_confirmation",
    "last_checked": "2026-11-15",
}

for key, value in expected.items():
    if row.get(key, "") != value:
        raise SystemExit(f"Batch 46 allowlist mismatch for {key}: expected {value!r}.")

if row.get("parent_jurisdiction") != "Saint Helena, Ascension and Tristan da Cunha (UK Overseas Territory)":
    raise SystemExit("Batch 46 parent jurisdiction is missing or incorrect.")

if row.get("country_row_status") != "candidate_country_master_not_imported":
    raise SystemExit("Batch 46 country master status must remain explicitly unimported before the guarded workflow.")

if row.get("source_status") != "official_AIG_eVisa_route_and_conditions_reviewed_BGD_selector_result_unconfirmed":
    raise SystemExit("Batch 46 source status does not preserve the Bangladesh-specific selector blocker.")

if "Ascension Island Government" not in row.get("next_action", "") or "nationality selector" not in row.get("next_action", ""):
    raise SystemExit("Batch 46 next action must request direct nationality-selector confirmation from Ascension Island Government.")

urls = [value.strip() for value in row.get("source_urls", "").split(";") if value.strip()]
required_domains = {
    "www.ascension.gov.ac",
    "www.ascension-visas.com",
    "www.gov.uk",
    "www.iso.org",
    "www.digitalpolicy.gov.hk",
}
domains = {urlparse(value).netloc for value in urls}
if not required_domains.issubset(domains):
    raise SystemExit("Batch 46 is missing one or more required primary official source domains.")

if any(urlparse(value).scheme != "https" for value in urls):
    raise SystemExit("Batch 46 source URLs must use HTTPS.")

notes = row.get("notes", "").lower()
for marker in (
    "bangladesh ordinary-passport eligibility remains unconfirmed",
    "live nationality selector",
    "multi-entry uk visa",
    "no visa record is created",
):
    if marker not in notes:
        raise SystemExit(f"Batch 46 notes are missing the required safety statement: {marker}")

if not research_path.is_file():
    raise SystemExit("Batch 46 research note is missing.")

research = research_path.read_text(encoding="utf-8")
for marker in (
    "Bangladesh ordinary-passport eligibility remains unconfirmed",
    "live nationality selector",
    "multi-entry UK visa",
    "No Bangladesh tourism requirement is created",
    "https://www.ascension-visas.com/faq",
    "https://www.ascension.gov.ac/travel-and-shipping/immigration",
):
    if marker not in research:
        raise SystemExit(f"Batch 46 research note is missing required evidence or blocker: {marker}")

print("BATCH46_DISCOVERY_ALLOWLIST_VALIDATED iso2=AC status=research_only")
