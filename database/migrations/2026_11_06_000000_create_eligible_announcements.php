<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('eligible_announcements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cohort_id')->unique()->constrained()->cascadeOnDelete();
            $table->unsignedInteger('eligible_quota');
            $table->unsignedInteger('reserve_quota');
            $table->timestamp('published_at');
            $table->string('eligible_wa_url')->nullable();
            $table->string('reserve_wa_url')->nullable();
            $table->timestamps();
        });

        Schema::create('eligible_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('eligible_announcement_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('rank')->nullable();
            $table->decimal('average', 6, 2)->nullable();
            $table->string('category');
            $table->string('response')->nullable();
            $table->timestamps();
            $table->unique(['eligible_announcement_id', 'student_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('eligible_results');
        Schema::dropIfExists('eligible_announcements');
    }
};
