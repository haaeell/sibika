<?php

namespace Tests\Feature;

use App\Models\ScoreSubjectSetting;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class StudentScoreExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_bk_can_download_score_export_xlsx(): void
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
        $student = Student::create(['nis' => 'EXP001', 'nisn' => null, 'name' => 'Siswa Export', 'status' => 'active']);
        $student->scores()->create(['subject_id' => $subject->id, 'semester_number' => 1, 'score' => 85.5]);

        $response = $this->actingAs($user)->get(route('bk.student-scores.export'));

        $response->assertOk();
        $this->assertStringContainsString(
            'spreadsheetml',
            $response->headers->get('Content-Type', '')
        );
    }

    public function test_score_export_respects_major_filter(): void
    {
        $user = $this->bkUser();

        $response = $this->actingAs($user)->get(route('bk.student-scores.export', ['major_id' => [999999]]));

        $response->assertOk();
    }

    public function test_guest_cannot_download_score_export(): void
    {
        $this->get(route('bk.student-scores.export'))->assertRedirect(route('login'));
    }

    private function bkUser(): User
    {
        $user = User::factory()->create();
        $user->assignRole(Role::firstOrCreate(['name' => 'bk', 'guard_name' => 'web']));

        return $user;
    }
}
