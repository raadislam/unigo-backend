<?php

namespace Tests\Feature;

use App\Models\Country;
use App\Models\VisaRequirement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VisaRequirementApplicantChecklistMetadataTest extends TestCase
{
    use RefreshDatabase;

    private function requirement(): VisaRequirement
    {
        $country = Country::create([
            'name' => 'Test Country',
            'slug' => 'test-country',
            'iso2' => 'ZZ',
            'iso3' => 'ZZZ',
        ]);

        return VisaRequirement::create([
            'country_id' => $country->id,
            'passport_nationality_iso3' => 'BGD',
            'passport_nationality_name' => 'Bangladesh',
            'travel_purpose' => 'tourism',
            'visa_required' => true,
            'verification_status' => 'pending_review',
            'is_published' => false,
        ]);
    }

    public function test_applicant_checklist_source_metadata_survives_admin_update_validation(): void
    {
        $this->withoutMiddleware();
        $requirement = $this->requirement();

        $payload = [
            'applicant_checklists' => [
                [
                    'applicant_type' => 'employee',
                    'notes' => 'Official visitor checklist',
                    'source_name' => 'Official Immigration Checklist',
                    'source_url' => 'https://example.gov/visitor-checklist',
                    'checked_at' => '2026-11-15',
                    'documents' => [
                        [
                            'name' => 'Passport',
                            'status' => 'required',
                            'notes' => 'Original passport',
                        ],
                    ],
                ],
            ],
        ];

        $response = $this->putJson('/api/admin/visa-requirements/' . $requirement->id, $payload);

        $response->assertOk()
            ->assertJsonPath('data.applicant_checklists.0.source_name', 'Official Immigration Checklist')
            ->assertJsonPath('data.applicant_checklists.0.source_url', 'https://example.gov/visitor-checklist')
            ->assertJsonPath('data.applicant_checklists.0.checked_at', '2026-11-15');

        $stored = $requirement->fresh()->applicant_checklists;
        $this->assertSame('Official Immigration Checklist', $stored[0]['source_name']);
        $this->assertSame('https://example.gov/visitor-checklist', $stored[0]['source_url']);
        $this->assertSame('2026-11-15', $stored[0]['checked_at']);
    }

    public function test_applicant_checklist_source_metadata_remains_optional_for_manual_admin_edits(): void
    {
        $this->withoutMiddleware();
        $requirement = $this->requirement();

        $response = $this->putJson('/api/admin/visa-requirements/' . $requirement->id, [
            'applicant_checklists' => [
                [
                    'applicant_type' => 'student',
                    'notes' => 'Manual draft checklist',
                    'documents' => [
                        [
                            'name' => 'Passport',
                            'status' => 'required',
                            'notes' => 'Original passport',
                        ],
                    ],
                ],
            ],
        ]);

        $response->assertOk();
        $this->assertSame('student', $requirement->fresh()->applicant_checklists[0]['applicant_type']);
    }
}
