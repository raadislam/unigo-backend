# UniGo destination discovery batch 46 — Ascension Island

**Checked:** 15 November 2026  
**Destination:** Ascension Island, a British Overseas Territory and constituent part of Saint Helena, Ascension and Tristan da Cunha  
**Data key:** `AC` (exceptionally reserved alpha-2 code; no ISO-3 code assigned)  
**Passport/purpose:** Bangladesh ordinary passport, tourism/visitor

## Decision

**Research-complete; suitable for a controlled eVisa publication batch.** The AIG FAQ states that all nationalities require an eVisa except the specifically listed exempt classes. The current AIG eligibility page separately lists nationalities to which it does not issue eVisas; Bangladesh is not on that list. This supports the visitor eVisa route for an ordinary Bangladesh passport, while preserving that visa issuance remains an AIG decision. No general visa-on-arrival facility exists.

AIG separately states that nationals who require a UK visa may be required to show a multiple-entry UK visa valid for both outward and return journeys before an Ascension eVisa is issued. Bangladesh is on the UK Home Office visa-national list. This is a **possible, case-dependent condition**, not an automatic requirement; the public record must preserve that distinction and advise applicants to confirm with AIG before travel.

## Tourism entry route

- **Route:** Visitor eVisa, applied for online through the official AIG eVisa portal.
- **Visa required:** Yes, unless the applicant falls within one of AIG's narrowly defined official-duty/crew exemptions. Ordinary leisure tourists should not assume an exemption.
- **Visa on arrival:** No general visa-on-arrival facility. The separate yacht landing-permit procedure is not a general tourist visa-on-arrival route.
- **Stay:** Single entry, up to 3 months per visit; the same individual may not receive more than 3 months cumulatively in any 12-month period.
- **Passport validity:** At least 6 months remaining at the point of entry.
- **Application timing:** AIG advises applying at least 14 days before arrival for the standard fee.
- **Fees (effective 1 January 2026):** Visitor eVisa GBP 40 standard; GBP 60 fast-track/priority; GBP 10 for applicants under 12. Fees are non-refundable per application. Fast-track applies where arrival is within 14 days or priority is requested.
- **Processing:** AIG targets 90% of applications within 21 days and all within 6 weeks; fast-track applications are targeted within 14 days. These are targets, not guarantees.
- **Payment:** Secure card payment or bank transfer through the official application process.
- **Application:** Separate application per traveller; create an online account, upload the required photo and insurance evidence, enter travel dates and accommodation details, and submit payment. No authorised Bangladesh VAC route is identified; the official route is online.
- **Insurance:** Mandatory for the full stay. It must cover all medical treatment and emergency air evacuation to the applicant's country of residence. Air-evacuation cover must be at least GBP 500,000; GBP 1 million overall cover is advised. Pre-existing conditions must be declared and covered. Proof is requested during application and must be produced at entry.
- **Supporting documents:** A scanned photograph and medical insurance are the two documents the FAQ says must be provided. Onward itinerary and accommodation information are supporting documents for stays of 48 hours or more. For applicants under 18 travelling without a parent or with someone other than a parent, AIG strongly recommends a signed parental letter and the parent's/guardian's passport bio page.
- **Funds:** No fixed minimum funds amount or standard bank-statement requirement is published in the reviewed FAQ. Do not invent a financial threshold.
- **Return travel:** The FAQ does not state a universal return-ticket rule. It requests onward itinerary details as supporting evidence for transit and stays of 48 hours or more.
- **Health:** Applicants must declare specified contagious diseases if currently suffering from them or if they suffered from them within the preceding 12 months. Yellow fever is on the declaration list; the FAQ does not state a universal yellow-fever vaccination-certificate requirement.
- **Biometrics:** AIG may require fingerprints and a facial photograph as biometric data. This is not evidence of a VAC photo-capture service.
- **Transit:** A transit eVisa allows up to 7 days per journey. Double transit permits a return stopover within 3 months. Stays longer than the transit allowance require the visitor category.
- **Travel routing:** AIG describes air access via South Africa and St Helena on the monthly SA Airlink service. Bangladeshi travellers must check South African entry/transit requirements separately for their itinerary. Do not assume Ascension permission covers South African transit.

## Bangladesh-specific eligibility analysis

1. AIG's official FAQ states that all nationalities, including British citizens, require eVisas except the specified exemption classes.
2. AIG's current eligibility page lists the nationalities to which it does not issue eVisas: Belarus, China, Egypt, Hong Kong, Iran, Libya, Macau, North Korea, Russia, Syria, Taiwan, Ukraine and Vietnam. Bangladesh is not listed.
3. The UK Home Office Appendix Visitor Visa National List includes Bangladesh. AIG's FAQ says nationals who require a UK visa may be required to show a multi-entry UK visa valid for both outward and return journeys. The word **may** must be preserved; this is a potential condition requiring AIG confirmation, not a universal mandatory document proven for every Bangladeshi applicant.

## Photo specification

**Status: partially specified.** The AIG FAQ requires a JPEG photograph that clearly shows the applicant's face and says it does not have to be a specific passport photo. The reviewed FAQ does not publish printed dimensions, digital pixel dimensions, aspect ratio, file-size limit, background colour, head/face measurement, photo age or printed-copy count. Do not classify this as an officially confirmed universal no-fixed-size rule. AIG does not accept images surrounded by blank space, group photos or passport-scan images with holograms visible. The photo is uploaded online. AIG may separately require biometric fingerprints and a facial photograph; that is not a VAC photo-capture service.

## Applicant checklist

The same official route applies to employee, self-employed, student and retired applicants. The four profile checklists contain the same core required items and distinguish the UK visa, onward itinerary, accommodation, under-18 documents and independently verifiable English translations for non-English supporting documents as conditional or if-applicable rather than universally mandatory.

## Source quality and limitations

| Official source | Use |
|---|---|
| [AIG eVisa FAQ](https://www.ascension-visas.com/faq) | Primary source for entry categories, fees, stay, passport validity, insurance, documents, photo guidance, transit and processing |
| [AIG eVisa eligibility information](https://www.ascension-visas.com/useful-information) | Current AIG eVisa nationality exclusion list; Bangladesh is not listed |
| [UK Home Office Appendix Visitor: Visa National List](https://www.gov.uk/guidance/immigration-rules/immigration-rules-appendix-visitor-visa-national-list) | Confirms Bangladesh is a UK visa-national country for the conditional AIG UK-visa caveat |

The AIG government immigration page at `https://www.ascension.gov.ac/travel-and-shipping/immigration` returned HTTP 502 during this check, so it is not used as a supporting source. The official AIG eVisa portal and its eligibility page were accessible and are the primary evidence used.

## Data and publication controls

- `AC` is an exceptionally reserved alpha-2 code used for Ascension Island; no ISO-3 code is assigned. Do not invent one.
- The shared frontend flag component already has a tested AC SVG fallback.
- The batch data sets `verification_status=verified` and `publish_status=published` only for the allowlisted BGD/tourism row; the production workflow must still pass backup, isolated restore, dry-run, database readback and public API checks before the row is counted as imported/published.
- The AIG multi-entry UK visa caveat remains conditional in all public text and applicant checklists.
- No government fee is conflated with a VAC service charge; no Bangladesh VAC charge is applicable because the official application route is online.
