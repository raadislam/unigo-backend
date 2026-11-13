<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class VisaCategory extends Model
{
    protected $fillable = ['name', 'slug', 'description', 'sort_order', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function countries(): BelongsToMany
    {
        return $this->belongsToMany(Country::class, 'country_visa_categories')
            ->withPivot(['availability_note', 'application_method'])
            ->withTimestamps();
    }
}