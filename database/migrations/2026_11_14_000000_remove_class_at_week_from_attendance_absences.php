<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('attendance_absences', 'class_at_week')) {
            return;
        }

        Schema::table('attendance_absences', function (Blueprint $table) {
            $table->dropColumn('class_at_week');
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('attendance_absences', 'class_at_week')) {
            return;
        }

        Schema::table('attendance_absences', function (Blueprint $table) {
            $table->string('class_at_week', 100)->nullable()->after('student_id');
        });
    }
};
