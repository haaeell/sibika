<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_profiles', function (Blueprint $table) {
            $table->unsignedSmallInteger('mcu_count')->nullable()->after('mcu_status');
            $table->date('mcu_last_date')->nullable()->after('mcu_count');
            $table->string('parent_father_name', 100)->nullable()->after('mcu_last_date');
            $table->string('parent_father_occupation', 100)->nullable()->after('parent_father_name');
            $table->string('parent_mother_name', 100)->nullable()->after('parent_father_occupation');
            $table->string('parent_mother_occupation', 100)->nullable()->after('parent_mother_name');
            $table->string('parent_phone', 30)->nullable()->after('parent_mother_occupation');
            $table->text('parent_address')->nullable()->after('parent_phone');
        });
    }

    public function down(): void
    {
        Schema::table('student_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'mcu_count', 'mcu_last_date', 'parent_father_name', 'parent_father_occupation',
                'parent_mother_name', 'parent_mother_occupation', 'parent_phone', 'parent_address',
            ]);
        });
    }
};
