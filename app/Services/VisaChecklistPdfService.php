<?php

namespace App\\Services;

use App\\Models\\VisaRequirement;
use setasign\\Fpdi\\Tcpdf\\Fpdi;

class VisaChecklistPdfService
{
    public function generate(VisaRequirement $requirement, array $options = []): string
    {
        $pdf = new class extends Fpdi {
            public string $letterheadPath = '';

            public function Header(): void
            {
                if (is_file($this->letterheadPath)) {
                    $this->ImageSVG($this->letterheadPath, 0, 0, 210, 297);
                }
                $this->SetY(43);
            }

            public function Footer(): void
            {
                $this->SetY(-14);
                $this->SetFont('helvetica', '', 8);
                $this->SetTextColor(90, 100, 112);
                $this->Cell(0, 7, 'UniGo Tours & Travels  |  Page '.$this->getAliasNumPage().' of '.$this->getAliasNbPages(), 0, 0, 'R');
            }
        };

        $pdf->letterheadPath = resource_path('pdf/unigo-letterhead.svg');
        $pdf->SetCreator('UniGo');
        $pdf->SetAuthor('UniGo Tours & Travels');
        $pdf->SetTitle(($requirement->country?->name ?? 'Destination').' visa checklist');
        $pdf->SetSubject('Bangladeshi ordinary passport visa checklist');
        $pdf->SetMargins(16, 44, 16);
        $pdf->SetHeaderMargin(0);
        $pdf->SetFooterMargin(12);
        $pdf->SetAutoPageBreak(true, 20);
        $pdf->setPrintHeader(true);
        $pdf->setPrintFooter(true);
        $pdf->SetFont('helvetica', '', 11);
        $pdf->AddPage();

        $country = $requirement->country?->name ?? 'Destination';
        $html = '<h1 style="color:#082b4c;font-size:17pt;">'.$this->escape($country).' Visa Checklist</h1>';
        $html .= '<p style="font-size:10pt;color:#526579;">Bangladeshi ordinary passport | '.ucfirst($this->escape((string) $requirement->travel_purpose)).'</p>';
        $html .= $this->section('Visa route and conditions', [
            'Requirement type' => $requirement->requirement_type,
            'Visa required' => $this->boolean($requirement->visa_required),
            'Visa-free available' => $this->boolean($requirement->visa_free_available),
            'Visa on arrival' => $this->boolean($requirement->visa_on_arrival_available),
            'eVisa available' => $this->boolean($requirement->evisa_available),
            'Maximum permitted stay' => $requirement->maximum_permitted_stay,
            'Entry count' => $requirement->entry_count,
            'Application channel' => $requirement->application_channel,
            'Application fee' => $requirement->application_fee,
            'Processing time' => $requirement->processing_time,
        ]);

        $scope = $options['scope'] ?? 'both';
        if ($scope !== 'sponsor') {
            $html .= $this->section('Required documents', $requirement->required_documents ?? []);
            $profiles = $requirement->applicant_checklists ?? [];
            foreach ($profiles as $profile) {
                $type = is_array($profile) ? ($profile['applicant_type'] ?? 'Applicant') : 'Applicant';
                $documents = is_array($profile) ? ($profile['documents'] ?? []) : [];
                $html .= '<h2>'. $this->escape(ucwords(str_replace(['_', '-'], ' ', $type))).' checklist</h2>';
                $html .= $this->documentList($documents);
                $html .= $this->section('Checklist source', [
                    'Source' => $profile['source_name'] ?? null,
                    'URL' => $profile['source_url'] ?? null,
                    'Last checked' => $profile['checked_at'] ?? null,
                ]);
            }
            if (!$profiles) {
                $html .= '<p>Occupation-specific checklist: no separate profile was supplied in the verified record. Confirm any additional occupation documents with the relevant authority.</p>';
            }
        }

        if ($scope !== 'applicant' && ($options['funding'] ?? 'full') !== 'self') {
            $html .= '<h2>Sponsor supporting documents</h2><p>These are supporting-document suggestions, not universal official requirements. Prepare only where relevant to the application route.</p>';
            $html .= $this->documentList([
                ['name' => 'Sponsor identity and lawful-status evidence, where applicable', 'status' => 'suggested'],
                ['name' => 'Signed invitation or financial-support letter stating the costs covered', 'status' => 'suggested'],
                ['name' => 'Recent evidence of funds available to the sponsor', 'status' => 'suggested'],
                ['name' => 'Evidence of relationship to the applicant, where relevant', 'status' => 'suggested'],
            ]);
            $html .= $this->section('Sponsor details', [
                'Sponsor occupation' => $options['sponsor_occupation'] ?? null,
                'Funding arrangement' => $options['funding'] ?? null,
                'Relationship to applicant' => $options['relationship'] ?? null,
            ]);
        }

        $html .= $this->section('Passport, financial and travel conditions', [
            'Passport validity' => $requirement->passport_validity_rule,
            'Blank passport pages' => $requirement->blank_passport_pages_rule,
            'Proof of funds' => $requirement->proof_of_funds_rule,
            'Return or onward ticket' => $requirement->return_ticket_rule,
            'Accommodation' => $requirement->accommodation_rule,
            'Travel insurance' => $requirement->travel_insurance_rule,
            'Health requirements' => $requirement->health_requirements,
            'Transit requirements' => $requirement->transit_requirements,
        ]);

        $photo = $requirement->photo_specifications ?? [];
        $html .= '<h2>Photograph specifications</h2>';
        $html .= $photo ? $this->section('', $photo) : '<p>No verified photo specification is currently recorded. Confirm photo capture and format with the official application centre.</p>';

        $sources = $requirement->sources ?? collect();
        $html .= '<h2>Official sources</h2>';
        foreach ($sources as $source) {
            $html .= '<p><strong>'.$this->escape($source->source_name ?? $source->source_type).'</strong><br />';
            if ($source->source_url) {
                $url = $this->escape($source->source_url);
                $html .= '<a href="'.$url.'">'.$url.'</a><br />';
            }
            $html .= 'Last checked: '.$this->escape((string) $source->source_last_checked_at).'</p>';
        }
        $html .= '<p style="font-size:9pt;color:#526579;">Conditional and if-applicable documents must be prepared only when the stated circumstances apply. Requirements, fees and processing times can change. Confirm the latest instructions with the responsible authority before applying.</p>';

        $pdf->writeHTML($html, true, false, true, false, '');
        $slug = $requirement->country?->slug ?? '';
        $url = 'https://unigo-bd.com/visa-services/'.$slug.'/';
        $pdf->Ln(2);
        $pdf->SetFont('helvetica', '', 9);
        $pdf->Cell(0, 6, 'Scan for the latest UniGo destination guide', 0, 1, 'L');
        $pdf->write2DBarcode($url, 'QRCODE,H', '', '', 24, 24, ['border' => 0, 'padding' => 1], 'N');

        return $pdf->Output('', 'S');
    }

