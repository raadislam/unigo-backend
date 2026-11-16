<?php

namespace App\Services;

use App\Models\VisaRequirement;
use DomainException;
use InvalidArgumentException;
use RuntimeException;
use setasign\Fpdi\Tcpdf\Fpdi;

class VisaChecklistPdfService
{
    public function generate(VisaRequirement $requirement, array $options = []): string
    {
        $letterheadPath = resource_path('pdf/unigo-letterhead.pdf');

        if (! is_file($letterheadPath)) {
            throw new RuntimeException('The original UniGo uploaded PDF letterhead is missing.');
        }

        $pdf = new Fpdi();
        $pageCount = $pdf->setSourceFile($letterheadPath);

        if ($pageCount < 1) {
            throw new RuntimeException('The original UniGo PDF letterhead has no pages.');
        }

        $templateId = $pdf->importPage(1);
        $pageSize = $pdf->getTemplateSize($templateId);
        $pageWidth = (float) $pageSize['width'];
        $pageHeight = (float) $pageSize['height'];
        $left = $pageWidth * 0.08;
        $right = $pageWidth * 0.08;
        $top = $pageHeight * 0.155;
        $bottom = $pageHeight * 0.09;
        $qrSize = min(17.0, $pageWidth * 0.075);
        $contentWidth = $pageWidth - $left - $right;

        $pdf->SetCreator('UniGo');
        $pdf->SetAuthor('UniGo Tours & Travels');
        $pdf->SetTitle(($requirement->country?->name ?? 'Destination').' required visa documents');
        $pdf->SetSubject('Required visa documents for Bangladeshi ordinary passport holders');
        $pdf->SetMargins($left, $top, $right);
        $pdf->SetHeaderMargin(0);
        $pdf->SetFooterMargin(0);
        $pdf->SetAutoPageBreak(false, 0);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetFont('dejavusans', '', 8.5);
        $pdf->AddPage($pageSize['orientation'], [$pageWidth, $pageHeight]);
        $pdf->useTemplate($templateId, 0, 0, $pageWidth, $pageHeight);

        $pdf->SetXY($left, $top);
        $pdf->SetFont('dejavusans', 'B', 13);
        $pdf->SetTextColor(8, 43, 76);
        $pdf->MultiCell($contentWidth, 6, ($requirement->country?->name ?? 'Destination').' Visa Checklist', 0, 'L', false, 1);

        $pdf->SetFont('dejavusans', '', 8);
        $pdf->SetTextColor(82, 101, 121);
        $pdf->MultiCell($contentWidth, 4.5, 'Bangladeshi ordinary passport  |  Tourism', 0, 'L', false, 1);
        $pdf->Ln(2);

        $documents = $this->requiredDocuments($requirement, $options);
        if ($documents === []) {
            throw new DomainException('No required documents match the selected occupation filter.');
        }

        $pdf->SetFont('dejavusans', 'B', 9);
        $pdf->SetTextColor(8, 43, 76);
        $pdf->MultiCell($contentWidth, 5, 'Required documents', 0, 'L', false, 1);
        $pdf->Ln(1);
        $pdf->SetFont('dejavusans', '', 8.5);
        $pdf->SetTextColor(38, 63, 89);

        foreach ($documents as $index => $document) {
            $pdf->MultiCell($contentWidth, 4.6, ($index + 1).'. '.$document, 0, 'L', false, 1);
            $pdf->Ln(0.4);
        }

        $qrY = $pageHeight - $bottom - $qrSize;
        if ($pdf->GetY() + 4 > $qrY) {
            throw new DomainException('The selected required documents do not fit safely on one original letterhead page.');
        }

        $pdf->SetDrawColor(215, 25, 45);
        $pdf->SetLineWidth(0.25);
        $pdf->Line($left, $qrY - 3, $pageWidth - $right, $qrY - 3);
        $pdf->SetXY($left, $qrY + 1);
        $pdf->SetFont('dejavusans', 'B', 8);
        $pdf->SetTextColor(8, 43, 76);
        $pdf->MultiCell($contentWidth - $qrSize - 5, 5, 'To get full information, please visit here.', 0, 'L', false, 1);
        $url = 'https://unigo-bd.com/visa-services/'.($requirement->country?->slug ?? '').'/';
        $pdf->write2DBarcode($url, 'QRCODE,H', $pageWidth - $right - $qrSize, $qrY, $qrSize, $qrSize, ['border' => 0, 'padding' => 1], 'N');

        if ($pdf->getNumPages() !== 1) {
            throw new RuntimeException('The visa checklist must be exactly one page.');
        }

        return $pdf->Output('', 'S');
    }

    public function requiredDocuments(VisaRequirement $requirement, array $options = []): array
    {
        $scope = $options['scope'] ?? 'applicant';
        $applicantOccupation = $options['applicant_occupation'] ?? 'employee';
        $documents = [];

        if ($scope !== 'applicant') {
            throw new InvalidArgumentException('Only applicant checklists are currently supported. Sponsor-specific requirements are not yet stored as verified destination data.');
        }

        foreach ($requirement->required_documents ?? [] as $document) {
            $this->appendRequiredDocument($documents, $document);
        }

        foreach ($requirement->applicant_checklists ?? [] as $profile) {
            if (! is_array($profile) || ! $this->matchesOccupation($profile['applicant_type'] ?? '', $applicantOccupation)) {
                continue;
            }

            foreach ($profile['documents'] ?? [] as $document) {
                $this->appendRequiredDocument($documents, $document);
            }
        }

        return array_values($documents);
    }

    private function appendRequiredDocument(array &$documents, mixed $document): void
    {
        if (is_array($document)) {
            $status = strtolower(trim((string) ($document['status'] ?? '')));
            if (! in_array($status, ['required', 'mandatory'], true)) {
                return;
            }
            $name = trim((string) ($document['name'] ?? ''));
        } else {
            $name = trim((string) $document);
        }

        if ($name === '') {
            return;
        }

        $key = mb_strtolower(preg_replace('/\s+/', ' ', $name));
        $documents[$key] = $name;
    }

    private function matchesOccupation(string $applicantType, string $occupation): bool
    {
        $type = strtolower(trim(str_replace([' ', '-'], '_', $applicantType)));
        $occupation = strtolower(trim($occupation));

        $aliases = [
            'employee' => ['employee', 'employed', 'salaried_employee', 'government_employee', 'private_employee'],
            'business' => ['business', 'business_owner', 'self_employed', 'self_employed_or_business_owner', 'entrepreneur', 'trader'],
            'student' => ['student', 'pupil'],
            'retired' => ['retired', 'pensioner'],
            'other' => ['other', 'unemployed', 'freelancer'],
        ];

        return in_array($type, $aliases[$occupation] ?? [$occupation], true);
    }
}
