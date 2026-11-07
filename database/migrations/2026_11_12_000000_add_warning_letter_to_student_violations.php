<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_violations', function (Blueprint $table) {
            $table->string('warning_letter_path')->nullable()->after('sanction');
            $table->string('warning_letter_name')->nullable()->after('warning_letter_path');
        });
    }

    public function down(): void
    {
        Schema::table('student_violations', function (Blueprint $table) {
            $table->dropColumn(['warning_letter_path', 'warning_letter_name']);
        });
    }
};
