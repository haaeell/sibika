<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_profiles', function (Blueprint $table) {
            $table->string('parent_father_income_range', 20)->nullable()->after('parent_father_occupation');
            $table->string('parent_mother_income_range', 20)->nullable()->after('parent_mother_occupation');
            $table->dropColumn('parent_income_range');
        });
    }

    public function down(): void
    {
        Schema::table('student_profiles', function (Blueprint $table) {
            $table->string('parent_income_range', 20)->nullable()->after('parent_mother_occupation');
            $table->dropColumn(['parent_father_income_range', 'parent_mother_income_range']);
        });
    }
};
