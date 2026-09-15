<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_profiles', function (Blueprint $table) {
            $table->string('university_major_choice_1', 150)->nullable()->after('university_choice_1_id');
            $table->string('university_major_choice_2', 150)->nullable()->after('university_choice_2_id');
            $table->string('university_major_choice_3', 150)->nullable()->after('university_choice_3_id');
        });
    }

    public function down(): void
    {
        Schema::table('student_profiles', function (Blueprint $table) {
            $table->dropColumn(['university_major_choice_1', 'university_major_choice_2', 'university_major_choice_3']);
        });
    }
};
