<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('login_settings', function (Blueprint $table) {
            $table->string('help_whatsapp_number', 30)->nullable()->after('help_text');
        });
    }

    public function down(): void
    {
        Schema::table('login_settings', function (Blueprint $table) {
            $table->dropColumn('help_whatsapp_number');
        });
    }
};
