<?php

namespace App\\Http\\Controllers\\Api;

use App\\Http\\Controllers\\Controller;
use App\\Models\\Country;
use App\\Models\\Service;
use App\\Models\\VisaCategory;
use App\\Models\\VisaRequirement;
use Illuminate\\Http\\JsonResponse;
use Illuminate\\Http\\Request;

class CountryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'letter' => ['nullable', 'string', 'size:1', 'alpha'],
            'category' => ['nullable', 'string', 'max:120'],
            'service' => ['nullable', 'string', 'max:160'],
        ]);

        $publishedRequirements = VisaRequirement::query()
            ->select('country_id')
            ->where('passport_nationality_iso3', 'BGD')
            ->where('travel_purpose', 'tourism')
            ->where('verification_status', 'verified')
            ->where('is_published', true)
            ->where(fn ($query) => $query->whereNull('effective_from')->orWhereDate('effective_from', '<=', now()->toDateString()))
            ->where(fn ($query) => $query->whereNull('effective_until')->orWhereDate('effective_until', '>=', now()->toDateString()));

        $countries = Country::query()
            ->where(fn ($query) => $query
                ->where('is_published', true)
                ->orWhereIn('id', $publishedRequirements))
            ->with([
                'visaCategories' => fn ($query) => $query->where('is_active', true),
                'services' => fn ($query) => $query->where('is_active', true),
            ])
            ->when($validated['q'] ?? null, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->when($validated['letter'] ?? null, function ($query, $letter) {
                $query->where('name', 'like', strtoupper($letter) . '%');
            })
            ->when($validated['category'] ?? null, function ($query, $category) {
                $query->whereHas('visaCategories', fn ($relation) => $relation->where('slug', $category));
            })
            ->when($validated['service'] ?? null, function ($query, $service) {
                $query->whereHas('services', fn ($relation) => $relation->where('slug', $service));
            })
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $countries,
            'meta' => [
                'total' => $countries->count(),
                'filters' => [
                    'categories' => VisaCategory::query()->where('is_active', true)->orderBy('sort_order')->get(['name', 'slug']),
                    'services' => Service::query()->where('is_active', true)->orderBy('sort_order')->get(['name', 'slug']),
                ],
            ],
        ]);
    }

    public function show(string $slug): JsonResponse
    {
        $publishedRequirements = VisaRequirement::query()
            ->select('country_id')
            ->where('passport_nationality_iso3', 'BGD')
            ->where('travel_purpose', 'tourism')
            ->where('verification_status', 'verified')
            ->where('is_published', true)
            ->where(fn ($query) => $query->whereNull('effective_from')->orWhereDate('effective_from', '<=', now()->toDateString()))
            ->where(fn ($query) => $query->whereNull('effective_until')->orWhereDate('effective_until', '>=', now()->toDateString()));

        $country = Country::query()
            ->where('slug', $slug)
            ->where(fn ($query) => $query
                ->where('is_published', true)
                ->orWhereIn('id', $publishedRequirements))
            ->with([
                'visaCategories' => fn ($query) => $query->where('is_active', true),
                'services' => fn ($query) => $query->where('is_active', true),
            ])
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => $country,
        ]);
    }
}
