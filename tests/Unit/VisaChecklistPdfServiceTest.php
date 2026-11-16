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
        $requirement->required_documents = ['Do not include this embassy-level requirement'];
        $requirement->applicant_checklists = [
            [
                'applicant_type' => 'employee',
                'checklist_review_status' => 'verified',
                'client_documents' => [
                    ['name' => 'Employment certificate', 'status' => 'required'],
                    ['name' => 'Recent payslips', 'status' => 'required'],
                    ['name' => 'Leave approval', 'status' => 'conditional'],
                ],
            ],
            [
                'applicant_type' => 'business_owner',
                'checklist_review_status' => 'verified',
                'client_documents' => [
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
            'Employment certificate',
            'Recent payslips',
        ], $documents);
    }


    public function test_pdf_includes_only_explicit_client_submission_documents_and_deduplicates_them(): void
    {
        $requirement = new VisaRequirement();
        $requirement->required_documents = [
            'Biometrics appointment confirmation',
            'Travel medical insurance arranged by UniGo',
            'Completed visa application form prepared by UniGo',
        ];
        $requirement->applicant_checklists = [
            [
                'applicant_type' => 'employee',
                'checklist_review_status' => 'verified',
                'client_documents' => [
                    ['name' => 'Employer letter', 'status' => 'required'],
                    ['name' => 'Appointment attendance', 'status' => 'required'],
                ],
                'client_documents' => [
                    ['name' => 'Passport and biodata copy', 'status' => 'required'],
                    ['name' => 'Six-month personal bank statement', 'status' => 'required'],
                    ['name' => 'Employer letter', 'status' => 'required'],
                    ['name' => 'Passport and biodata copy', 'status' => 'required'],
                ],
            ],
            [
                'applicant_type' => 'business_owner',
                'checklist_review_status' => 'verified',
                'client_documents' => [
                    ['name' => 'Trade licence', 'status' => 'required'],
                ],
            ],
        ];

        $documents = app(VisaChecklistPdfService::class)->requiredDocuments($requirement, [
            'scope' => 'applicant',
            'applicant_occupation' => 'employee',
        ]);

        $this->assertSame([
            'Passport and biodata copy',
            'Six-month personal bank statement',
            'Employer letter',
        ], $documents);
    }

    public function test_checklist_does_not_fall_back_to_embassy_requirements_when_client_documents_are_absent(): void
    {
        $requirement = new VisaRequirement();
        $requirement->required_documents = ['Passport', 'Biometrics'];
        $requirement->applicant_checklists = [
            ['applicant_type' => 'employee', 'checklist_review_status' => 'verified', 'documents' => [['name' => 'Employer letter', 'status' => 'required']]],
        ];

        $this->assertSame([], app(VisaChecklistPdfService::class)->requiredDocuments($requirement, [
            'scope' => 'applicant',
            'applicant_occupation' => 'employee',
        ]));
    }

    public function test_sponsor_checklist_is_rejected_until_verified_sponsor_specific_requirements_exist(): void
    {
        $requirement = new VisaRequirement();
        $requirement->required_documents = ['Applicant passport'];
        $requirement->applicant_checklists = [
            [
                'applicant_type' => 'employee',
                'checklist_review_status' => 'verified',
                'client_documents' => [
                    ['name' => 'Employment certificate', 'status' => 'required'],
                ],
            ],
            [
                'applicant_type' => 'business_owner',
                'checklist_review_status' => 'verified',
                'client_documents' => [
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
                'checklist_review_status' => 'verified',
                'client_documents' => [
                    ['name' => 'Trade licence', 'status' => 'required'],
                    ['name' => 'Conditional tax evidence', 'status' => 'if_applicable'],
                ],
            ],
            [
                'applicant_type' => 'employee',
                'checklist_review_status' => 'verified',
                'client_documents' => [
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

    public function test_pdf_uses_the_original_uploaded_letterhead_page_size_and_contains_exactly_one_page(): void
    {
        $letterhead = resource_path('pdf/unigo-letterhead.pdf');
        $this->assertFileExists($letterhead);

        $country = new Country();
        $country->name = 'Test Destination';
        $country->slug = 'test-destination';

        $requirement = new VisaRequirement();
        $requirement->travel_purpose = 'tourism';
        $requirement->required_documents = ['Do not include embassy-level requirements'];
        $requirement->applicant_checklists = [[
            'applicant_type' => 'employee',
                'checklist_review_status' => 'verified',
            'client_documents' => [['name' => 'Applicant passport', 'status' => 'required']],
        ]];
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
