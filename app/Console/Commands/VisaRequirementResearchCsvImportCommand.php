<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

class VisaRequirementResearchCsvImportCommand extends Command
{
    protected $signature = 'visa:requirements-import-research {--file=database/data/visa_fee_processing_batch_01.csv} {--base-url=} {--dry-run}';

    protected $description = 'Import official visa fee and processing-time research through the signed UniGo admin API';

    public function handle(): int
    {
        $file = (string) $this->option('file');
        $file = str_starts_with($file, DIRECTORY_SEPARATOR) ? $file : base_path($file);
        $secret = (string) config('services.unigo_admin.secret');
        $base = rtrim((string) ($this->option('base-url') ?: config('app.url')), '/');
        $dry = (bool) $this->option('dry-run');

        if (! is_file($file) || $secret === '' || $base === '') {
            $this->error('The research CSV, signed API secret, or API base URL is unavailable.');
            return self::FAILURE;
        }

        $hash = hash_file('sha256', $file);
        $marker = storage_path('logs/visa-imports-' . $hash . '.json');
        if (! $dry && is_file($marker)) {
            $this->info('This exact CSV batch has already been imported and reconciled.');
            return self::SUCCESS;
        }

        try {
            $rows = $this->rows($file);
            $this->validateRows($rows);
            $index = $this->api('GET', $base . '/api/admin/visa-requirements?' . http_build_query(['passport' => 'BGD', 'purpose' => 'tourism', 'per_page' => 100]), [], $secret);
            $records = data_get($index, 'data.data', []);
            if (! is_array($records)) {
                throw new RuntimeException('The signed admin API did not return a record list.');
            }

            $byIso2 = collect($records)->keyBy(fn (array $record) => strtoupper((string) data_get($record, 'country.iso2', '')));
            $summary = ['mode' => $dry ? 'dry_run' : 'import', 'csv_sha256' => $hash, 'rows' => count($rows), 'updated' => 0, 'sources_added' => 0, 'reverified' => 0, 'republished' => 0, 'read_back' => 0, 'failures' => []];

            foreach ($rows as $row) {
                $iso2 = strtoupper(trim($row['destination_iso2']));
                $record = $byIso2->get($iso2);
                if (! is_array($record)) {
                    $summary['failures'][] = ['destination_iso2' => $iso2, 'error' => 'No matching BGD tourism record.'];
                    continue;
                }

                $id = (int) $record['id'];
                $checked = trim($row['official_source_last_checked']);
                $fee = trim($row['application_fee']);
                $time = trim($row['processing_time']);

                try {
                    $changes = [];
                    if (($record['application_fee'] ?? null) !== $fee) $changes['application_fee'] = $fee;
                    if (($record['processing_time'] ?? null) !== $time) $changes['processing_time'] = $time;
                    if (empty($record['official_source_checked_at']) || substr((string) $record['official_source_checked_at'], 0, 10) < $checked) $changes['official_source_checked_at'] = $checked;
                    $fullAudit = filter_var($row['full_audit'] ?? 'false', FILTER_VALIDATE_BOOLEAN);
                    if ($fullAudit) $changes = array_merge($changes, $this->fullAuditPayload($row));

                    if ($changes !== []) {
                        if (! $dry) $this->api('PUT', $base . '/api/admin/visa-requirements/' . $id, $changes, $secret);
                        $summary['updated']++;
                    }

                    $detail = $this->api('GET', $base . '/api/admin/visa-requirements/' . $id, [], $secret);
                    $current = data_get($detail, 'data', []);
                    if (! is_array($current)) throw new RuntimeException('Could not read the current record.');

                    $sources = $current['sources'] ?? [];
                    $primaryUrl = trim($row['official_source_url']);
                    $primary = [
                        'source_type' => 'official',
                        'source_name' => trim($row['official_immigration_source_name']),
                        'source_url' => $primaryUrl,
                        'source_last_checked_at' => Carbon::parse($checked)->startOfDay()->toDateTimeString(),
                        'is_primary' => true,
                        'verification_notes' => trim($row['review_notes']),
                    ];

                    if ($this->addSource($base, $id, $sources, $primary, $secret, $dry)) {
                        $summary['sources_added']++;
                        $sources[] = ['source_url' => $primaryUrl, 'source_last_checked_at' => $checked, 'is_primary' => true];
                    }

                    $crossUrl = trim($row['cross_check_source_url']);
                    $crossType = str_contains(strtolower((string) parse_url($crossUrl, PHP_URL_HOST)), 'vfsglobal.com') ? 'provider' : 'official';
                    $cross = [
                        'source_type' => $crossType,
                        'source_name' => trim($row['cross_check_source_name']),
                        'source_url' => $crossUrl,
                        'source_last_checked_at' => Carbon::parse($checked)->startOfDay()->toDateTimeString(),
                        'is_primary' => false,
                        'verification_notes' => 'Additional official or application-provider reference for fee or processing-time information. Checked ' . $checked . '.',
                    ];

                    if ($this->addSource($base, $id, $sources, $cross, $secret, $dry)) {
                        $summary['sources_added']++;
                        $sources[] = ['source_url' => $crossUrl, 'source_last_checked_at' => $checked, 'is_primary' => false];
                    }

                    $extraSources = json_decode((string) ($row['additional_sources'] ?? '[]'), true, 512, JSON_THROW_ON_ERROR);
                    if (! is_array($extraSources)) throw new RuntimeException('Additional sources must be a JSON array.');
                    foreach ($extraSources as $extra) {
                        $extra['source_last_checked_at'] = Carbon::parse($checked)->startOfDay()->toDateTimeString();
                        $extra['is_primary'] = false;
                        if ($this->addSource($base, $id, $sources, $extra, $secret, $dry)) {
                            $summary['sources_added']++;
                            $sources[] = ['source_url' => $extra['source_url'], 'source_last_checked_at' => $checked, 'is_primary' => false];
                        }
                    }

                    $shouldPublish = trim($row['verification_status']) === 'verified' && trim($row['publish_status']) === 'published';
                    if ($shouldPublish && ! $dry) {
                        $this->api('POST', $base . '/api/admin/visa-requirements/' . $id . '/verification', [
                            'verification_status' => 'verified',
                            'change_summary' => 'Rechecked official application fee and processing-time information on ' . $checked . '; previously verified requirement fields were retained.',
                            'official_source_checked_at' => $checked,
                        ], $secret);
                        $summary['reverified']++;
                        $this->api('POST', $base . '/api/admin/visa-requirements/' . $id . '/publish', [], $secret);
                        $summary['republished']++;
                    }

                    if (! $dry) {
                        $saved = data_get($this->api('GET', $base . '/api/admin/visa-requirements/' . $id, [], $secret), 'data', []);
                        if (! is_array($saved) || ($saved['application_fee'] ?? null) !== $fee || ($saved['processing_time'] ?? null) !== $time) {
                            throw new RuntimeException('Saved fee or processing time does not match the CSV.');
                        }

                        if ($fullAudit) {
                            foreach ($this->fullAuditPayload($row) as $field => $expected) {
                                $actual = $saved[$field] ?? null;
                                if ($field === 'required_documents') {
                                    $actualList = is_array($actual) ? $actual : [];
                                    sort($actualList);
                                    $expectedList = $expected;
                                    sort($expectedList);
                                    if ($actualList !== $expectedList) throw new RuntimeException('Readback mismatch for required_documents.');
                                } elseif (in_array($field, ['visa_required', 'visa_free_available', 'visa_on_arrival_available', 'evisa_available'], true)) {
                                    if ((bool) $actual !== (bool) $expected) throw new RuntimeException('Readback mismatch for ' . $field . '.');
                                } elseif ((string) $actual !== (string) $expected) {
                                    throw new RuntimeException('Readback mismatch for ' . $field . '.');
                                }
                            }
                        }

                        $savedSources = collect($saved['sources'] ?? []);
                        if (! $savedSources->contains(fn ($source) => ($source['source_url'] ?? null) === $primaryUrl && (bool) ($source['is_primary'] ?? false))) {
                            throw new RuntimeException('Primary official source was not present after readback.');
                        }
                        if (! $savedSources->contains(fn ($source) => ($source['source_url'] ?? null) === $crossUrl)) {
                            throw new RuntimeException('Cross-check source was not present after readback.');
                        }
                        foreach ($extraSources as $extra) {
                            if (! $savedSources->contains(fn ($source) => ($source['source_url'] ?? null) === ($extra['source_url'] ?? null))) {
                                throw new RuntimeException('An additional source was not present after readback.');
                            }
                        }

                        if ($shouldPublish && (($saved['verification_status'] ?? null) !== 'verified' || ! (bool) ($saved['is_published'] ?? false))) {
                            throw new RuntimeException('The previously published record did not return to verified and published status.');
                        }
                        if (! $shouldPublish && (($saved['verification_status'] ?? null) !== 'pending_review' || (bool) ($saved['is_published'] ?? false))) {
                            throw new RuntimeException('A pending-review record was unexpectedly verified or published.');
                        }
                        $summary['read_back']++;
                    }
                } catch (Throwable $exception) {
                    $summary['failures'][] = ['destination_iso2' => $iso2, 'record_id' => $id, 'error' => $exception->getMessage()];
                }
            }

            $this->line(json_encode($summary, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
            if ($summary['failures'] !== []) return self::FAILURE;

            if (! $dry) {
                file_put_contents($marker, json_encode(['csv_sha256' => $hash, 'imported_at' => now()->toIso8601String(), 'summary' => $summary], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
            }

            return self::SUCCESS;
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());
            return self::FAILURE;
        }
    }

    private function rows(string $file): array
    {
        $handle = fopen($file, 'r');
        if ($handle === false) throw new RuntimeException('Unable to open the research CSV.');

        try {
            $headers = fgetcsv($handle);
            if (! is_array($headers)) throw new RuntimeException('The research CSV is empty.');
            $headers[0] = preg_replace('/^\xEF\xBB\xBF/', '', (string) $headers[0]);
            $required = ['passport_iso3', 'destination_iso2', 'travel_purpose', 'application_fee', 'processing_time', 'official_source_url', 'official_immigration_source_name', 'official_source_last_checked', 'cross_check_source_url', 'cross_check_source_name', 'verification_status', 'publish_status', 'review_notes'];
            $missing = array_diff($required, $headers);
            if ($missing !== []) throw new RuntimeException('Missing CSV columns: ' . implode(', ', $missing));

            $rows = [];
            while (($values = fgetcsv($handle)) !== false) {
                if (count($values) !== count($headers)) throw new RuntimeException('A CSV row has a different number of fields from the header.');
                $row = array_combine($headers, $values);
                if (! is_array($row)) throw new RuntimeException('A CSV row could not be parsed.');
                $rows[] = $row;
            }
            return $rows;
        } finally {
            fclose($handle);
        }
    }

    private function validateRows(array $rows): void
    {
        if ($rows === []) throw new RuntimeException('The research CSV contains no data rows.');
        $seen = [];

        foreach ($rows as $index => $row) {
            $line = $index + 2;
            $iso2 = strtoupper(trim($row['destination_iso2']));
            if (strtoupper(trim($row['passport_iso3'])) !== 'BGD' || trim($row['travel_purpose']) !== 'tourism') throw new RuntimeException('Only BGD tourism rows are allowed (line ' . $line . ').');
            if (! preg_match('/^[A-Z]{2}$/', $iso2)) throw new RuntimeException('Invalid destination ISO2 (line ' . $line . ').');
            if (trim($row['application_fee']) === '' || mb_strlen(trim($row['application_fee'])) > 120) throw new RuntimeException('Application fee missing or over 120 characters (line ' . $line . ').');
            if (trim($row['processing_time']) === '' || mb_strlen(trim($row['processing_time'])) > 120) throw new RuntimeException('Processing time missing or over 120 characters (line ' . $line . ').');
            if (! filter_var(trim($row['official_source_url']), FILTER_VALIDATE_URL) || ! filter_var(trim($row['cross_check_source_url']), FILTER_VALIDATE_URL)) throw new RuntimeException('Invalid source URL (line ' . $line . ').');
            if (trim($row['official_immigration_source_name']) === '' || trim($row['cross_check_source_name']) === '') throw new RuntimeException('Source name missing (line ' . $line . ').');
            if (! in_array(trim($row['verification_status']), ['pending_review', 'verified'], true)) throw new RuntimeException('Invalid verification status (line ' . $line . ').');
            if (! in_array(trim($row['publish_status']), ['draft', 'published'], true)) throw new RuntimeException('Invalid publication status (line ' . $line . ').');
            if (trim($row['publish_status']) === 'published' && trim($row['verification_status']) !== 'verified') throw new RuntimeException('Unverified rows cannot be published (line ' . $line . ').');
            try { Carbon::parse(trim($row['official_source_last_checked'])); } catch (Throwable) { throw new RuntimeException('Invalid source checked date (line ' . $line . ').'); }

            if (filter_var($row['full_audit'] ?? 'false', FILTER_VALIDATE_BOOLEAN)) {
                $fullColumns = ['visa_requirement_type', 'visa_required', 'visa_free_eligibility', 'visa_on_arrival_available', 'evisa_available', 'maximum_permitted_stay', 'entry_count', 'passport_validity_rule', 'blank_passport_pages_rule', 'return_ticket_rule', 'proof_of_funds_rule', 'accommodation_rule', 'travel_insurance_rule', 'yellow_fever_or_health_rule', 'transit_rule', 'application_channel', 'required_documents', 'additional_sources'];
                foreach ($fullColumns as $column) {
                    if (! array_key_exists($column, $row)) throw new RuntimeException('Missing full-audit column ' . $column . ' (line ' . $line . ').');
                }
                foreach (['visa_required', 'visa_free_eligibility', 'visa_on_arrival_available', 'evisa_available'] as $column) {
                    if (filter_var(trim($row[$column]), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) === null) throw new RuntimeException('Invalid boolean ' . $column . ' (line ' . $line . ').');
                }
                if (trim($row['required_documents']) === '') throw new RuntimeException('Required document list is empty (line ' . $line . ').');
                $extra = json_decode((string) $row['additional_sources'], true);
                if (! is_array($extra)) throw new RuntimeException('Additional sources must be a JSON array (line ' . $line . ').');
                foreach ($extra as $source) {
                    if (! in_array($source['source_type'] ?? null, ['official', 'provider', 'secondary'], true) || trim((string) ($source['source_name'] ?? '')) === '' || ! filter_var(trim((string) ($source['source_url'] ?? '')), FILTER_VALIDATE_URL)) {
                        throw new RuntimeException('Invalid additional source (line ' . $line . ').');
                    }
                }
            }

            $key = $iso2 . ':BGD:tourism';
            if (isset($seen[$key])) throw new RuntimeException('Duplicate destination/passport/purpose row (line ' . $line . ').');
            $seen[$key] = true;
        }
    }

    private function fullAuditPayload(array $row): array
    {
        $payload = [];
        $booleanFields = [
            'visa_required' => 'visa_required',
            'visa_free_eligibility' => 'visa_free_available',
            'visa_on_arrival_available' => 'visa_on_arrival_available',
            'evisa_available' => 'evisa_available',
        ];

        foreach ($booleanFields as $column => $field) {
            $value = filter_var(trim($row[$column]), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($value === null) throw new RuntimeException('Invalid boolean value for ' . $column . '.');
            $payload[$field] = $value;
        }

        $stringFields = [
            'visa_requirement_type' => 'requirement_type',
            'maximum_permitted_stay' => 'maximum_permitted_stay',
            'entry_count' => 'entry_count',
            'passport_validity_rule' => 'passport_validity_rule',
            'blank_passport_pages_rule' => 'blank_passport_pages_rule',
            'return_ticket_rule' => 'return_ticket_rule',
            'proof_of_funds_rule' => 'proof_of_funds_rule',
            'accommodation_rule' => 'accommodation_rule',
            'travel_insurance_rule' => 'travel_insurance_rule',
            'yellow_fever_or_health_rule' => 'health_requirements',
            'transit_rule' => 'transit_requirements',
            'application_channel' => 'application_channel',
        ];

        foreach ($stringFields as $column => $field) {
            $payload[$field] = trim((string) $row[$column]);
        }

        $documents = array_values(array_filter(array_map('trim', explode('|', (string) $row['required_documents']))));
        if ($documents === []) throw new RuntimeException('The full-audit required document list is empty.');
        $payload['required_documents'] = $documents;

        foreach (['effective_from', 'effective_until'] as $field) {
            if (trim((string) ($row[$field] ?? '')) !== '') $payload[$field] = trim($row[$field]);
        }

        return $payload;
    }

    private function addSource(string $base, int $id, array $sources, array $source, string $secret, bool $dry): bool
    {
        $url = trim($source['source_url']);
        $date = substr((string) $source['source_last_checked_at'], 0, 10);
        $exists = collect($sources)->contains(fn ($item) => ($item['source_url'] ?? null) === $url && substr((string) ($item['source_last_checked_at'] ?? ''), 0, 10) === $date && (bool) ($item['is_primary'] ?? false) === (bool) $source['is_primary']);
        if ($exists) return false;
        if (! $dry) $this->api('POST', $base . '/api/admin/visa-requirements/' . $id . '/sources', $source, $secret);
        return true;
    }

    private function api(string $method, string $url, array $payload, string $secret): array
    {
        $body = $payload === [] ? '' : json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        $timestamp = (string) now()->timestamp;
        $signature = hash_hmac('sha256', $timestamp . '.' . $body, $secret);
        $request = Http::timeout(45)->acceptJson()->withHeaders([
            'X-UniGo-Timestamp' => $timestamp,
            'X-UniGo-Signature' => $signature,
            'X-UniGo-Admin-User' => 'UniGo Automation',
            'Content-Type' => 'application/json',
        ]);
        $response = strtoupper($method) === 'GET'
            ? $request->get($url)
            : $request->withBody($body, 'application/json')->send(strtoupper($method), $url);
        if (! $response->successful()) throw new RuntimeException('Signed admin API request failed with HTTP ' . $response->status() . ': ' . $response->body());
        return $response->json() ?? [];
    }
}
