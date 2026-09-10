<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('score_subject_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained()->restrictOnDelete();
            $table->foreignId('major_id')->nullable()->constrained()->restrictOnDelete();
            $table->unsignedTinyInteger('semester_number');
            $table->boolean('is_required')->default(true);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
            $table->unique(['subject_id', 'major_id', 'semester_number'], 'score_setting_subject_major_semester_unique');
            $table->index(['semester_number', 'major_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('score_subject_settings');
    }
};
