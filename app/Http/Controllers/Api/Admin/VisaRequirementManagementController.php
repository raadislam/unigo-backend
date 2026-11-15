<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\VisaRequirement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class VisaRequirementManagementController extends Controller
{
    private const FIELDS = [
        'passport_nationality_iso3', 'passport_nationality_name', 'travel_purpose',
        'requirement_type', 'visa_required', 'visa_free_available',
        'visa_on_arrival_available', 'evisa_available', 'maximum_permitted_stay',
        'entry_count', 'passport_validity_rule', 'blank_passport_pages_rule',
        'return_ticket_rule', 'proof_of_funds_rule', 'accommodation_rule',
        'travel_insurance_rule', 'health_requirements', 'transit_requirements',
        'application_channel', 'application_fee', 'processing_time',
        'required_documents', 'photo_specifications', 'applicant_checklists', 'effective_from', 'effective_until',
        'official_source_checked_at', 'provider_updated_at', 'last_synced_at',
    ];

    public function countries(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => Country::query()->select('id', 'name', 'iso2', 'iso3', 'flag_emoji')->orderBy('name')->get(),
        ]);
    }

    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:120'],
            'passport' => ['nullable', 'string', 'size:3', 'alpha'],
            'purpose' => ['nullable', 'string', 'in:tourism,business,study,transit,work'],
            'status' => ['nullable', 'string', 'in:not_verified,pending_review,verified'],
            'published' => ['nullable', 'boolean'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $records = VisaRequirement::query()
            ->with(['country:id,name,iso2,iso3,flag_emoji', 'sources', 'histories'])
            ->when($validated['search'] ?? null, fn ($query, $search) => $query->whereHas('country', fn ($country) => $country->where('name', 'like', '%' . $search . '%')->orWhere('iso2', strtoupper($search))))
            ->when($validated['passport'] ?? null, fn ($query, $passport) => $query->where('passport_nationality_iso3', strtoupper($passport)))
            ->when($validated['purpose'] ?? null, fn ($query, $purpose) => $query->where('travel_purpose', $purpose))
            ->when($validated['status'] ?? null, fn ($query, $status) => $query->where('verification_status', $status))
            ->when(array_key_exists('published', $validated), fn ($query) => $query->where('is_published', $validated['published']))
            ->latest('updated_at')
            ->paginate($validated['per_page'] ?? 25);

        return response()->json(['success' => true, 'data' => $records]);
    }

    public function show(int $id): JsonResponse
    {
        $record = VisaRequirement::with(['country', 'sources', 'histories' => fn ($query) => $query->latest('recorded_at')])->findOrFail($id);

        return response()->json(['success' => true, 'data' => $record]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate($this->rules(true));

        $record = DB::transaction(function () use ($validated, $request) {
            $record = VisaRequirement::create(Arr::only($validated, self::FIELDS) + [
                'country_id' => $validated['country_id'],
                'verification_status' => 'not_verified',
                'is_published' => false,
            ]);
            $this->recordHistory($record, 'Created draft', $request);

            return $record->load(['country', 'sources']);
        });

        return response()->json(['success' => true, 'data' => $record], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $record = VisaRequirement::findOrFail($id);
        $validated = $request->validate($this->rules(false));

        DB::transaction(function () use ($record, $validated, $request) {
            $record->fill(Arr::only($validated, self::FIELDS));
            $record->is_published = false;
            if ($record->verification_status === 'verified') {
                $record->verification_status = 'pending_review';
            }
            $record->save();
            $this->recordHistory($record, 'Requirement updated; publication disabled pending re-verification', $request);
        });

        return response()->json(['success' => true, 'data' => $record->fresh(['country', 'sources'])]);
    }

    public function addSource(Request $request, int $id): JsonResponse
    {
        $record = VisaRequirement::findOrFail($id);
        $validated = $request->validate([
            'source_type' => ['required', Rule::in(['official', 'provider', 'secondary'])],
            'source_name' => ['required', 'string', 'max:180'],
            'source_url' => ['required', 'url', 'max:2000'],
            'provider_name' => ['nullable', 'string', 'max:100'],
            'provider_record_id' => ['nullable', 'string', 'max:180'],
            'source_last_checked_at' => ['nullable', 'date'],
            'is_primary' => ['sometimes', 'boolean'],
            'verification_notes' => ['nullable', 'string', 'max:5000'],
        ]);

        $source = DB::transaction(function () use ($record, $validated, $request) {
            if ($validated['is_primary'] ?? false) {
                $record->sources()->update(['is_primary' => false]);
            }
            $source = $record->sources()->create($validated);
            $record->update(['is_published' => false]);
            $this->recordHistory($record, 'Source added; publication disabled pending review', $request);

            return $source;
        });

        return response()->json(['success' => true, 'data' => $source], 201);
    }

    public function deleteSource(Request $request, int $id, int $sourceId): JsonResponse
    {
        $record = VisaRequirement::findOrFail($id);
        $source = $record->sources()->findOrFail($sourceId);

        DB::transaction(function () use ($record, $source, $request) {
            $source->delete();
            $record->update(['is_published' => false]);
            $this->recordHistory($record, 'Source removed; publication disabled', $request);
        });

        return response()->json(['success' => true]);
    }

    public function verify(Request $request, int $id): JsonResponse
    {
        $record = VisaRequirement::with('sources')->findOrFail($id);
        $validated = $request->validate([
            'verification_status' => ['required', Rule::in(['pending_review', 'verified', 'not_verified'])],
            'change_summary' => ['required', 'string', 'max:2000'],
            'official_source_checked_at' => ['nullable', 'date'],
        ]);

        if ($validated['verification_status'] === 'verified' && !$record->sources->contains(fn ($source) => $source->source_type === 'official' && $source->is_primary)) {
            return response()->json(['success' => false, 'message' => 'A primary official source is required before verification.'], 422);
        }

        DB::transaction(function () use ($record, $validated, $request) {
            $record->update([
                'verification_status' => $validated['verification_status'],
                'official_source_checked_at' => $validated['official_source_checked_at'] ?? now(),
                'is_published' => false,
            ]);
            $this->recordHistory($record, $validated['change_summary'], $request);
        });

        return response()->json(['success' => true, 'data' => $record->fresh(['country', 'sources'])]);
    }

    public function publish(Request $request, int $id): JsonResponse
    {
        $record = VisaRequirement::with('sources')->findOrFail($id);
        $hasOfficialPrimarySource = $record->sources->contains(fn ($source) => $source->source_type === 'official' && $source->is_primary);

        if ($record->verification_status !== 'verified' || !$hasOfficialPrimarySource) {
            return response()->json(['success' => false, 'message' => 'Only verified requirements with a primary official source can be published.'], 422);
        }

        DB::transaction(function () use ($record, $request) {
            $record->update(['is_published' => true]);
            $this->recordHistory($record, 'Requirement published', $request);
        });

        return response()->json(['success' => true, 'data' => $record->fresh(['country', 'sources'])]);
    }

    public function unpublish(Request $request, int $id): JsonResponse
    {
        $record = VisaRequirement::findOrFail($id);

        DB::transaction(function () use ($record, $request) {
            $record->update(['is_published' => false]);
            $this->recordHistory($record, 'Requirement unpublished', $request);
        });

        return response()->json(['success' => true, 'data' => $record->fresh()]);
    }

    private function rules(bool $creating): array
    {
        return [
            'country_id' => [$creating ? 'required' : 'sometimes', 'integer', 'exists:countries,id'],
            'passport_nationality_iso3' => ['sometimes', 'string', 'size:3', 'alpha'],
            'passport_nationality_name' => ['sometimes', 'string', 'max:100'],
            'travel_purpose' => ['sometimes', Rule::in(['tourism', 'business', 'study', 'transit', 'work'])],
            'requirement_type' => ['nullable', 'string', 'max:255'],
            'visa_required' => ['nullable', 'boolean'],
            'visa_free_available' => ['nullable', 'boolean'],
            'visa_on_arrival_available' => ['nullable', 'boolean'],
            'evisa_available' => ['nullable', 'boolean'],
            'maximum_permitted_stay' => ['nullable', 'string', 'max:60000'],
            'entry_count' => ['nullable', 'string', 'max:40'],
            'passport_validity_rule' => ['nullable', 'string', 'max:10000'],
            'blank_passport_pages_rule' => ['nullable', 'string', 'max:10000'],
            'return_ticket_rule' => ['nullable', 'string', 'max:10000'],
            'proof_of_funds_rule' => ['nullable', 'string', 'max:10000'],
            'accommodation_rule' => ['nullable', 'string', 'max:10000'],
            'travel_insurance_rule' => ['nullable', 'string', 'max:10000'],
            'health_requirements' => ['nullable', 'string', 'max:10000'],
            'transit_requirements' => ['nullable', 'string', 'max:10000'],
            'application_channel' => ['nullable', 'string', 'max:60000'],
            'application_fee' => ['nullable', 'string', 'max:120'],
            'processing_time' => ['nullable', 'string', 'max:120'],
            'required_documents' => ['nullable', 'array'],
            'required_documents.*' => ['string', 'max:500'],
            'photo_specifications' => ['nullable', 'array'],
            'photo_specifications.*' => ['nullable'],
            'applicant_checklists' => ['nullable', 'array'],
            'applicant_checklists.*.applicant_type' => ['required', 'string', 'max:80'],
            'applicant_checklists.*.notes' => ['nullable', 'string', 'max:1000'],
            'applicant_checklists.*.source_name' => ['nullable', 'string', 'max:180'],
            'applicant_checklists.*.source_url' => ['nullable', 'url', 'max:2000'],
            'applicant_checklists.*.checked_at' => ['nullable', 'date_format:Y-m-d'],
            'applicant_checklists.*.documents' => ['required', 'array'],
            'applicant_checklists.*.documents.*.name' => ['required', 'string', 'max:200'],
            'applicant_checklists.*.documents.*.status' => ['required', 'in:required,conditional,if_applicable'],
            'applicant_checklists.*.documents.*.notes' => ['nullable', 'string', 'max:1000'],
            'effective_from' => ['nullable', 'date'],
            'effective_until' => ['nullable', 'date', 'after_or_equal:effective_from'],
            'official_source_checked_at' => ['nullable', 'date'],
            'provider_updated_at' => ['nullable', 'date'],
            'last_synced_at' => ['nullable', 'date'],
        ];
    }

    private function recordHistory(VisaRequirement $record, string $summary, Request $request): void
    {
        $record->histories()->create([
            'snapshot' => $record->fresh()->toArray(),
            'change_source' => 'admin',
            'changed_by' => $request->header('X-UniGo-Admin-User', 'UniGo Admin'),
            'change_summary' => $summary,
            'recorded_at' => now(),
        ]);
    }
}
