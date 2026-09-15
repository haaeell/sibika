<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_achievements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->string('type', 30);
            $table->string('name', 150);
            $table->string('level', 30);
            $table->unsignedSmallInteger('year');
            $table->timestamps();
        });

        Schema::create('student_organizations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->string('name', 150);
            $table->string('position', 100);
            $table->string('level', 30);
            $table->unsignedSmallInteger('year');
            $table->timestamps();
        });

        Schema::table('student_documents', function (Blueprint $table) {
            $table->foreignId('achievement_id')->nullable()->after('student_id')->constrained('student_achievements')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('student_documents', function (Blueprint $table) {
            $table->dropConstrainedForeignId('achievement_id');
        });
        Schema::dropIfExists('student_organizations');
        Schema::dropIfExists('student_achievements');
    }
};
