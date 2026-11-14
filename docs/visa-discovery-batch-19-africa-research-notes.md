# Batch 19 — Africa visa discovery and research

Checked: 14 November 2026  
Passport: Bangladesh ordinary passport  
Purpose: Tourism / short visitor stay

## Result

Ten previously unstarted African destinations were progressed: Algeria, Comoros, Equatorial Guinea, Eswatini, Lesotho, Liberia, Mauritania, Senegal, Sierra Leone and São Tomé and Príncipe. Five research records are verified (Algeria, Liberia, Lesotho, Sierra Leone, São Tomé and Príncipe); five remain pending (Comoros, Equatorial Guinea, Eswatini, Mauritania, Senegal). All ten country-master rows already exist in production. No new country rows were created.

## Verified findings

- **Algeria:** prior tourist visa from an Algerian mission. Dhaka Embassy checklist: duplicate form, six-month passport/copy, two photos, travel insurance/repatriation assistance and hotel/host/travel-agency evidence. Max 90 days per entry and 180 days cumulative per year. 48-hour statement applies only to organised tours. Dhaka fee is unpublished; San Francisco fee is not transferable.
- **Liberia:** prior tourist visa; official Embassy says non-Liberian passport holders need a visa, except ECOWAS citizens with valid ECOWAS passports. Bangladesh is not ECOWAS. Six-month passport, photo, hotel/tourism-company letter and yellow-fever certificate. MFA lists a Dhaka Consulate General but does not publish its local fee or SLA.
- **Lesotho:** Bangladesh explicitly listed as visa-required. M1000 single / M2000 multiple. Manual Home Affairs application. South African transit visa explicitly required for Bangladeshi applicants. Government charter says 3–5 working days; Embassy says up to 10. A 72-hour pre-approved arrival letter is not walk-up VOA.
- **Sierra Leone:** MFA says all visitors need a visa/entry permit, but tourists may obtain VOA; ECOWAS only exemption. Single entry usually 3-month validity and up to 30 days; multiple entry 6 months or 1 year with up to 30 days per visit. Six-month passport, two photos, yellow-fever certificate and round-trip ticket. MFA fee unpublished; NTB-hosted 2025 FAQ reports USD80 for other passports, reconfirm at arrival.
- **São Tomé and Príncipe:** conditional exemption up to 15 days for any other nationality, including Bangladesh, with passport valid more than 3 months and valid Schengen or US visa. Otherwise tourist eVisa/consular visa. SMF portal says 7 working days, return ticket, accommodation, EUR100/day funds, passport validity 3 months beyond visa end, and payment at border. Airport processing is a fallback if online visa cannot be obtained. Separate tourist tax EUR25 São Tomé / EUR10 Príncipe.

## Pending blockers

- **Eswatini:** official Home Affairs nationality table could not be retrieved due timeout; eVisa portal only accepts eligible nationalities and does not expose Bangladesh's eligibility. Need direct BGD row and eVisa result.
- **Senegal:** Ministry visa-free schedule does not list Bangladesh but is based on a 2015 regime. 2026 consulate page confirms nationality-dependent categories but not BGD. Need current mission confirmation.
- **Mauritania:** official ANRPTS portal has 30/90/360-day eVisa options. Published exemption list names 11 countries, not Bangladesh, but dynamic BGD nationality selector and fee were not retrievable. Need current BGD result.
- **Comoros:** Law 88-025 requires visa for foreign nationals, tourist visa up to 45 days and departure ticket. UN Mission page says 45-day visa on spot for EUR60 but is undated. Need current BGD exemption, arrival and fee confirmation.
- **Equatorial Guinea:** Decree 49/2019 sets tourist visa up to 90 days for nationalities requiring it. Government launched eVisa in 2023, but a 29 October 2026 Government release says the VFS contract/platform is under review. No BGD nationality result or fee found. Need Immigration confirmation.

## Official source register

- Algeria: https://embdhaka.mfa.gov.dz/en/services-for-foreigners/entry-visa-to-algeria-1 ; https://cgsanfrancisco.mfa.gov.dz/en/visa-fees
- Liberia: https://embassyofliberia.be/visiting-tourism/ ; https://mofa.gov.lr/index.php/general/foreign-missions
- Lesotho: https://www.lesothoemb-usa.gov.ls/visa/ ; https://www.gov.ls/eservice/format-for-non-citizens-service-delivery-charter/
- Sierra Leone: https://www.mfaic.gov.sl/visa/ ; https://www.mfaic.gov.sl/consular-services/ ; NTB-hosted 2025 FAQ: https://ntb.gov.sl/wp-content/uploads/2025/06/Voyages-Afriq-Magazine.pdf
- São Tomé and Príncipe: https://turismo.gov.st/fr/guide-pratique/informations-utiles ; https://www.smf.st/evisa/ ; https://turismo.gov.st/en/guide-pratique/tourism-tax
- Eswatini: https://www.gov.sz/index.php?id=314 ; https://evisa.gov.sz/FAQ
- Senegal: https://www.diplomatie.gouv.sn/demande-de-visa ; https://consulsen-paris.gouv.sn/demande-de-visa/
- Mauritania: https://anrpts.gov.mr/visa/requestvisa ; https://anrpts.gov.mr/fr/service-de-lagance/visitors ; https://www.diplomatie.gov.mr/ar/node/50
- Comoros: https://justice.gouv.km/texte/loi-n88-025-relative-aux-conditions-de-sejour-des-etrangers-aux-comores/ ; https://www.un.int/comoros/fr/comoros/passport-and-visa-process
- Equatorial Guinea: https://www.guineaecuatorialpress.com/index.php/noticias/ya_esta_operativo_el_visado_online_en_guinea_ecuatorial ; https://www.guineaecuatorialpress.com/noticias/decreto_presidencial_que_regula_el_visado_de_estancia_en_guinea_ecuatorial ; https://www.guineaecuatorialpress.com/index.php/noticias/el_gobierno_analiza_el_contrato_de_vfs_global_para_reforzar_el_sistema_de_visados_en_linea_y_potenciar_la_promocion_turistica ; https://equatorialguinea-evisa.com/

## Production and publication status

New research rows staged: 10; research-verified in files: 5; pending/draft: 5. Production inserts: 0; updates: 0; new verified records: 0; new publications: 0. All are research-file classifications only.

The latest successful production audit remains the 14 November 2026 05:37:12 UTC backend deployment. The current deploy workflow performs rsync --delete, dependency installation, cache operations and several import/publish commands before its audit step; it is not read-only and was not triggered. The guarded importer still requires privileged MySQL access for an isolated backup-restore test. No safeguard was bypassed.
