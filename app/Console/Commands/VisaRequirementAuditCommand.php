<?php

namespace App\Console\Commands;

use App\Models\Country;
use App\Models\VisaRequirement;
use Illuminate\Console\Command;

class VisaRequirementAuditCommand extends Command
{
    protected $signature = 'visa:requirements-audit';

    protected $description = 'Export a read-only snapshot of UniGo visa requirement records for audit';

    public function handle(): int
    {
        $records = VisaRequirement::query()
            ->with([
                'country:id,name,iso2,iso3,slug,is_published,sort_order',
                'sources:id,visa_requirement_id,source_type,source_name,source_url,source_last_checked_at,is_primary,verification_notes',
            ])
            ->orderBy('id')
            ->get();

        $publishedRequirements = VisaRequirement::query()
            ->select('country_id')
            ->where('passport_nationality_iso3', 'BGD')
            ->where('travel_purpose', 'tourism')
            ->where('verification_status', 'verified')
            ->where('is_published', true)
            ->where(fn ($query) => $query->whereNull('effective_from')->orWhereDate('effective_from', '<=', today()))
            ->where(fn ($query) => $query->whereNull('effective_until')->orWhereDate('effective_until', '>=', today()));

        $publicCountries = Country::query()
            ->where(fn ($query) => $query->where('is_published', true)->orWhereIn('id', $publishedRequirements))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name', 'iso2', 'iso3', 'slug', 'is_published', 'sort_order']);

        $allCountries = Country::query()
            ->orderBy('name')
            ->get(['id', 'name', 'iso2', 'iso3', 'slug', 'is_published', 'sort_order']);

        $fields = [
            'id',
            'passport_nationality_iso3',
            'passport_nationality_name',
            'travel_purpose',
            'requirement_type',
            'visa_required',
            'visa_free_available',
            'visa_on_arrival_available',
            'evisa_available',
            'maximum_permitted_stay',
            'entry_count',
            'passport_validity_rule',
            'blank_passport_pages_rule',
            'return_ticket_rule',
            'proof_of_funds_rule',
            'accommodation_rule',
            'travel_insurance_rule',
            'health_requirements',
            'transit_requirements',
            'application_channel',
            'application_fee',
            'processing_time',
            'required_documents',
            'photo_specifications',
            'applicant_checklists',
            'verification_status',
            'is_published',
            'effective_from',
            'effective_until',
            'official_source_checked_at',
            'provider_updated_at',
            'last_synced_at',
            'created_at',
            'updated_at',
        ];

        $payload = [
            'generated_at' => now()->toIso8601String(),
            'total' => $records->count(),
            'verification_status_counts' => $records->countBy('verification_status'),
            'published_count' => $records->where('is_published', true)->count(),
            'public_country_directory_count' => $publicCountries->count(),
            'public_country_directory' => $publicCountries->map(fn (Country $country) => $country->only(['id', 'name', 'iso2', 'iso3', 'slug', 'is_published', 'sort_order']))->values()->all(),
            'country_table_count' => $allCountries->count(),
            'country_table_published_count' => $allCountries->where('is_published', true)->count(),
            'country_table_unpublished_count' => $allCountries->where('is_published', false)->count(),
            'country_table' => $allCountries->map(fn (Country $country) => $country->only(['id', 'name', 'iso2', 'iso3', 'slug', 'is_published', 'sort_order']))->values()->all(),
            'records' => $records->map(function (VisaRequirement $record) use ($fields): array {
                return [
                    ...$record->only($fields),
                    'country' => $record->country?->only(['id', 'name', 'iso2', 'iso3', 'slug', 'is_published', 'sort_order']),
                    'sources' => $record->sources->map(fn ($source) => $source->only([
                        'id',
                        'source_type',
                        'source_name',
                        'source_url',
                        'source_last_checked_at',
                        'is_primary',
                        'verification_notes',
                    ]))->values()->all(),
                ];
            })->values()->all(),
        ];

        $this->line(json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

        return self::SUCCESS;
    }
}
