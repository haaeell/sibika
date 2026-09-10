<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->restrictOnDelete();
            $table->unsignedTinyInteger('semester_number');
            $table->decimal('score', 5, 2)->nullable();
            $table->enum('status', ['draft', 'submitted', 'verified', 'rejected'])->default('draft')->index();
            $table->timestamp('submitted_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->text('verification_note')->nullable();
            $table->timestamps();
            $table->unique(['student_id', 'subject_id', 'semester_number'], 'student_subject_semester_unique');
            $table->index(['student_id', 'semester_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_scores');
    }
};
