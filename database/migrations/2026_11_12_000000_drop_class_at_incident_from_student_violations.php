<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_violations', fn (Blueprint $table) => $table->dropColumn('class_at_incident'));
    }

    public function down(): void
    {
        Schema::table('student_violations', fn (Blueprint $table) => $table->string('class_at_incident', 100)->nullable());
    }
};
