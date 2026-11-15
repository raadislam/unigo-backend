<?php

namespace Tests\Unit;

use App\Models\Country;
use App\Models\VisaRequirement;
use App\Models\VisaRequirementSource;
use App\Services\VisaChecklistPdfService;
use Illuminate\Support\Collection;
use Tests\TestCase;

class VisaChecklistPdfServiceTest extends TestCase
{
    public function test_it_generates_a_multi_page_pdf_with_all_checklist_items_and_official_details(): void
    {
        $country = new Country();
        $country->name = 'Test Destination';
        $country->slug = 'test-destination';

        $requirement = new VisaRequirement();
        $requirement->travel_purpose = 'tourism';
        $requirement->application_fee = 'USD 75';
        $requirement->required_documents = array_map(
            fn (int $number) => sprintf('Completeness verification document %03d', $number),
            range(1, 90)
        );
        $requirement->photo_specifications = [
            'status' => 'verified',
            'dimensions_mm' => '35 × 45 mm',
            'background' => 'White background',
        ];
        $requirement->setRelation('country', $country);
        $requirement->setRelation('sources', new Collection());

        $source = new VisaRequirementSource();
        $source->source_type = 'official';
        $source->source_name = 'Test Government';
        $source->source_url = 'https://example.gov/visa';
        $source->source_last_checked_at = now();
        $requirement->setRelation('sources', new Collection([$source]));

        $service = app(VisaChecklistPdfService::class);
        $html = $service->buildHtml($requirement, ['scope' => 'applicant']);
        $this->assertStringContainsString('Completeness verification document 001', $html);
        $this->assertStringContainsString('Completeness verification document 090', $html);
        $this->assertStringContainsString('USD 75', $html);
        $this->assertStringContainsString('35 × 45 mm', $html);
        $this->assertStringContainsString('https://example.gov/visa', $html);

        $pdf = $service->generate($requirement, ['scope' => 'applicant']);
        $this->assertStringStartsWith('%PDF-', $pdf);
        $this->assertStringContainsString('%%EOF', $pdf);
        preg_match_all('~/Type\s*/Page\b~', $pdf, $pages);
        $this->assertGreaterThan(1, count($pages[0]));
    }
}
