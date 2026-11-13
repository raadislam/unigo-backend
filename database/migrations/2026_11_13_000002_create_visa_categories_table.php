<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visa_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('slug', 120)->unique();
            $table->text('description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('country_visa_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')->constrained()->cascadeOnDelete();
            $table->foreignId('visa_category_id')->constrained()->cascadeOnDelete();
            $table->text('availability_note')->nullable();
            $table->string('application_method', 120)->nullable();
            $table->timestamps();
            $table->unique(['country_id', 'visa_category_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('country_visa_categories');
        Schema::dropIfExists('visa_categories');
    }
};