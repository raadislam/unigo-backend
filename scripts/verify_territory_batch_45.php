<?php

use App\Models\Country;
use App\Models\VisaRequirement;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

$root = getenv('UNIGO_APP_ROOT') ?: dirname(__DIR__);
require $root . '/vendor/autoload.php';
$app = require $root . '/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

function batch45Fail(string $message): never
{
    fwrite(STDERR, "BATCH45_VERIFY_FAILED: {$message}\n");
    exit(1);
}

function batch45Check(bool $condition, string $message): void
{
    if (!$condition) {
        batch45Fail($message);
    }
}

function batch45Record(string $code): ?VisaRequirement
{
    return VisaRequirement::query()->with(['country','sources'])
        ->where('passport_nationality_iso3','BGD')->where('travel_purpose','tourism')
        ->whereHas('country',fn($query)=>$query->where('iso2',$code))->first();
}

function batch45Counts(): array
{
    $codes=['AC','TA'];
    $query=VisaRequirement::query()->where('passport_nationality_iso3','BGD')->where('travel_purpose','tourism');
    $duplicates=DB::table('visa_requirements')->join('countries','countries.id','=','visa_requirements.country_id')
        ->where('visa_requirements.passport_nationality_iso3','BGD')->where('visa_requirements.travel_purpose','tourism')
        ->selectRaw('visa_requirements.country_id, visa_requirements.passport_nationality_iso3, visa_requirements.travel_purpose, COUNT(*) AS total')
        ->groupBy('visa_requirements.country_id','visa_requirements.passport_nationality_iso3','visa_requirements.travel_purpose')
        ->havingRaw('COUNT(*) > 1')->count();
    return [
        'country_count'=>Country::query()->count(),
        'base_country_count'=>Country::query()->whereNotIn('iso2',$codes)->count(),
        'requirement_count'=>(clone $query)->count(),
        'base_requirement_count'=>(clone $query)->whereHas('country',fn($q)=>$q->whereNotIn('iso2',$codes))->count(),
        'published_count'=>(clone $query)->where('verification_status','verified')->where('is_published',true)->count(),
        'base_published_count'=>(clone $query)->whereHas('country',fn($q)=>$q->whereNotIn('iso2',$codes))->where('verification_status','verified')->where('is_published',true)->count(),
        'pending_count'=>(clone $query)->where('verification_status','pending_review')->where('is_published',false)->count(),
        'base_pending_count'=>(clone $query)->whereHas('country',fn($q)=>$q->whereNotIn('iso2',$codes))->where('verification_status','pending_review')->where('is_published',false)->count(),
        'source_count'=>DB::table('visa_requirement_sources')->count(),
        'duplicate_keys'=>$duplicates
    ];
}

function batch45Collection(string $url): array
{
    $all=[];$page=1;$total=null;
    do {
        $response=Http::timeout(30)->get($url,['per_page'=>100,'page'=>$page]);
        batch45Check($response->successful(),"Public API request failed: {$url}");
        $payload=$response->json();
        batch45Check(is_array($payload)&&isset($payload['data'])&&is_array($payload['data']),"Invalid API response: {$url}");
        $total ??= data_get($payload,'meta.total');
        $all=array_merge($all,$payload['data']);
        $last=(int)data_get($payload,'meta.last_page',1);
        $page++;
    } while($page<=$last);
    return ['total'=>(int)$total,'data'=>$all];
}

$mode=$argv[1]??'preflight';
$codes=['AC','TA'];
$counts=batch45Counts();
batch45Check($counts['base_country_count']===250,'Existing country-master baseline changed.');
batch45Check($counts['base_requirement_count']===135,'Existing BGD tourism requirement baseline changed.');
batch45Check($counts['base_published_count']===133,'Existing published requirement baseline changed.');
batch45Check($counts['base_pending_count']===2,'Existing pending requirement baseline changed.');
batch45Check($counts['duplicate_keys']===0,'Duplicate BGD tourism requirement keys exist.');

foreach(['AC'=>['Ascension Island','ascension-island'],'TA'=>['Tristan da Cunha','tristan-da-cunha']] as $code=>$identity){
    $country=Country::query()->where('iso2',$code)->first();
    if($country){
        batch45Check($country->name===$identity[0]&&$country->slug===$identity[1],"Existing {$code} country identity conflicts.");
        batch45Check($country->iso3===null&&!(bool)$country->is_published&&$country->flag_emoji===null,"Existing {$code} country row is not safely unpublished.");
    }
    $records=VisaRequirement::query()->where('passport_nationality_iso3','BGD')->where('travel_purpose','tourism')->whereHas('country',fn($q)=>$q->where('iso2',$code))->get();
    batch45Check($records->count()<=1,"Duplicate {$code} requirements exist.");
    if($records->isNotEmpty()){
        $record=$records->first();
        $pending=$record->verification_status==='pending_review'&&!(bool)$record->is_published;
        $published=$record->verification_status==='verified'&&(bool)$record->is_published;
        batch45Check($pending||$published,"Existing {$code} requirement has an unsafe status.");
    }
}

if($mode==='preflight'){
    echo json_encode(['mode'=>$mode,'counts'=>$counts],JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES).PHP_EOL;
    exit(0);
}

foreach($codes as $code){
    $country=Country::query()->where('iso2',$code)->first();
    batch45Check($country!==null,"{$code} country master row is missing.");
    batch45Check($country->iso3===null&&!(bool)$country->is_published&&$country->flag_emoji===null,"{$code} country row must remain unpublished without alpha-3 or emoji.");
}
if($mode!=='postimport'){
    batch45Fail('Unknown verification mode.');
}

