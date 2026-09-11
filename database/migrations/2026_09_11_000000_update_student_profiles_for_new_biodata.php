<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_profiles', function (Blueprint $table) {
            // Add new biodata fields
            $table->unsignedSmallInteger('height_cm')->nullable()->after('phone');
            $table->unsignedSmallInteger('weight_kg')->nullable()->after('height_cm');
            $table->text('medical_history')->nullable()->after('weight_kg');
            $table->string('university_choice_1', 150)->nullable()->after('medical_history');
            $table->string('university_choice_2', 150)->nullable()->after('university_choice_1');
            $table->text('grade_11_preparation')->nullable()->after('university_choice_2');
            $table->text('career_concern')->nullable()->after('grade_11_preparation');
            $table->text('school_achievements')->nullable()->after('career_concern');
            $table->text('organization_participation')->nullable()->after('school_achievements');
            $table->text('self_improvement_notes')->nullable()->after('organization_participation');
            $table->string('mcu_status', 50)->nullable()->after('self_improvement_notes');
        });

        Schema::table('student_profiles', function (Blueprint $table) {
            // Drop legacy columns not in new biodata spec
            // Keep: gender, birth_place, birth_date, phone, province, city, district, village, postal_code, address
            if (Schema::hasColumn('student_profiles', 'nickname')) {
                $table->dropColumn('nickname');
            }
            if (Schema::hasColumn('student_profiles', 'email')) {
                $table->dropColumn('email');
            }
            if (Schema::hasColumn('student_profiles', 'previous_school')) {
                $table->dropColumn('previous_school');
            }
            if (Schema::hasColumn('student_profiles', 'previous_school_address')) {
                $table->dropColumn('previous_school_address');
            }
            if (Schema::hasColumn('student_profiles', 'graduation_year')) {
                $table->dropColumn('graduation_year');
            }
            if (Schema::hasColumn('student_profiles', 'academic_notes')) {
                $table->dropColumn('academic_notes');
            }
        });
    }

    public function down(): void
    {
        Schema::table('student_profiles', function (Blueprint $table) {
            $table->string('nickname', 50)->nullable();
            $table->string('email')->nullable();
            $table->string('previous_school', 150)->nullable();
            $table->text('previous_school_address')->nullable();
            $table->unsignedSmallInteger('graduation_year')->nullable();
            $table->text('academic_notes')->nullable();
        });

        Schema::table('student_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'height_cm',
                'weight_kg',
                'medical_history',
                'university_choice_1',
                'university_choice_2',
                'grade_11_preparation',
                'career_concern',
                'school_achievements',
                'organization_participation',
                'self_improvement_notes',
                'mcu_status',
            ]);
        });
    }
};
