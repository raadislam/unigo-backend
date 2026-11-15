# UniGo non-ISO destination discovery ledger

Last reconciled: 15 November 2026  
Scope: practical sub-destinations and special regional travel regimes that are not separate ISO 3166-1 countries or territories.  
Parent country records remain authoritative for country-level visa rules. This ledger must never be merged into the ISO country master without an approved parent-child schema.

| Candidate key | Destination | Parent | ISO-2 / ISO-3 | Region | Discovery status | Bangladesh tourism research status | Production status | Photo status | Exact blocker | Next action | Research file |
|---|---|---|---|---|---|---|---|---|---|---|---|
| northern-cyprus-trnc | Northern Cyprus (TRNC-administered area) | Cyprus (CY) | Not assigned | Europe / Eastern Mediterranean | Discovered in batch 47 | Research-only; TRNC visitor framework reviewed; BGD eVisa/arrival route not confirmed; Türkiye double-transit notice lists Bangladesh | No country row, visa record, source record, API entry or public page created | Unresearched; no-fixed-size not confirmed; VAC capture not established | No ISO code / no child-destination schema; current BGD entry route, stay, fee, Turkish transit scope and photo/checklist require confirmation | Obtain written TRNC Immigration and Turkish Embassy Dhaka confirmation; decide child-destination model | [Batch 47 research](visa-discovery-batch-47-northern-cyprus-research.md) |
| xizang-tibet-cn | Xizang / Tibet Autonomous Region | China (CN) | Not assigned | Asia / East Asia | Discovered in batch 48 | Research-only; regional access controls and national foreigner travel-permit framework reviewed; Bangladesh-specific Tibet permit route not confirmed | No country row, visa record, source record, API entry or public page created | Unresearched; no-fixed-size not confirmed; VAC capture not established | No ISO code / no child-destination schema; current Tibet permit, tour/guide rules, application channel, fees, photo/checklist and Shigatse border-area suspension status require confirmation | Obtain written Xizang tourism/public-security confirmation; recheck Shigatse notice before travel; decide child-destination model | [Batch 48 research](visa-discovery-batch-48-xizang-research.md) |

| palmyra-atoll-us | Palmyra Atoll National Wildlife Refuge | United States (incorporated territory; U.S. Minor Outlying Islands grouping) | Not assigned | Oceania / Central Pacific | Discovered in batch 50 | Research-only; USFWS describes permit-only access through visiting vessels or specific partnerships; no general public flight route is documented; Bangladesh B-1/B-2 visa-bond commercial-air port condition creates unresolved route compatibility | No country row, visa record, source record, API entry or public page created | USFWS permit photo status unresearched; parent U.S. visa photo rules remain separate | No ISO code / no child-destination schema; written CBP/State Department confirmation required for bonded traveller's domestic Honolulu–Palmyra private-vessel route; USFWS permit fee, application channel and processing estimate not stated | Obtain written USFWS permit/fee/season details and CBP/State Department confirmation; retain research-only until parent-child schema and Bangladesh route are confirmed | [Batch 50 research](visa-discovery-batch-50-palmyra-atoll-research.md) |

## Reconciliation rules

1. Do not assign an invented ISO-2 or ISO-3 code to either candidate.
2. Do not create country-master rows or separate country-level visa requirements for these candidates.
3. Country-level visa rules remain attached to the parent country (Cyprus or China). A child page or structured permit note requires an explicit schema decision.
4. Keep entry, transit, regional permit, government fee, VAC fee, processing estimate, photo and checklist fields separate.
5. A candidate can move to production only after Bangladesh ordinary-passport applicability and all material route conditions are supported by current authoritative sources, the child-destination schema is approved, and the normal backup/restore/dry-run/readback gates pass.
6. On each run, compare this sidecar with the ISO country master and mark a candidate as represented only when its parent/child relationship is explicit. Do not count a sub-destination as a new sovereign country.

This sidecar currently contains **3 research-only candidates**. None are counted in the ISO country universe or public directory.
