# Batch 20 — Kosovo country-master backfill and Bangladesh visa research

Checked: 14 November 2026  
Passport: Bangladesh ordinary passport  
Purpose: Tourism / short visitor stay

## Why this batch exists

The fresh read-only production audit confirms the discovery universe has 250 destinations but the production country table has 249 rows. Kosovo (XK / XKX) is the only missing country row. Kosovo is already present in the master discovery ledger, so this is a country-table backfill, not a newly discovered master destination.

## Bangladesh ordinary-passport visa finding

Bangladesh ordinary passport holders require a prior Kosovo short-stay Visa C. Bangladesh is not on Kosovo's official short-stay visa-exempt nationality list. The Official Gazette's Kosovo–Bangladesh visa-free agreement is expressly limited to diplomatic and official passports, so it does not exempt ordinary passports.

A conditional exemption applies to nationals of visa-required countries who hold either a valid biometric residence permit issued by a Schengen state or a valid multiple-entry Schengen visa. The Kosovo Embassy portal says such travellers may enter, transit and stay for up to 15 days.

The official Kosovo visa page states Visa C is for visits shorter than three months; the general foreigners law allows stays up to three months per six-month period. Applications must be submitted in person at the relevant Kosovo diplomatic/consular mission. The official Embassy portal lists the Kosovo Embassy in Dhaka. Visa C fee: EUR 40. Passport must be valid at least three months after departure and have two blank pages. The checklist also requires an ICAO-standard photo not older than one month, evidence of purpose, accommodation or means for accommodation, sufficient funds, valid health insurance, translations into English or an official Kosovo language, and two copies. Processing time is not published.

## Country row backfill

The existing countries schema already has unique iso2, iso3 and slug columns; no migration is needed. KosovoCountryBackfillSeeder uses Country::firstOrCreate(['iso2' => 'XK'], ...), so it inserts the missing row only and does not overwrite an existing country. It keeps is_published=false.

The seeder is staged only and has not been executed in production. The Kosovo visa requirement must not be imported until the country row is inserted and read back successfully.

## Production state

- Master destination universe: 250
- Production country rows: 249
- Missing country row: Kosovo (XK)
- Existing verified/published Bangladesh tourism requirements: 41
- New Kosovo country row inserted: 0
- New Kosovo visa requirement imported: 0
- New Kosovo requirement published: 0

## Official sources

- Kosovo visa regime, Visa C application, exemptions, EUR40 fee and required documents: https://ambasadat.net/visas/
- Kosovo Embassy in Dhaka and mission contact: https://ambasadat.net/Bangladesh/
- Kosovo–Bangladesh visa-free agreement limited to diplomatic and official passports: https://gzk.rks-gov.net/ActDetail.aspx?ActID=69879
- Kosovo Law No. 04/L-219 on Foreigners: https://cps.rks-gov.net/wp-content/uploads/2020/09/LAW_NO._04_L-219_ON_FOREIGNERS.pdf

## Publication status

The Kosovo visa record is research-verified in the staged CSV only. It is not imported, database-verified or published. The country row is staged with an idempotent seeder and remains unpublished.
