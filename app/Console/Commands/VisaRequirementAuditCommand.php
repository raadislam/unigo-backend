<?php

namespace App\Console\Commands;

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
                'country:id,name,iso2,iso3,slug',
                'sources:id,visa_requirement_id,source_type,source_name,source_url,source_last_checked_at,is_primary,verification_notes',
            ])
            ->orderBy('id')
            ->get();

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
            'records' => $records->map(function (VisaRequirement $record) use ($fields): array {
                return [
                    ...$record->only($fields),
                    'country' => $record->country?->only(['id', 'name', 'iso2', 'iso3', 'slug']),
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
