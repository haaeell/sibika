<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class WaliKelasMonitoringTest extends TestCase
{
    use RefreshDatabase;

    public function test_wali_kelas_can_only_monitor_own_class(): void
    {
        $user = User::factory()->create();
        $user->assignRole(Role::firstOrCreate(['name' => 'wali_kelas', 'guard_name' => 'web']));
        $teacher = Teacher::create(['code' => 'WK-002', 'name' => 'Wali Test', 'email' => 'wali.monitor@example.test', 'status' => 'active', 'user_id' => $user->id]);
        $year = AcademicYear::create(['name' => '2026 / 2027', 'start_year' => 2026, 'end_year' => 2027, 'semester' => 'ganjil', 'is_active' => true]);
        $ownClass = SchoolClass::create(['name' => 'XI WALI', 'grade_level' => 'XI', 'academic_year_id' => $year->id, 'homeroom_teacher_id' => $teacher->id]);
        $otherClass = SchoolClass::create(['name' => 'XI LAIN', 'grade_level' => 'XI', 'academic_year_id' => $year->id]);
        $ownStudent = Student::create(['nis' => 'WK-001', 'name' => 'Siswa Wali', 'class_id' => $ownClass->id, 'status' => 'active']);
        $otherStudent = Student::create(['nis' => 'WK-002', 'name' => 'Siswa Lain', 'class_id' => $otherClass->id, 'status' => 'active']);

        $this->actingAs($user)->get(route('wali-kelas.biodata.index'))
            ->assertOk()->assertSee('Biodata')->assertSee('XI WALI')->assertDontSee('XI LAIN')->assertDontSee('Export');
        $this->actingAs($user)->get(route('wali-kelas.scores.index'))
            ->assertOk()->assertSee('Data Nilai')->assertDontSee('Ranking Kelas')->assertDontSee('Ranking Jurusan')->assertDontSee('Ranking Angkatan')->assertDontSee('Export Excel');
        $this->actingAs($user)->get(route('wali-kelas.scores.report'))
            ->assertOk()->assertDontSee('Sorotan untuk BK')->assertDontSee('Rank Kelas')->assertDontSee('Rank Jurusan')->assertDontSee('Rank Angkatan');
        $this->actingAs($user)->get(route('wali-kelas.biodata.data'))
            ->assertOk()->assertSee('Siswa Wali')->assertDontSee('Siswa Lain');
        $this->actingAs($user)->get(route('wali-kelas.biodata.show', $ownStudent))->assertOk()->assertDontSee('Edit Biodata');
        $this->actingAs($user)->get(route('wali-kelas.biodata.show', $otherStudent))->assertNotFound();
        $this->actingAs($user)->get(route('wali-kelas.scores.show', $otherStudent))->assertNotFound();
    }
}
