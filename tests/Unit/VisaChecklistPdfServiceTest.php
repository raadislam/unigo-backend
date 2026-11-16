<?php

namespace Tests\Unit;

use App\Models\Country;
use App\Models\VisaRequirement;
use App\Services\VisaChecklistPdfService;
use InvalidArgumentException;
use setasign\Fpdi\Tcpdf\Fpdi;
use Tests\TestCase;

class VisaChecklistPdfServiceTest extends TestCase
{
    public function test_occupation_filter_returns_only_required_documents_for_the_selected_profile(): void
    {
        $requirement = new VisaRequirement();
        $requirement->required_documents = [
            'Valid passport',
            'Completed application form',
            ['name' => 'Optional itinerary', 'status' => 'conditional'],
        ];
        $requirement->applicant_checklists = [
            [
                'applicant_type' => 'employee',
                'documents' => [
                    ['name' => 'Employment certificate', 'status' => 'required'],
                    ['name' => 'Recent payslips', 'status' => 'required'],
                    ['name' => 'Leave approval', 'status' => 'conditional'],
                ],
            ],
            [
                'applicant_type' => 'business_owner',
                'documents' => [
                    ['name' => 'Trade licence', 'status' => 'required'],
                    ['name' => 'Company bank statement', 'status' => 'required'],
                ],
            ],
        ];

        $documents = app(VisaChecklistPdfService::class)->requiredDocuments($requirement, [
            'scope' => 'applicant',
            'applicant_occupation' => 'employee',
        ]);

        $this->assertSame([
            'Valid passport',
            'Completed application form',
            'Employment certificate',
            'Recent payslips',
        ], $documents);
    }

    public function test_sponsor_checklist_is_rejected_until_verified_sponsor_specific_requirements_exist(): void
    {
        $requirement = new VisaRequirement();
        $requirement->required_documents = ['Applicant passport'];
        $requirement->applicant_checklists = [
            [
                'applicant_type' => 'employee',
                'documents' => [
                    ['name' => 'Employment certificate', 'status' => 'required'],
                ],
            ],
            [
                'applicant_type' => 'business_owner',
                'documents' => [
                    ['name' => 'Trade licence', 'status' => 'required'],
                ],
            ],
        ];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Only applicant checklists are currently supported');

        app(VisaChecklistPdfService::class)->requiredDocuments($requirement, [
            'scope' => 'sponsor',
            'sponsor_occupation' => 'business',
            'funding' => 'full',
        ]);
    }

    public function test_combined_checklist_scope_is_rejected_instead_of_silently_omitting_sponsor_documents(): void
    {
        $requirement = new VisaRequirement();
        $requirement->required_documents = ['Applicant passport'];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Only applicant checklists are currently supported');

        app(VisaChecklistPdfService::class)->requiredDocuments($requirement, [
            'scope' => 'both',
            'applicant_occupation' => 'employee',
            'funding' => 'self',
        ]);
    }

    public function test_business_occupation_matches_the_published_combined_business_profile(): void
    {
        $requirement = new VisaRequirement();
        $requirement->required_documents = [];
        $requirement->applicant_checklists = [
            [
                'applicant_type' => 'self_employed_or_business_owner',
                'documents' => [
                    ['name' => 'Trade licence', 'status' => 'required'],
                    ['name' => 'Conditional tax evidence', 'status' => 'if_applicable'],
                ],
            ],
            [
                'applicant_type' => 'employee',
                'documents' => [
                    ['name' => 'Employment certificate', 'status' => 'required'],
                ],
            ],
        ];

        $documents = app(VisaChecklistPdfService::class)->requiredDocuments($requirement, [
            'scope' => 'applicant',
            'applicant_occupation' => 'business',
        ]);

        $this->assertSame(['Trade licence'], $documents);
    }

