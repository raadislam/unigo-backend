<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VisaRequirement extends Model
{
    protected $fillable = [
        'country_id',
        'passport_nationality_iso3',
        'passport_nationality_name',
        'travel_purpose',
        'requirement_type',
        'visa_required',
        'visa_free_available',
        'visa_on_arrival_available',
        'evisa_available',
        'maximum_permitted_stay',
        'entry_count',
        'passport_validity_rule',
        'blank_passport_pages_rule',
        'return_ticket_rule',
        'proof_of_funds_rule',
        'accommodation_rule',
        'travel_insurance_rule',
        'health_requirements',
        'transit_requirements',
        'application_channel',
        'application_fee',
        'processing_time',
        'required_documents',
        'verification_status',
        'is_published',
        'effective_from',
        'effective_until',
        'official_source_checked_at',
        'provider_updated_at',
        'last_synced_at',
    ];

    protected function casts(): array
    {
        return [
            'visa_required' => 'boolean',
            'visa_free_available' => 'boolean',
            'visa_on_arrival_available' => 'boolean',
            'evisa_available' => 'boolean',
            'required_documents' => 'array',
            'is_published' => 'boolean',
            'effective_from' => 'date',
            'effective_until' => 'date',
            'official_source_checked_at' => 'datetime',
            'provider_updated_at' => 'datetime',
            'last_synced_at' => 'datetime',
        ];
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function sources(): HasMany
    {
        return $this->hasMany(VisaRequirementSource::class);
    }

    public function histories(): HasMany
    {
        return $this->hasMany(VisaRequirementHistory::class);
    }
}
