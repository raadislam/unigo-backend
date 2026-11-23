<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('whatsapp_knowledge', function (Blueprint $table) {
            $table->id();
            $table->text('question');
            $table->text('normalized_question');
            $table->char('normalized_question_hash', 64)->unique();
            $table->longText('answer');
            $table->unsignedInteger('use_count')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_knowledge');
    }
};
