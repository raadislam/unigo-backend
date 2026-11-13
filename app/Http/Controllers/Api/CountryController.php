<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\Service;
use App\Models\VisaCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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

        $countries = Country::query()
            ->where('is_published', true)
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
        $country = Country::query()
            ->where('slug', $slug)
            ->where('is_published', true)
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