    public function test_italy_pdf_collapses_semantic_duplicates_and_keeps_the_detailed_vfs_requirements(): void
    {
        $country = new Country();
        $country->iso2 = 'IT';

        $requirement = new VisaRequirement();
        $requirement->required_documents = [
            'Valid passport issued within the previous 10 years',
            'Completed Schengen visa application form',
            'Recent ICAO-compliant photograph',
            'Travel medical insurance covering emergency medical care and repatriation',
            'Evidence of purpose and itinerary',
            'Evidence of accommodation',
            'Evidence of sufficient financial means',
            'Recent colour photograph meeting destination-specific rules',
            'Completed and signed short-stay Schengen application form',
            'Passport with at least 2 blank visa pages and validity extending 3 months beyond departure',
            'Return airline booking showing Passenger Registration Number and travel itinerary',
            'Hotel confirmation for the entire Schengen stay',
            'Travel insurance for all Schengen countries, minimum EUR 30,000',
        ];
        $requirement->applicant_checklists = [[
            'applicant_type' => 'employee',
            'documents' => [
                ['name' => 'Original employer letter showing role, salary, service duration, leave and funding', 'status' => 'required'],
                ['name' => 'Personal bank statement for the last 6 months and other financial solvency evidence', 'status' => 'required'],
            ],
        ]];
        $requirement->setRelation('country', $country);

        $documents = app(VisaChecklistPdfService::class)->requiredDocuments($requirement, [
            'scope' => 'applicant',
            'applicant_occupation' => 'employee',
        ]);

        $this->assertCount(1, array_filter($documents, fn ($document) => str_contains(strtolower($document), 'photograph')));
        $this->assertCount(1, array_filter($documents, fn ($document) => str_contains(strtolower($document), 'passport')));
        $this->assertCount(1, array_filter($documents, fn ($document) => str_contains(strtolower($document), 'insurance')));
        $this->assertCount(1, array_filter($documents, fn ($document) => str_contains(strtolower($document), 'accommodation') || str_contains(strtolower($document), 'hotel')));
        $this->assertCount(1, array_filter($documents, fn ($document) => str_contains(strtolower($document), 'financial') || str_contains(strtolower($document), 'bank statement')));
        $this->assertContains('Two recent identical ICAO-compliant colour photographs, 40 × 35 mm, with a white background, taken within the last 6 months', $documents);
        $this->assertContains('Original employer letter showing role, salary, service duration, leave and funding', $documents);
    }

    public function test_pdf_uses_the_original_uploaded_letterhead_page_size_and_contains_exactly_one_page(): void
    {
        $letterhead = resource_path('pdf/unigo-letterhead.pdf');
        $this->assertFileExists($letterhead);

        $country = new Country();
        $country->name = 'Test Destination';
        $country->slug = 'test-destination';

        $requirement = new VisaRequirement();
        $requirement->travel_purpose = 'tourism';
        $requirement->required_documents = ['Valid passport', 'Completed visa application form'];
        $requirement->setRelation('country', $country);

        $source = new Fpdi();
        $source->setSourceFile($letterhead);
        $sourceTemplate = $source->importPage(1);
        $sourceSize = $source->getTemplateSize($sourceTemplate);

        $bytes = app(VisaChecklistPdfService::class)->generate($requirement, [
            'scope' => 'applicant',
            'applicant_occupation' => 'employee',
        ]);

        $this->assertStringStartsWith('%PDF-', $bytes);
        $this->assertStringContainsString('%%EOF', $bytes);

        $temporaryPdf = tempnam(sys_get_temp_dir(), 'unigo-checklist-');
        try {
            file_put_contents($temporaryPdf, $bytes);
            $result = new Fpdi();
            $this->assertSame(1, $result->setSourceFile($temporaryPdf));
            $resultTemplate = $result->importPage(1);
            $resultSize = $result->getTemplateSize($resultTemplate);
            $this->assertEqualsWithDelta($sourceSize['width'], $resultSize['width'], 0.1);
            $this->assertEqualsWithDelta($sourceSize['height'], $resultSize['height'], 0.1);
        } finally {
            @unlink($temporaryPdf);
        }
    }
}
