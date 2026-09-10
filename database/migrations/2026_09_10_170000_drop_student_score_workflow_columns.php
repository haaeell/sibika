<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'sqlite') {
            try {
                DB::statement('PRAGMA foreign_keys=OFF');
            } catch (\Throwable $e) {
            }
            try {
                DB::statement('DROP INDEX IF EXISTS "student_scores_status_index"');
            } catch (\Throwable $e) {
            }
            try {
                Schema::table('student_scores', function (Blueprint $table) {
                    $table->dropForeign(['verified_by']);
                });
            } catch (\Throwable $e) {
            }
        } else {
            Schema::table('student_scores', function (Blueprint $table) {
                try {
                    $table->dropForeign(['verified_by']);
                } catch (\Throwable $e) {
                }
                try {
                    $table->dropIndex('student_scores_status_index');
                } catch (\Throwable $e) {
                }
            });
        }

        try {
            Schema::table('student_scores', function (Blueprint $table) {
                $columns = array_filter(['status', 'submitted_at', 'verified_by', 'verified_at', 'verification_note'], fn ($col) => Schema::hasColumn('student_scores', $col));
                if ($columns) {
                    $table->dropColumn($columns);
                }
            });
        } finally {
            if ($driver === 'sqlite') {
                try {
                    DB::statement('PRAGMA foreign_keys=ON');
                } catch (\Throwable $e) {
                }
            }
        }
    }

    public function down(): void
    {
        Schema::table('student_scores', function (Blueprint $table) {
            $table->enum('status', ['draft', 'submitted', 'verified', 'rejected'])->default('draft')->index()->after('score');
            $table->timestamp('submitted_at')->nullable()->after('status');
            $table->foreignId('verified_by')->nullable()->after('submitted_at')->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable()->after('verified_by');
            $table->text('verification_note')->nullable()->after('verified_at');
        });
    }
};
