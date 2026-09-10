<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_scores', function (Blueprint $table) {
            $table->dropForeign(['verified_by']);
            $table->dropColumn(['status', 'submitted_at', 'verified_by', 'verified_at', 'verification_note']);
        });
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
