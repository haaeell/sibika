<?php

namespace Tests\Feature;

use App\Models\Major;
use App\Models\ScoreSubjectSetting;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class StudentAndMajorDeletionTest extends TestCase
{
    use RefreshDatabase;

    public function test_bk_can_delete_all_students_and_their_accounts(): void
    {
        $firstUser = User::factory()->create();
        $secondUser = User::factory()->create();
        Student::create(['nis' => 'S-001', 'name' => 'Siswa Satu', 'user_id' => $firstUser->id]);
        Student::create(['nis' => 'S-002', 'name' => 'Siswa Dua', 'user_id' => $secondUser->id]);

        $this->actingAs($this->bkUser())
            ->delete(route('bk.students.destroy-all'))
            ->assertRedirect(route('bk.students.index'));

        $this->assertDatabaseCount('students', 0);
        $this->assertDatabaseMissing('users', ['id' => $firstUser->id]);
        $this->assertDatabaseMissing('users', ['id' => $secondUser->id]);
    }

    public function test_deleting_major_cascades_its_score_settings(): void
    {
        $major = Major::create(['code' => 'IPA', 'name' => 'Ilmu Pengetahuan Alam', 'is_active' => true]);
        $subject = Subject::create(['code' => 'MAT', 'name' => 'Matematika', 'is_active' => true]);
        $setting = ScoreSubjectSetting::create(['subject_id' => $subject->id, 'major_id' => $major->id, 'semester_number' => 1]);

        $this->actingAs($this->bkUser())
            ->delete(route('bk.majors.destroy', $major))
            ->assertRedirect(route('bk.majors.index'));

        $this->assertDatabaseMissing('majors', ['id' => $major->id]);
        $this->assertDatabaseMissing('score_subject_settings', ['id' => $setting->id]);
    }

    private function bkUser(): User
    {
        $user = User::factory()->create();
        $user->assignRole(Role::firstOrCreate(['name' => 'bk', 'guard_name' => 'web']));

        return $user;
    }
}
