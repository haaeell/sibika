<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('score_subject_settings', function (Blueprint $table) {
            $table->dropForeign(['major_id']);
            $table->foreign('major_id')->references('id')->on('majors')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('score_subject_settings', function (Blueprint $table) {
            $table->dropForeign(['major_id']);
            $table->foreign('major_id')->references('id')->on('majors')->restrictOnDelete();
        });
    }
};
