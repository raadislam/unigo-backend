import csv,json,sys
from pathlib import Path
root=Path(__file__).resolve().parents[1]
files=[root/'database/data/visa_full_audit_batch_06.csv',root/'database/data/visa_full_audit_batch_07.csv',root/'database/data/visa_full_audit_batch_08.csv',root/'database/data/visa_full_audit_batch_09.csv',root/'database/data/visa_full_audit_batch_10.csv',root/'database/data/visa_full_audit_batch_12.csv',root/'database/data/visa_full_audit_batch_13.csv',root/'database/data/visa_full_audit_batch_14.csv',root/'database/data/visa_full_audit_batch_15.csv',root/'database/data/visa_full_audit_batch_16.csv',root/'database/data/visa_full_audit_batch_17.csv',root/'database/data/visa_full_audit_batch_18.csv',root/'database/data/visa_full_audit_batch_19.csv']
seed_files=[root/'database/data/new_country_visa_batch_06_07.csv',root/'database/data/new_country_visa_batch_08.csv',root/'database/data/new_country_visa_batch_09.csv',root/'database/data/new_country_visa_batch_10.csv',root/'database/data/new_country_visa_batch_12.csv',root/'database/data/new_country_visa_batch_13.csv',root/'database/data/new_country_visa_batch_14.csv',root/'database/data/new_country_visa_batch_15.csv',root/'database/data/new_country_visa_batch_16.csv',root/'database/data/new_country_visa_batch_17.csv',root/'database/data/new_country_visa_batch_18.csv',root/'database/data/new_country_visa_batch_19.csv']
required={'passport_iso3','passport_nationality','destination_iso2','destination_name','travel_purpose','visa_requirement_type','visa_required','visa_free_eligibility','visa_on_arrival_available','evisa_available','maximum_permitted_stay','entry_count','passport_validity_rule','blank_passport_pages_rule','return_ticket_rule','proof_of_funds_rule','accommodation_rule','travel_insurance_rule','yellow_fever_or_health_rule','transit_rule','application_channel','application_fee','processing_time','required_documents','official_source_url','official_immigration_source_name','official_source_last_checked','cross_check_source_url','cross_check_source_name','cross_check_source_last_checked','verification_status','publish_status','review_notes','full_audit','additional_sources'}
limits={'visa_requirement_type':50,'entry_count':40,'maximum_permitted_stay':120,'passport_validity_rule':120,'blank_passport_pages_rule':120,'return_ticket_rule':120,'proof_of_funds_rule':120,'accommodation_rule':120,'travel_insurance_rule':120,'yellow_fever_or_health_rule':120,'transit_rule':120,'application_channel':120,'application_fee':120,'processing_time':120}
seen=set(); all_rows=[]; errors=[]
for path in files:
 with path.open(encoding='utf-8-sig',newline='') as f:
  reader=csv.DictReader(f); missing=required-set(reader.fieldnames or [])
  if missing: errors.append(f'{path.name}: missing columns {sorted(missing)}')
  rows=list(reader)
  if not rows: errors.append(f'{path.name}: no rows')
  for n,row in enumerate(rows,2):
   key=(row.get('passport_iso3'),row.get('destination_iso2'),row.get('travel_purpose'))
   if key in seen: errors.append(f'{path.name}:{n}: duplicate key {key}')
   seen.add(key); all_rows.append(row)
   if row.get('passport_iso3')!='BGD' or row.get('travel_purpose')!='tourism': errors.append(f'{path.name}:{n}: not BGD tourism')
   if row.get('full_audit','').lower()!='true': errors.append(f'{path.name}:{n}: full_audit must be true')
   if row.get('verification_status') not in {'verified','pending_review'}: errors.append(f'{path.name}:{n}: invalid verification_status')
   if row.get('publish_status') not in {'published','draft'}: errors.append(f'{path.name}:{n}: invalid publish_status')
   if row.get('publish_status')=='published' and row.get('verification_status')!='verified': errors.append(f'{path.name}:{n}: published record is not verified')
   if row.get('verification_status')=='pending_review' and row.get('publish_status')!='draft': errors.append(f'{path.name}:{n}: pending record must remain draft')
   if not row.get('required_documents','').strip(): errors.append(f'{path.name}:{n}: required_documents is empty')
   for field,limit in limits.items():
    if len(row.get(field,'') or '')>limit: errors.append(f'{path.name}:{n}: {field} exceeds {limit} characters')
   for field in ('official_source_url','cross_check_source_url'):
    if not (row.get(field,'') or '').startswith('https://'): errors.append(f'{path.name}:{n}: {field} must be HTTPS')
   for field in ('official_source_last_checked','cross_check_source_last_checked'):
    if not row.get(field,'').startswith('2026-11-14'): errors.append(f'{path.name}:{n}: {field} not checked today')
   try:
    sources=json.loads(row.get('additional_sources',''))
    if not isinstance(sources,list) or len(sources)<2: raise ValueError('expected at least two sources')
    for source in sources:
     if source.get('source_type') not in {'official','provider'} or not source.get('source_name','').strip() or not source.get('source_url','').startswith('https://'): raise ValueError('non-official or invalid source')
   except Exception as exc: errors.append(f'{path.name}:{n}: invalid additional_sources: {exc}')
seed_rows=[]; seed_keys=set()
for seed in seed_files:
 with seed.open(encoding='utf-8-sig',newline='') as f:
  current=list(csv.DictReader(f))
  for n,row in enumerate(current,2):
   key=(row.get('passport_iso3'),row.get('destination_iso2'),row.get('travel_purpose'))
   if key in seed_keys: errors.append(f'{seed.name}:{n}: duplicate seed key {key}')
   seed_keys.add(key); seed_rows.append(row)
audit_keys={(r.get('passport_iso3'),r.get('destination_iso2'),r.get('travel_purpose')) for r in all_rows}
if seed_keys!=audit_keys: errors.append(f'seed/full-audit key mismatch; seed-only={sorted(seed_keys-audit_keys)} audit-only={sorted(audit_keys-seed_keys)}')
if errors:
 print('VALIDATION FAILED'); print('\\n'.join(errors)); sys.exit(1)
print(f'VALIDATION PASSED: {len(all_rows)} unique BGD tourism records; {sum(r["publish_status"]=="published" for r in all_rows)} verified/publishable; {sum(r["publish_status"]=="draft" for r in all_rows)} pending; seed/full-audit keys match.')
