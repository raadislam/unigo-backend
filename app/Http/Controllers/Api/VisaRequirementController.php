<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\VisaRequirement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VisaRequirementController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'passport' => ['nullable', 'string', 'size:3', 'alpha'],
            'destination' => ['nullable', 'string', 'size:2', 'alpha'],
            'purpose' => ['nullable', 'string', 'in:tourism,business,study,transit,work'],
        ]);

        $requirements = VisaRequirement::query()
            ->with([
                'country:id,name,slug,iso2,iso3,flag_emoji',
                'sources' => fn ($query) => $query
                    ->where('source_type', 'official')
                    ->orderByDesc('is_primary')
                    ->get(['id', 'visa_requirement_id', 'source_type', 'source_name', 'source_url', 'source_last_checked_at', 'is_primary']),
            ])
            ->where('passport_nationality_iso3', strtoupper($validated['passport'] ?? 'BGD'))
            ->where('travel_purpose', $validated['purpose'] ?? 'tourism')
            ->where('verification_status', 'verified')
            ->where('is_published', true)
            ->where(function ($query) {
                $query->whereNull('effective_from')->orWhereDate('effective_from', '<=', now()->toDateString());
            })
            ->where(function ($query) {
                $query->whereNull('effective_until')->orWhereDate('effective_until', '>=', now()->toDateString());
            })
            ->when($validated['destination'] ?? null, function ($query, $destination) {
                $query->whereHas('country', fn ($country) => $country->where('iso2', strtoupper($destination)));
            })
            ->orderBy('country_id')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $requirements,
            'meta' => [
                'total' => $requirements->count(),
                'passport' => strtoupper($validated['passport'] ?? 'BGD'),
                'purpose' => $validated['purpose'] ?? 'tourism',
                'destination_filter' => isset($validated['destination']) ? strtoupper($validated['destination']) : null,
                'notice' => 'Visa and entry requirements can change. Confirm requirements with the destination government before travel.',
            ],
        ]);
    }

    public function show(Request $request, string $destination): JsonResponse
    {
        $validated = $request->validate([
            'passport' => ['nullable', 'string', 'size:3', 'alpha'],
            'purpose' => ['nullable', 'string', 'in:tourism,business,study,transit,work'],
        ]);

        $requirement = VisaRequirement::query()
            ->with([
                'country:id,name,slug,iso2,iso3,flag_emoji',
                'sources' => fn ($query) => $query->where('source_type', 'official')->orderByDesc('is_primary'),
            ])
            ->where('passport_nationality_iso3', strtoupper($validated['passport'] ?? 'BGD'))
            ->where('travel_purpose', $validated['purpose'] ?? 'tourism')
            ->where('verification_status', 'verified')
            ->where('is_published', true)
            ->whereHas('country', fn ($country) => $country->where('iso2', strtoupper($destination)))
            ->where(function ($query) {
                $query->whereNull('effective_from')->orWhereDate('effective_from', '<=', now()->toDateString());
            })
            ->where(function ($query) {
                $query->whereNull('effective_until')->orWhereDate('effective_until', '>=', now()->toDateString());
            })
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => $requirement,
            'meta' => [
                'notice' => 'Visa and entry requirements can change. Confirm requirements with the destination government before travel.',
            ],
        ]);
    }
}
