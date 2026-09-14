<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('login_settings', function (Blueprint $table) {
            $table->id();
            $table->string('app_name', 100);
            $table->string('school_name', 150);
            $table->string('seo_description', 255);
            $table->string('help_text', 100);
            $table->string('hero_title', 150);
            $table->text('hero_description');
            $table->string('footer_name', 150);
            $table->string('footer_tagline', 150);
            $table->string('welcome_title', 100);
            $table->string('welcome_subtitle', 150);
            $table->string('copyright_text', 150);
            $table->string('logo_path')->nullable();
            $table->string('hero_image_path')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('login_settings');
    }
};
