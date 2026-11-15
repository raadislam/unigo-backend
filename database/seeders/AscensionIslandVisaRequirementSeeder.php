<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\VisaRequirement;
use App\Models\VisaRequirementHistory;
use App\Models\VisaRequirementSource;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class AscensionIslandVisaRequirementSeeder extends Seeder
{
    public function run(): void
    {
        $path = storage_path('app/imports/verified_seed_batch_46.csv');

        if (! is_file($path)) {
            throw new RuntimeException('The allowlisted batch 46 seed CSV was not found.');
        }

        $handle = fopen($path, 'r');

        if ($handle === false) {
            throw new RuntimeException('Unable to open the allowlisted batch 46 seed CSV.');
        }

        try {
            $headers = fgetcsv($handle);

            if (! is_array($headers)) {
                throw new RuntimeException('The allowlisted batch 46 seed CSV is empty.');
            }

            $headers[0] = preg_replace('/^\xEF\xBB\xBF/', '', $headers[0]);
            $required = [
                'passport_iso3', 'passport_nationality', 'destination_iso2', 'destination_name',
                'travel_purpose', 'visa_requirement_type', 'visa_required', 'visa_free_eligibility',
                'visa_on_arrival_available', 'evisa_available', 'maximum_permitted_stay', 'entry_count',
                'passport_validity_rule', 'blank_passport_pages_rule', 'return_ticket_rule',
                'proof_of_funds_rule', 'accommodation_rule', 'travel_insurance_rule',
                'yellow_fever_or_health_rule', 'transit_rule', 'application_channel', 'application_fee',
                'processing_time', 'required_documents', 'official_source_url',
                'official_immigration_source_name', 'official_source_last_checked', 'verification_status',
                'review_notes',
            ];
            $missing = array_diff($required, $headers);

            if ($missing !== []) {
                throw new RuntimeException('Batch 46 seed CSV is missing columns: ' . implode(', ', $missing));
            }

            $rows = [];

            while (($values = fgetcsv($handle)) !== false) {
                if (count($values) !== count($headers)) {
                    throw new RuntimeException('Batch 46 seed CSV contains a malformed row.');
                }

                $rows[] = array_combine($headers, $values);
            }
        } finally {
            fclose($handle);
        }

        if (count($rows) !== 1) {
            throw new RuntimeException('The batch 46 seeder accepts exactly one allowlisted row.');
        }

        $row = $rows[0];

        if (
            ($row['passport_iso3'] ?? null) !== 'BGD'
            || ($row['destination_iso2'] ?? null) !== 'AC'
            || ($row['destination_name'] ?? null) !== 'Ascension Island'
            || ($row['travel_purpose'] ?? null) !== 'tourism'
            || ($row['verification_status'] ?? null) !== 'pending_review'
        ) {
            throw new RuntimeException('Batch 46 seeder rejected a row outside the AC/BGD/ordinary-tourism allowlist.');
        }

        $country = Country::query()->where('iso2', 'AC')->first();

        if (! $country || $country->slug !== 'ascension-island' || $country->iso3 !== null) {
            throw new RuntimeException('The Ascension Island country master row is missing or does not match the approved identity.');
        }

        DB::transaction(function () use ($country, $row): void {
            $record = VisaRequirement::query()
                ->where('country_id', $country->id)
                ->where('passport_nationality_iso3', 'BGD')
                ->where('travel_purpose', 'tourism')
                ->first();

            $created = false;

            if ($record) {
                if ($record->verification_status !== 'pending_review' || (bool) $record->is_published) {
                    throw new RuntimeException('An existing AC/BGD tourism record is not pending and unpublished; refusing to overwrite it.');
                }
            } else {
                $truthy = static fn ($value): ?bool => $value === '' || $value === null
                    ? null
                    : filter_var($value, FILTER_VALIDATE_BOOLEAN);
                $documents = array_values(array_filter(array_map('trim', explode('|', $row['required_documents'] ?? ''))));

                $record = VisaRequirement::create([
                    'country_id' => $country->id,
                    'passport_nationality_iso3' => 'BGD',
                    'passport_nationality_name' => 'Bangladesh',
                    'travel_purpose' => 'tourism',
                    'requirement_type' => $row['visa_requirement_type'],
                    'visa_required' => $truthy($row['visa_required']),
                    'visa_free_available' => $truthy($row['visa_free_eligibility']),
                    'visa_on_arrival_available' => $truthy($row['visa_on_arrival_available']),
                    'evisa_available' => $truthy($row['evisa_available']),
                    'maximum_permitted_stay' => $row['maximum_permitted_stay'],
                    'entry_count' => $row['entry_count'],
                    'passport_validity_rule' => $row['passport_validity_rule'],
                    'blank_passport_pages_rule' => $row['blank_passport_pages_rule'],
                    'return_ticket_rule' => $row['return_ticket_rule'],
                    'proof_of_funds_rule' => $row['proof_of_funds_rule'],
                    'accommodation_rule' => $row['accommodation_rule'],
                    'travel_insurance_rule' => $row['travel_insurance_rule'],
                    'health_requirements' => $row['yellow_fever_or_health_rule'],
                    'transit_requirements' => $row['transit_rule'],
                    'application_channel' => $row['application_channel'],
                    'application_fee' => $row['application_fee'],
                    'processing_time' => $row['processing_time'],
                    'required_documents' => $documents,
                    'verification_status' => 'pending_review',
                    'is_published' => false,
                    'official_source_checked_at' => Carbon::parse($row['official_source_last_checked'])->startOfDay(),
                    'last_synced_at' => now(),
                ]);

                $created = true;
            }

            VisaRequirementSource::updateOrCreate(
                [
                    'visa_requirement_id' => $record->id,
                    'source_url' => trim($row['official_source_url']),
                ],
                [
                    'source_type' => 'official',
                    'source_name' => trim($row['official_immigration_source_name']),
                    'source_url' => trim($row['official_source_url']),
                    'source_last_checked_at' => Carbon::parse($row['official_source_last_checked'])->startOfDay(),
                    'is_primary' => true,
                    'verification_notes' => trim($row['review_notes']),
                ]
            );

            if ($created) {
                VisaRequirementHistory::create([
                    'visa_requirement_id' => $record->id,
                    'snapshot' => $record->fresh()->toArray(),
                    'change_source' => 'csv_import',
                    'changed_by' => 'UniGo batch 46',
                    'change_summary' => 'Created the allowlisted Ascension Island BGD tourism record as pending and unpublished for the full-audit import.',
                    'recorded_at' => now(),
                ]);
            }
        });

        $this->command?->info('AC_BGD_TOURISM_SEED_READY: one allowlisted record retained pending and unpublished.');
    }
}