    private function section(string $title, array $values): string
    {
        $html = $title !== '' ? '<h2>'.$this->escape($title).'</h2>' : '';
        $html .= '<table cellpadding="4" cellspacing="0" border="0">';
        foreach ($values as $key => $value) {
            if (is_int($key)) {
                $name = is_array($value) ? ($value['name'] ?? '') : $value;
                if (is_array($value)) {
                    $html .= '<tr><td>'.$this->escape((string) $name).'</td></tr>';
                } elseif ($name !== null && trim((string) $name) !== '') {
                    $html .= '<tr><td>'.$this->escape((string) $name).'</td></tr>';
                }
                continue;
            }
            if (is_array($value)) {
                $value = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            }
            if ($value === null || $value === '' || $value === false) {
                continue;
            }
            $html .= '<tr><td width="34%"><strong>'.$this->escape((string) $key).'</strong></td><td>'.$this->escape((string) $value).'</td></tr>';
        }
        return $html.'</table>';
    }

    private function documentList(array $documents): string
    {
        $html = '<table cellpadding="4" cellspacing="0" border="0">';
        foreach ($documents as $document) {
            $name = is_array($document) ? ($document['name'] ?? '') : $document;
            $status = is_array($document) ? ($document['status'] ?? 'required') : 'required';
            $notes = is_array($document) ? ($document['notes'] ?? null) : null;
            if ($name === '') {
                continue;
            }
            $html .= '<tr><td width="24%"><strong>'.$this->escape(ucwords(str_replace('_', ' ', (string) $status))).'</strong></td><td>'.$this->escape((string) $name);
            if ($notes) {
                $html .= '<br /><i>'.$this->escape((string) $notes).'</i>';
            }
            $html .= '</td></tr>';
        }
        return $html.'</table>';
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    private function boolean(?bool $value): ?string
    {
        return $value === null ? null : ($value ? 'Yes' : 'No');
    }
}
