<?php

namespace Tests\Unit;

use App\Models\Country;
use App\Models\VisaRequirement;
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

        $pdf = app(VisaChecklistPdfService::class)->generate($requirement, ['scope' => 'applicant']);

        $this->assertStringStartsWith('%PDF-', $pdf);
        $this->assertStringContainsString('%%EOF', $pdf);
        preg_match_all('/\/Type\s*\/Page\b/', $pdf, $pages);
        $this->assertGreaterThan(1, count($pages[0]));

        preg_match_all('/<([0-9A-F]{4,})>\s*Tj/', $pdf, $matches);
        $text = '';
        foreach ($matches[1] as $hex) {
            $decoded = @iconv('UTF-16BE', 'UTF-8//IGNORE', hex2bin($hex));
            if ($decoded !== false) {
                $text .= $decoded;
            }
        }

        $this->assertStringContainsString('Completeness verification document 001', $text);
        $this->assertStringContainsString('Completeness verification document 090', $text);
        $this->assertStringContainsString('USD 75', $text);
        $this->assertStringContainsString('35', $text);
        $this->assertStringContainsString('Page', $text);
    }
}
