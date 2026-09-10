<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('classes', function (Blueprint $table) {
            $table->foreignId('major_id')->nullable()->after('grade_level')->constrained('majors')->nullOnDelete();
        });

        DB::table('classes')
            ->select('major')
            ->whereNotNull('major')
            ->where('major', '!=', '')
            ->distinct()
            ->get()
            ->each(function ($class) {
                $majorId = DB::table('majors')->where('name', $class->major)->value('id');

                if (! $majorId) {
                    $majorId = DB::table('majors')->insertGetId([
                        'code' => strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $class->major), 0, 20)),
                        'name' => $class->major,
                        'is_active' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                DB::table('classes')->where('major', $class->major)->update(['major_id' => $majorId]);
            });
    }

    public function down(): void
    {
        Schema::table('classes', function (Blueprint $table) {
            $table->dropForeign(['major_id']);
            $table->dropColumn('major_id');
        });
    }
};
