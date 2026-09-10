<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('score_average_subject_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->foreignId('major_id')->nullable()->constrained()->cascadeOnDelete();
            $table->boolean('include_in_average')->default(true);
            $table->timestamps();
            $table->unique(['subject_id', 'major_id'], 'score_average_subject_major_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('score_average_subject_settings');
    }
};
