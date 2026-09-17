<?php

namespace Tests\Feature;

use App\Models\ScoreSubjectSetting;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use App\Services\StudentScoreService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ScoreQueryOptimizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_data_endpoint_runs_few_queries(): void
    {
        $bk = $this->bkUser();
        $this->seedScores(30);

        DB::enableQueryLog();
        $response = $this->actingAs($bk)->getJson(route('bk.student-scores.data'));
        $count = count(DB::getQueryLog());

        $response->assertOk();
        $this->assertSame(30, $response->json('recordsTotal'));
        $this->assertLessThan(40, $count, "Terlalu banyak query: {$count}");
    }

    public function test_report_runs_few_queries(): void
    {
        $bk = $this->bkUser();
        $this->seedScores(30);

        DB::enableQueryLog();
        $this->actingAs($bk)->get(route('bk.student-scores.report'))->assertOk();
        $count = count(DB::getQueryLog());

        $this->assertLessThan(60, $count, "Terlalu banyak query: {$count}");
    }

    public function test_batch_matches_single_student_results(): void
    {
        $this->seedScores(10);

        $fresh = app(StudentScoreService::class);
        $students = Student::with(['schoolClass.major', 'scores.subject'])->orderBy('id')->get();
        $averages = $fresh->averagesForMany($students);
        $ranks = $fresh->ranksForMany($students, $averages);

        foreach ($students as $student) {
            // Service baru per siswa agar tanpa cache antar-siswa.
            $solo = app(StudentScoreService::class);
            $summary = $solo->overallSummary($student->fresh(['schoolClass.major', 'scores.subject']));

            $this->assertSame($summary['average'], $averages[$student->id]['overall'], "Rata-rata beda: {$student->nis}");
            $this->assertSame($summary['class_total'], $ranks[$student->id]['class_total']);
            $this->assertSame($summary['major_total'], $ranks[$student->id]['major_total']);

            foreach (range(1, 5) as $semester) {
                $this->assertSame(
                    $solo->semesterAverage($student, $semester),
                    $averages[$student->id]['semesters'][$semester],
                    "Smt {$semester} beda: {$student->nis}"
                );
            }
        }

        // Ranking dalam himpunan penuh (tanpa filter) harus identik.
        $solo = app(StudentScoreService::class);
        foreach ($students->take(3) as $student) {
            $summary = $solo->overallSummary($student->fresh(['schoolClass.major', 'scores.subject']));
            $this->assertSame($summary['class_rank'], $ranks[$student->id]['class_rank'], "Rank kelas beda: {$student->nis}");
            $this->assertSame($summary['major_rank'], $ranks[$student->id]['major_rank'], "Rank jurusan beda: {$student->nis}");
            $this->assertSame($summary['cohort_rank'], $ranks[$student->id]['cohort_rank'], "Rank angkatan beda: {$student->nis}");
        }
    }

    private function seedScores(int $total): void
    {
        Role::firstOrCreate(['name' => 'siswa', 'guard_name' => 'web']);

        $subjects = collect();
        foreach (['MAT', 'BIN', 'FIS'] as $code) {
            $subject = Subject::create(['code' => $code.$total, 'name' => 'Mapel '.$code, 'category' => 'general', 'is_active' => true]);
            ScoreSubjectSetting::create([
                'subject_id' => $subject->id, 'major_id' => null, 'semester_number' => 1,
                'is_required' => true, 'include_in_average' => true, 'is_active' => true,
            ]);
            $subjects->push($subject);
        }

        foreach (range(1, $total) as $i) {
            $student = Student::create([
                'nis' => 'OPT'.time().$i, 'nisn' => null, 'name' => 'Siswa '.$i, 'status' => 'active',
            ]);
            foreach ($subjects as $subject) {
                $student->scores()->create([
                    'subject_id' => $subject->id, 'semester_number' => 1, 'score' => 70 + ($i % 30),
                ]);
            }
        }
    }

    private function bkUser(): User
    {
        $user = User::factory()->create();
        $user->assignRole(Role::firstOrCreate(['name' => 'bk', 'guard_name' => 'web']));

        return $user;
    }
}
