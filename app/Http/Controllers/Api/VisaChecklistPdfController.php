<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\VisaRequirement;
use App\Services\VisaChecklistPdfService;
use Illuminate\Http\Request;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;

class VisaChecklistPdfController extends Controller
{
    public function __invoke(Request $request, string $destination, VisaChecklistPdfService $pdfService): Response
    {
        $options = $request->validate([
            'scope' => ['nullable', 'in:applicant,both,sponsor'],
            'applicant_occupation' => ['nullable', 'in:employee,business,student,retired,other'],
            'funding' => ['nullable', 'in:self,full,partial'],
        ]);

        $requirement = VisaRequirement::query()
            ->with('country:id,name,slug,iso2')
            ->where('passport_nationality_iso3', 'BGD')
            ->where('travel_purpose', 'tourism')
            ->where('verification_status', 'verified')
            ->where('is_published', true)
            ->where(fn ($query) => $query->whereNull('effective_from')->orWhereDate('effective_from', '<=', now()->toDateString()))
            ->where(fn ($query) => $query->whereNull('effective_until')->orWhereDate('effective_until', '>=', now()->toDateString()))
            ->whereHas('country', fn ($query) => $query->where('slug', $destination))
            ->firstOrFail();

        try {
            $bytes = $pdfService->generate($requirement, $options);
        } catch (InvalidArgumentException $exception) {
            return response()->json(['success' => false, 'message' => $exception->getMessage()], 422);
        }
        $filename = ($requirement->country?->slug ?: 'destination').'-visa-checklist-unigo.pdf';

        return response($bytes, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            'Content-Length' => (string) strlen($bytes),
            'Cache-Control' => 'private, no-store, max-age=0',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
