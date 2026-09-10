<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('teachers')
            ->select(['id', 'subjects'])
            ->whereNotNull('subjects')
            ->where('subjects', '!=', '')
            ->get()
            ->each(function ($teacher): void {
                foreach (array_filter(array_map('trim', explode(',', $teacher->subjects))) as $index => $subjectName) {
                    $subject = DB::table('subjects')->where('name', $subjectName)->first();
                    $code = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $subjectName), 0, 24));

                    if (! $subject) {
                        $existingCode = DB::table('subjects')->where('code', $code)->exists();
                        $code = $existingCode ? 'LEGACY'.($teacher->id).$index : $code;
                        $subjectId = DB::table('subjects')->insertGetId([
                            'code' => $code,
                            'name' => $subjectName,
                            'category' => 'general',
                            'is_active' => true,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    } else {
                        $subjectId = $subject->id;
                    }

                    DB::table('subject_teacher')->insertOrIgnore([
                        'subject_id' => $subjectId,
                        'teacher_id' => $teacher->id,
                    ]);
                }
            });

        Schema::table('teachers', function (Blueprint $table): void {
            $table->dropColumn('subjects');
        });

        Schema::table('classes', function (Blueprint $table): void {
            $table->dropColumn('major');
        });
    }

    public function down(): void
    {
        Schema::table('classes', function (Blueprint $table): void {
            $table->string('major', 50)->nullable();
        });

        Schema::table('teachers', function (Blueprint $table): void {
            $table->string('subjects')->nullable();
        });
    }
};
