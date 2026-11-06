<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('eligible_results', function (Blueprint $table) {
            $table->unsignedInteger('initial_rank')->nullable()->after('rank');
        });

        // Hasil lama belum menyimpan urutan awal. Susun dari snapshot nilai saat pengumuman dibuat.
        $results = DB::table('eligible_results')
            ->join('students', 'students.id', '=', 'eligible_results.student_id')
            ->select('eligible_results.id', 'eligible_results.eligible_announcement_id', 'eligible_results.average', 'students.name', 'students.nis', 'students.id as student_id')
            ->get()
            ->groupBy('eligible_announcement_id');

        foreach ($results as $announcementResults) {
            $ranked = $announcementResults->whereNotNull('average')->sort(function ($a, $b): int {
                return ((float) $b->average <=> (float) $a->average)
                    ?: strcasecmp($a->name, $b->name)
                    ?: strcmp($a->nis, $b->nis)
                    ?: ($a->student_id <=> $b->student_id);
            })->values();

            foreach ($ranked as $index => $result) {
                DB::table('eligible_results')->where('id', $result->id)->update(['initial_rank' => $index + 1]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('eligible_results', function (Blueprint $table) {
            $table->dropColumn('initial_rank');
        });
    }
};
