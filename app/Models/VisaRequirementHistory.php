<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VisaRequirementHistory extends Model
{
    protected $fillable = [
        'visa_requirement_id',
        'snapshot',
        'change_source',
        'changed_by',
        'change_summary',
        'recorded_at',
    ];

    protected function casts(): array
    {
        return [
            'snapshot' => 'array',
            'recorded_at' => 'datetime',
        ];
    }

    public function visaRequirement(): BelongsTo
    {
        return $this->belongsTo(VisaRequirement::class);
    }
}
