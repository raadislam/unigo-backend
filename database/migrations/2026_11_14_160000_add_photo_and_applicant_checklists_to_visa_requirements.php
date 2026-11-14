<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('visa_requirements', function (Blueprint $table) {
            $table->json('photo_specifications')->nullable()->after('required_documents');
            $table->json('applicant_checklists')->nullable()->after('photo_specifications');
        });
    }

    public function down(): void
    {
        Schema::table('visa_requirements', function (Blueprint $table) {
            $table->dropColumn(['photo_specifications', 'applicant_checklists']);
        });
    }
};
