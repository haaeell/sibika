<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_achievements', function (Blueprint $table) {
            $table->string('type', 30)->nullable()->change();
            $table->string('name', 150)->nullable()->change();
            $table->string('level', 30)->nullable()->change();
            $table->unsignedSmallInteger('year')->nullable()->change();
        });

        Schema::table('student_organizations', function (Blueprint $table) {
            $table->string('name', 150)->nullable()->change();
            $table->string('position', 100)->nullable()->change();
            $table->string('level', 30)->nullable()->change();
            $table->unsignedSmallInteger('year')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('student_achievements', function (Blueprint $table) {
            $table->string('type', 30)->nullable(false)->change();
            $table->string('name', 150)->nullable(false)->change();
            $table->string('level', 30)->nullable(false)->change();
            $table->unsignedSmallInteger('year')->nullable(false)->change();
        });

        Schema::table('student_organizations', function (Blueprint $table) {
            $table->string('name', 150)->nullable(false)->change();
            $table->string('position', 100)->nullable(false)->change();
            $table->string('level', 30)->nullable(false)->change();
            $table->unsignedSmallInteger('year')->nullable(false)->change();
        });
    }
};
