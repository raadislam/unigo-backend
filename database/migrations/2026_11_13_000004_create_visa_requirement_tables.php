<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visa_requirements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')->constrained('countries')->cascadeOnDelete();
            $table->char('passport_nationality_iso3', 3)->default('BGD');
            $table->string('passport_nationality_name', 100)->default('Bangladesh');
            $table->string('travel_purpose', 40)->default('tourism');
            $table->string('requirement_type', 50)->nullable();
            $table->boolean('visa_required')->nullable();
            $table->boolean('visa_free_available')->nullable();
            $table->boolean('visa_on_arrival_available')->nullable();
            $table->boolean('evisa_available')->nullable();
            $table->string('maximum_permitted_stay', 120)->nullable();
            $table->string('entry_count', 40)->nullable();
            $table->text('passport_validity_rule')->nullable();
            $table->text('blank_passport_pages_rule')->nullable();
            $table->text('return_ticket_rule')->nullable();
            $table->text('proof_of_funds_rule')->nullable();
            $table->text('accommodation_rule')->nullable();
            $table->text('travel_insurance_rule')->nullable();
            $table->text('health_requirements')->nullable();
            $table->text('transit_requirements')->nullable();
            $table->string('application_channel', 180)->nullable();
            $table->string('application_fee', 120)->nullable();
            $table->string('processing_time', 120)->nullable();
            $table->json('required_documents')->nullable();
            $table->string('verification_status', 30)->default('not_verified')->index();
            $table->boolean('is_published')->default(false)->index();
            $table->date('effective_from')->nullable();
            $table->date('effective_until')->nullable();
            $table->timestamp('official_source_checked_at')->nullable();
            $table->timestamp('provider_updated_at')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();
            $table->unique(
                ['country_id', 'passport_nationality_iso3', 'travel_purpose'],
                'visa_req_country_passport_purpose_unique'
            );
            $table->index(['passport_nationality_iso3', 'travel_purpose', 'is_published'], 'visa_req_public_lookup_idx');
        });

        Schema::create('visa_requirement_sources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('visa_requirement_id')->constrained('visa_requirements')->cascadeOnDelete();
            $table->string('source_type', 30);
            $table->string('source_name', 180)->nullable();
            $table->text('source_url');
            $table->string('provider_name', 100)->nullable();
            $table->string('provider_record_id', 180)->nullable();
            $table->timestamp('source_last_checked_at')->nullable();
            $table->boolean('is_primary')->default(false);
            $table->text('verification_notes')->nullable();
            $table->timestamps();
            $table->index(['visa_requirement_id', 'source_type']);
        });

        Schema::create('visa_requirement_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('visa_requirement_id')->constrained('visa_requirements')->cascadeOnDelete();
            $table->json('snapshot');
            $table->string('change_source', 40)->default('manual');
            $table->string('changed_by', 120)->nullable();
            $table->text('change_summary')->nullable();
            $table->timestamp('recorded_at');
            $table->timestamps();
            $table->index(['visa_requirement_id', 'recorded_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visa_requirement_histories');
        Schema::dropIfExists('visa_requirement_sources');
        Schema::dropIfExists('visa_requirements');
    }
};
