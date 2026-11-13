<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('visa_requirements', function (Blueprint $table) {
            $table->text('application_channel')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('visa_requirements', function (Blueprint $table) {
            $table->string('application_channel', 180)->nullable()->change();
        });
    }
};
