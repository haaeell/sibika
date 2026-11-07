<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('graduation_announcements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cohort_id')->unique()->constrained()->cascadeOnDelete();
            $table->dateTime('published_at');
            $table->boolean('is_active')->default(false);
            $table->date('meeting_date')->nullable();
            $table->string('issued_place')->nullable();
            $table->string('principal_name')->nullable();
            $table->string('principal_nip')->nullable();
            $table->string('signature_path')->nullable();
            $table->string('stamp_path')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('graduation_announcements');
    }
};
