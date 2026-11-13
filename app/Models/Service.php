<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Service extends Model
{
    protected $fillable = ['name', 'slug', 'description', 'is_active', 'sort_order'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function countries(): BelongsToMany
    {
        return $this->belongsToMany(Country::class, 'country_services')
            ->withPivot(['is_featured', 'sort_order', 'description'])
            ->withTimestamps();
    }
}