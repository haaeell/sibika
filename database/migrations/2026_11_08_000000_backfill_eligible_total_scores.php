<?php

use App\Models\EligibleAnnouncement;
use App\Services\EligibleRankingService;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $ranking = app(EligibleRankingService::class);

        foreach (EligibleAnnouncement::with('results')->get() as $announcement) {
            $results = $announcement->results->keyBy('student_id');
            foreach ($ranking->forCohort($announcement->cohort_id) as $row) {
                $saved = $results->get($row['student']->id);
                if ($saved && $saved->total_score === null && $row['average'] !== null
                    && (float) $saved->average === (float) $row['average']) {
                    $saved->update(['total_score' => $row['total_score']]);
                }
            }
        }
    }

    public function down(): void
    {
        // ponytail: nilai hasil yang sudah terisi tidak dihapus saat rollback; snapshot dipertahankan.
    }
};
