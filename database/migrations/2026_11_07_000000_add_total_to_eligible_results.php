<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('eligible_results', function (Blueprint $table) {
            $table->decimal('total_score', 10, 2)->nullable()->after('average');
        });
    }

    public function down(): void
    {
        Schema::table('eligible_results', function (Blueprint $table) {
            $table->dropColumn('total_score');
        });
    }
};
