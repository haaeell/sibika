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

class RoleDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dashboard_shows_system_summary(): void
    {
        $user = $this->userWithRole('super_admin');
        Student::create(['nis' => 'D-001', 'name' => 'Siswa Admin']);

        $this->actingAs($user)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Dashboard Admin')
            ->assertSee('Total Siswa');
    }

    public function test_bk_dashboard_shows_biodata_summary(): void
    {
        $user = $this->userWithRole('bk');
        Student::create(['nis' => 'D-002', 'name' => 'Siswa BK', 'status' => 'active']);

        $this->actingAs($user)
            ->get(route('bk.dashboard'))
            ->assertOk()
            ->assertSee('Dashboard BK')
            ->assertSee('Siswa Aktif');
    }

    public function test_wali_kelas_dashboard_uses_homeroom_teacher_account(): void
    {
        $user = $this->userWithRole('wali_kelas');
        $teacher = Teacher::create(['code' => 'WK-001', 'name' => 'Wali Test', 'email' => 'wali.test@example.test', 'status' => 'active', 'user_id' => $user->id]);
        $year = AcademicYear::create(['name' => '2026 / 2027', 'start_year' => 2026, 'end_year' => 2027, 'semester' => 'ganjil', 'is_active' => true]);
        $class = SchoolClass::create(['name' => 'XI TEST', 'grade_level' => 'XI', 'academic_year_id' => $year->id, 'homeroom_teacher_id' => $teacher->id]);
        Student::create(['nis' => 'D-003', 'name' => 'Siswa Wali', 'class_id' => $class->id, 'status' => 'active']);

        $this->actingAs($user)
            ->get(route('wali-kelas.dashboard'))
            ->assertOk()
            ->assertSee('Dashboard Wali Kelas')
            ->assertSee('XI TEST')
            ->assertSee('Siswa Wali');
    }

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole(Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']));

        return $user;
    }
}
