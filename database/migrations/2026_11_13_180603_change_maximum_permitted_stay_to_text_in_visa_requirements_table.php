<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('visa_requirements', function (Blueprint $table) {
            $table->text('maximum_permitted_stay')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('visa_requirements', function (Blueprint $table) {
            $table->string('maximum_permitted_stay', 120)->nullable()->change();
        });
    }
};
