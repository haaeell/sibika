<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_parents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->enum('parent_type', ['father', 'mother', 'guardian']);
            $table->string('name', 100)->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('occupation', 100)->nullable();
            $table->string('education', 100)->nullable();
            $table->string('income_range', 50)->nullable();
            $table->string('relation', 50)->nullable();
            $table->timestamps();
            $table->unique(['student_id', 'parent_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_parents');
    }
};
