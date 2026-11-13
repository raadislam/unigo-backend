<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VisaRequirementSource extends Model
{
    protected $fillable = [
        'visa_requirement_id',
        'source_type',
        'source_name',
        'source_url',
        'provider_name',
        'provider_record_id',
        'source_last_checked_at',
        'is_primary',
        'verification_notes',
    ];

    protected function casts(): array
    {
        return [
            'source_last_checked_at' => 'datetime',
            'is_primary' => 'boolean',
        ];
    }

    public function visaRequirement(): BelongsTo
    {
        return $this->belongsTo(VisaRequirement::class);
    }
}
