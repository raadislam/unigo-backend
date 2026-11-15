# UniGo destination discovery batch 45 — Tristan da Cunha

Checked: 15 November 2026  
Passport scope: Bangladesh ordinary passport  
Purpose: tourism / visitor

## Decision

**Tristan da Cunha (exceptionally reserved code TA) is ready for a carefully caveated no-visa / prior-permission record.** The official visitor guidance says no visas are required for visitors, but every visitor must obtain advance approval from the Administrator / Island Council and a landing stamp. This is not visa-free entry: permission is a material precondition.

**Ascension Island (exceptionally reserved code AC) remains research-only.** Its official eVisa FAQ lists a broad nationality exclusion list that does not include Bangladesh, but the live application nationality selector result for an ordinary Bangladesh passport could not be established from the accessible official page. The FAQ also says UK-visa nationals may be required to show a multiple-entry UK visa valid for outward and return travel. The exact application of that condition to a Bangladesh visitor's intended route needs confirmation. No country row or tourism requirement is created for AC.

## Tristan da Cunha — verified route

- No visa is required; prior approval from the Administrator / Island Council is required for all visitors.
- Visitors should email the Secretary to the Administrator with purpose, dates, nationality, age, preferred vessel and accommodation sought.
- A valid passport, confirmed and fully paid return passage, adequate medical insurance including evacuation to Cape Town, and sufficient funds are required.
- A police certificate may be requested; it is not stated as universally mandatory.
- Visitors receive a landing stamp in the passport.
- Main-island landing / harbour charge: GBP 30 for cruise passengers; GBP 15 for yacht passengers.
- No universal stay maximum, decision SLA, minimum remaining passport validity, or fixed photo specification is published in the reviewed visitor guidance.
- Vessel schedules can require planning more than a year ahead; this is a travel-planning constraint, not a processing-time guarantee.
- Two official pages disagree on the additional Nightingale / Inaccessible landing charge (GBP 20 versus GBP 25). The batch records only the consistent main-island charge and directs travellers to confirm outer-island charges separately.
- The reviewed visitor guidance is stated for all visitors and does not distinguish Bangladesh ordinary passports. Separate transit-country requirements remain itinerary-dependent.

## Official sources

1. Tristan da Cunha Government — Organising a Visit: https://www.tristandc.com/visitsorganise.php
2. Tristan da Cunha Government — Summary of Conditions and Charges for Visits: https://www.tristandc.com/visitsadmin.php
3. Tristan da Cunha Government — Information for Visiting Ships and Yachts: https://www.tristandc.com/visitsships.php
4. Tristan da Cunha Government — More Ways to Pay on Tristan da Cunha! (11 September 2026): https://www.tristandc.com/news-2026-09-11-pay-by-link.php

## Country code and flag handling

TA is an exceptionally reserved ISO 3166-1 alpha-2 code, not an officially assigned ISO 3166-1 country code; no ISO alpha-3 code is assigned. The existing countries schema permits a nullable iso3, and the existing shared frontend/admin flag components have a dedicated Tristan da Cunha SVG fallback. Both public frontend and admin deployment verifiers successfully returned HTTP 200 for that SVG asset on 15 November 2026. The production country row therefore leaves iso3 null rather than inventing an alpha-3 code.

## Import and publication

The approved batch 45 workflow is restricted to one row: TA / BGD / tourism. It creates the country row idempotently, seeds the tourism record as pending, validates and dry-runs the full-audit importer against an isolated restore of the production backup, then imports, reads back, verifies and publishes the single allowlisted record. Any production readback failure invokes the importer's unpublish-only path; it never deletes the country row or unrelated data.
