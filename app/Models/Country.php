<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Country extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'iso2',
        'iso3',
        'flag_emoji',
        'capital',
        'short_description',
        'overview',
        'visa_information',
        'seo_title',
        'seo_description',
        'seo_keywords',
        'is_published',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'seo_keywords' => 'array',
            'is_published' => 'boolean',
        ];
    }

    public function visaCategories(): BelongsToMany
    {
        return $this->belongsToMany(VisaCategory::class, 'country_visa_categories')
            ->withPivot(['availability_note', 'application_method'])
            ->withTimestamps()
            ->orderBy('sort_order');
    }

    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class, 'country_services')
            ->withPivot(['is_featured', 'sort_order', 'description'])
            ->withTimestamps()
            ->orderByPivot('sort_order');
    }
}