batch45Check($counts['country_count']===252,'Final country master count must be 252.');
batch45Check($counts['requirement_count']===137,'Final BGD tourism requirement count must be 137.');
batch45Check($counts['published_count']===135,'Final published BGD tourism count must be 135.');
batch45Check($counts['pending_count']===2,'Existing pending BGD tourism count must remain 2.');

$expectedSources=[
'AC'=>['https://www.ascension-visas.com/faq','https://www.ascension.gov.ac/travel-and-shipping/immigration'],
'TA'=>['https://www.tristandc.com/visitsorganise.php','https://www.tristandc.com/visitsadmin.php']
];
$summary=[];
foreach($codes as $code){
    $record=batch45Record($code);
    batch45Check($record!==null&&$record->verification_status==='verified'&&(bool)$record->is_published,"{$code} is not verified and published.");
    batch45Check($record->country->iso3===null&&!(bool)$record->country->is_published&&$record->country->flag_emoji===null,"{$code} country master fields are invalid.");
    batch45Check($record->sources->count()>=4,"{$code} official source coverage is insufficient.");
    $urls=$record->sources->pluck('source_url')->all();
    foreach($expectedSources[$code] as $url){batch45Check(in_array($url,$urls,true),"{$code} required official source missing.");}
    foreach($record->sources as $source){
        batch45Check($source->source_type==='official',"{$code} contains a non-official source.");
        batch45Check(substr((string)$source->source_last_checked_at,0,10)==='2026-11-15',"{$code} source checked date mismatch.");
    }
    $photo=$record->photo_specifications??[];
    $checklists=$record->applicant_checklists??[];
    batch45Check(is_array($photo)&&isset($photo['status'])&&is_array($checklists)&&count($checklists)>0,"{$code} photo/checklist data missing.");
    if($code==='AC'){
        batch45Check((bool)$record->visa_required&&!(bool)$record->visa_free_available&&!(bool)$record->visa_on_arrival_available&&(bool)$record->evisa_available,'AC eVisa flags incorrect.');
        batch45Check(str_contains((string)$record->maximum_permitted_stay,'3 months'),'AC stay limit missing.');
        batch45Check(str_contains((string)$record->application_fee,'GBP40')&&str_contains((string)$record->application_fee,'GBP60'),'AC fee data missing.');
        batch45Check($photo['status']==='partially_specified'&&($photo['file_format']??null)==='JPEG','AC photo specification incorrect.');
        $types=collect($checklists)->pluck('applicant_type')->sort()->values()->all();
        batch45Check($types===['employee','retired','self_employed_or_business_owner','student'],'AC applicant checklists incomplete.');
    }else{
        batch45Check(!(bool)$record->visa_required&&!(bool)$record->visa_free_available&&!(bool)$record->visa_on_arrival_available&&!(bool)$record->evisa_available,'TA prior-permission flags incorrect.');
        batch45Check(str_contains(strtolower((string)$record->requirement_type),'not a visa'),'TA requirement type incorrect.');
        batch45Check(str_contains((string)$record->application_fee,'GBP30')&&str_contains((string)$record->application_fee,'GBP15')&&str_contains((string)$record->application_fee,'GBP25'),'TA landing charges incomplete.');
        batch45Check($photo['status']==='not_specified','TA photo status incorrect.');
        batch45Check(collect($checklists)->pluck('applicant_type')->all()===['tourist'],'TA tourist checklist incorrect.');
    }
    $summary[$code]=['name'=>$record->country->name,'slug'=>$record->country->slug,'status'=>$record->verification_status,'published'=>(bool)$record->is_published,'sources'=>$record->sources->count(),'photo_status'=>$photo['status'],'applicant_types'=>collect($checklists)->pluck('applicant_type')->all()];
}

$base=rtrim((string)config('app.url'),'/');
batch45Check($base!=='','APP_URL missing for API readback.');
$requirements=batch45Collection($base.'/api/visa-requirements?passport=BGD&purpose=tourism');
$countries=batch45Collection($base.'/api/countries');
batch45Check($requirements['total']===135&&count($requirements['data'])===135,'Public requirements API total is not 135.');
batch45Check($countries['total']===135&&count($countries['data'])===135,'Public country directory API total is not 135.');

foreach($codes as $code){
    $response=Http::timeout(30)->get($base.'/api/visa-requirements/'.$code,['passport'=>'BGD','purpose'=>'tourism']);
    batch45Check($response->successful(),"Public detail API failed for {$code}.");
    $payload=$response->json();$data=data_get($payload,'data',$payload);
    batch45Check(data_get($data,'country.iso2')===$code,"Public detail country mismatch for {$code}.");
    batch45Check(data_get($data,'verification_status')==='verified'&&(bool)data_get($data,'is_published'),"Public detail publication status mismatch for {$code}.");
    batch45Check(is_array(data_get($data,'sources'))&&count(data_get($data,'sources'))>=4,"Public detail sources incomplete for {$code}.");
    batch45Check(is_array(data_get($data,'photo_specifications'))&&is_array(data_get($data,'applicant_checklists')),"Public detail photo/checklist missing for {$code}.");
}
foreach($requirements['data'] as $item){$code=data_get($item,'country.iso2');}
$requirementCodes=collect($requirements['data'])->pluck('country.iso2')->filter()->all();
$countryCodes=collect($countries['data'])->pluck('iso2')->filter()->all();
foreach($codes as $code){
    batch45Check(in_array($code,$requirementCodes,true),"Public requirements API does not expose {$code}.");
    batch45Check(in_array($code,$countryCodes,true),"Public directory API does not expose {$code}.");
}
echo json_encode(['mode'=>$mode,'counts'=>$counts,'public_requirements_total'=>$requirements['total'],'public_countries_total'=>$countries['total'],'records'=>$summary],JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES).PHP_EOL;
