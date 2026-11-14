<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\VisaRequirement;
use App\Models\VisaRequirementHistory;
use App\Models\VisaRequirementSource;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ImportMajorVisaRequirementsSeeder extends Seeder
{
    public function run(): void
    {
        $path = storage_path('app/imports/unigo_visa_requirements_research_template.csv');

        if (! is_file($path)) {
            throw new RuntimeException('Visa requirements CSV was not found at ' . $path);
        }

        $handle = fopen($path, 'r');
        if ($handle === false) {
            throw new RuntimeException('Unable to open the visa requirements CSV.');
        }

        $headers = fgetcsv($handle);
        if (! is_array($headers)) {
            fclose($handle);
            throw new RuntimeException('The visa requirements CSV is empty.');
        }

        $headers[0] = preg_replace('/^\xEF\xBB\xBF/', '', $headers[0]);
        $required = ['passport_iso3', 'destination_iso2', 'travel_purpose', 'visa_requirement_type', 'visa_required', 'official_source_url', 'verification_status'];
        $missing = array_diff($required, $headers);
        if ($missing !== []) {
            fclose($handle);
            throw new RuntimeException('Missing CSV columns: ' . implode(', ', $missing));
        }

        $count = 0;
        $skipped = 0;
        $dryRun = filter_var(env('UNIGO_VISA_SEED_DRY_RUN', false), FILTER_VALIDATE_BOOLEAN);

        try {
            DB::transaction(function () use ($handle, $headers, &$count, &$skipped, $dryRun): void {
                while (($values = fgetcsv($handle)) !== false) {
                    if (count($values) !== count($headers)) {
                        $skipped++;
                        continue;
                    }

                    $row = array_combine($headers, $values);
                    if (($row['passport_iso3'] ?? '') !== 'BGD' || empty($row['destination_iso2']) || empty($row['official_source_url'])) {
                        continue;
                    }

                    $country = Country::query()->where('iso2', strtoupper(trim($row['destination_iso2'])))->first();
                    if (! $country) {
                        $skipped++;
                        continue;
                    }

                    $existing = VisaRequirement::query()
                        ->where('country_id', $country->id)
                        ->where('passport_nationality_iso3', 'BGD')
                        ->where('travel_purpose', $row['travel_purpose'] ?: 'tourism')
                        ->exists();

                    if ($existing) {
                        $skipped++;
                        continue;
                    }

                    if ($dryRun) {
                        $count++;
                        continue;
                    }

                    $truthy = static fn ($value): ?bool => $value === '' || $value === null ? null : filter_var($value, FILTER_VALIDATE_BOOLEAN);
                    $documents = array_values(array_filter(array_map('trim', explode('|', $row['required_documents'] ?? ''))));

                    $record = VisaRequirement::updateOrCreate(
                        [
                            'country_id' => $country->id,
                            'passport_nationality_iso3' => 'BGD',
                            'travel_purpose' => $row['travel_purpose'] ?: 'tourism',
                        ],
                        [
                            'passport_nationality_name' => $row['passport_nationality'] ?: 'Bangladesh',
                            'requirement_type' => $row['visa_requirement_type'] ?: null,
                            'visa_required' => $truthy($row['visa_required'] ?? ''),
                            'visa_free_available' => $truthy($row['visa_free_eligibility'] ?? ''),
                            'visa_on_arrival_available' => $truthy($row['visa_on_arrival_available'] ?? ''),
                            'evisa_available' => $truthy($row['evisa_available'] ?? ''),
                            'maximum_permitted_stay' => $row['maximum_permitted_stay'] ?: null,
                            'entry_count' => $row['entry_count'] ?: null,
                            'passport_validity_rule' => $row['passport_validity_rule'] ?: null,
                            'blank_passport_pages_rule' => $row['blank_passport_pages_rule'] ?: null,
                            'return_ticket_rule' => $row['return_ticket_rule'] ?: null,
                            'proof_of_funds_rule' => $row['proof_of_funds_rule'] ?: null,
                            'accommodation_rule' => $row['accommodation_rule'] ?: null,
                            'travel_insurance_rule' => $row['travel_insurance_rule'] ?: null,
                            'health_requirements' => $row['yellow_fever_or_health_rule'] ?: null,
                            'transit_requirements' => $row['transit_rule'] ?: null,
                            'application_channel' => $row['application_channel'] ?: null,
                            'application_fee' => $row['application_fee'] ?: null,
                            'processing_time' => $row['processing_time'] ?: null,
                            'required_documents' => $documents,
                            'verification_status' => 'pending_review',
                            'is_published' => false,
                            'official_source_checked_at' => ! empty($row['official_source_last_checked']) ? $row['official_source_last_checked'] . ' 00:00:00' : now(),
                            'last_synced_at' => now(),
                        ]
                    );

                    VisaRequirementSource::updateOrCreate(
                        ['visa_requirement_id' => $record->id, 'source_url' => $row['official_source_url']],
                        [
                            'source_type' => 'official',
                            'source_name' => $row['official_immigration_source_name'] ?: 'Official government source',
                            'source_url' => $row['official_source_url'],
                            'source_last_checked_at' => ! empty($row['official_source_last_checked']) ? $row['official_source_last_checked'] . ' 00:00:00' : now(),
                            'is_primary' => true,
                            'verification_notes' => $row['review_notes'] ?: null,
                        ]
                    );

                    if (! empty($row['cross_check_source_url'])) {
                        VisaRequirementSource::updateOrCreate(
                            ['visa_requirement_id' => $record->id, 'source_url' => $row['cross_check_source_url']],
                            [
                                'source_type' => 'secondary',
                                'source_name' => 'Additional official cross-check',
                                'source_url' => $row['cross_check_source_url'],
                                'source_last_checked_at' => ! empty($row['official_source_last_checked']) ? $row['official_source_last_checked'] . ' 00:00:00' : now(),
                                'is_primary' => false,
                                'verification_notes' => 'Additional official reference; review alongside the primary source.',
                            ]
                        );
                    }

                    VisaRequirementHistory::create([
                        'visa_requirement_id' => $record->id,
                        'snapshot' => $record->fresh()->toArray(),
                        'change_source' => 'csv_import',
                        'changed_by' => 'UniGo visa research import',
                        'change_summary' => 'Imported official-source research; retained as unpublished pending admin verification.',
                        'recorded_at' => now(),
                    ]);

                    $count++;
                }
            });
        } finally {
            fclose($handle);
        }

        if ($dryRun) {
            $this->command?->info($count . ' visa requirement records would be imported; ' . $skipped . ' rows skipped. Dry-run wrote no records.');
            return;
        }

        $this->command?->info($count . ' visa requirement records imported or updated; ' . $skipped . ' rows skipped. All records remain pending review and unpublished.');
    }
}
