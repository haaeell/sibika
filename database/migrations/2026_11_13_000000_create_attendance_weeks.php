<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_weeks', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('week_number');
            $table->date('starts_on');
            $table->date('ends_on');
            $table->timestamps();
            $table->unique(['week_number', 'starts_on']);
        });

        Schema::create('attendance_absences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attendance_week_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('meetings');
            $table->unsignedSmallInteger('absences');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['attendance_week_id', 'student_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_absences');
        Schema::dropIfExists('attendance_weeks');
    }
};
