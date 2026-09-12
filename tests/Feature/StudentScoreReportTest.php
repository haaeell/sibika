<?php

namespace Tests\Feature;

use App\Models\ScoreSubjectSetting;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class StudentScoreReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_bk_can_open_score_report(): void
    {
        $user = $this->bkUser();

        $subject = Subject::create(['code' => 'MAT', 'name' => 'Matematika', 'category' => 'general', 'is_active' => true]);
        ScoreSubjectSetting::create([
            'subject_id' => $subject->id,
            'major_id' => null,
            'semester_number' => 1,
            'is_required' => true,
            'include_in_average' => true,
            'is_active' => true,
        ]);
        $student = Student::create(['nis' => 'REP001', 'nisn' => null, 'name' => 'Siswa Laporan', 'status' => 'active']);
        $student->scores()->create(['subject_id' => $subject->id, 'semester_number' => 1, 'score' => 82]);

        $major = \App\Models\Major::create(['code' => 'IPA', 'name' => 'IPA', 'is_active' => true]);
        $year = \App\Models\AcademicYear::create(['name' => '2026 / 2027', 'start_year' => 2026, 'end_year' => 2027, 'semester' => 'ganjil', 'is_active' => true]);
        $class = \App\Models\SchoolClass::create(['name' => 'XI IPA 1', 'grade_level' => 'XI', 'major_id' => $major->id, 'academic_year_id' => $year->id]);
        $student->update(['class_id' => $class->id]);

        $this->actingAs($user)->get(route('bk.student-scores.report'))
            ->assertOk()
            ->assertSee('Laporan Nilai Siswa')
            ->assertSee('Siswa Laporan')
            ->assertSee('82.00');
    }

    public function test_score_report_filter_by_completeness(): void
    {
        $user = $this->bkUser();

        Student::create(['nis' => 'REP002', 'nisn' => null, 'name' => 'Siswa Kosong', 'status' => 'active']);

        $this->actingAs($user)->get(route('bk.student-scores.report', ['completeness' => 'incomplete']))
            ->assertOk()
            ->assertSee('Siswa Kosong');
    }

    public function test_guest_cannot_open_score_report(): void
    {
        $this->get(route('bk.student-scores.report'))->assertRedirect(route('login'));
    }

    private function bkUser(): User
    {
        $user = User::factory()->create();
        $user->assignRole(Role::firstOrCreate(['name' => 'bk', 'guard_name' => 'web']));

        return $user;
    }
}
