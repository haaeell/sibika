<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\User;
use App\Services\StudentProgressService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class StudentBiodataTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_view_own_biodata(): void
    {
        $user = $this->studentUser();
        Student::create(['nis' => 'S-001', 'name' => 'Siswa Test', 'user_id' => $user->id]);

        $this->actingAs($user)
            ->get(route('siswa.biodata.index'))
            ->assertOk()
            ->assertSee('Biodata Saya');
    }

    public function test_student_can_update_biodata_and_parents(): void
    {
        $user = $this->studentUser();
        $student = Student::create(['nis' => 'S-002', 'name' => 'Siswa Test', 'user_id' => $user->id]);

        $this->actingAs($user)
            ->put(route('siswa.biodata.update'), [
                'nickname' => 'Test',
                'gender' => 'female',
                'birth_place' => 'Bandung',
                'birth_date' => '2008-05-10',
                'phone' => '08123456789',
                'email' => 'siswa@example.test',
                'province' => 'Jawa Barat',
                'city' => 'Bandung',
                'district' => 'Coblong',
                'village' => 'Dago',
                'postal_code' => '40135',
                'address' => 'Jalan Test',
                'previous_school' => 'SMP Test',
                'graduation_year' => 2024,
                'father' => ['name' => 'Ayah Test', 'phone' => '0811111111'],
                'mother' => ['name' => 'Ibu Test', 'phone' => '0822222222'],
            ])
            ->assertRedirect(route('siswa.biodata.index'));

        $this->assertDatabaseHas('student_profiles', ['student_id' => $student->id, 'nickname' => 'Test']);
        $this->assertDatabaseHas('student_parents', ['student_id' => $student->id, 'parent_type' => 'father', 'name' => 'Ayah Test']);
    }

    public function test_progress_service_reports_empty_biodata_as_zero(): void
    {
        $user = $this->studentUser();
        $student = Student::create(['nis' => 'S-003', 'name' => 'Siswa Test', 'user_id' => $user->id]);

        $this->assertSame(0, app(StudentProgressService::class)->calculate($student)['percentage']);
    }

    public function test_bk_can_edit_student_biodata_without_delete_action(): void
    {
        $user = User::factory()->create();
        $user->assignRole(Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']));
        $student = Student::create(['nis' => 'S-004', 'name' => 'Siswa BK']);

        $this->actingAs($user)
            ->get(route('bk.students.biodata.edit', $student))
            ->assertOk()
            ->assertSee('Edit Biodata Siswa')
            ->assertDontSee('Hapus Biodata');

        $this->actingAs($user)
            ->put(route('bk.students.biodata.update', $student), [
                'nickname' => 'Diperbarui',
                'gender' => 'male',
                'birth_place' => 'Bandung',
                'birth_date' => '2008-01-01',
                'phone' => '08123456789',
                'email' => 'bk-student@example.test',
                'province' => 'Jawa Barat',
                'city' => 'Bandung',
                'district' => 'Coblong',
                'village' => 'Dago',
                'postal_code' => '40135',
                'address' => 'Jalan Test',
                'previous_school' => 'SMP Test',
                'graduation_year' => 2024,
                'father' => ['name' => 'Ayah Test'],
                'mother' => ['name' => 'Ibu Test'],
            ])
            ->assertRedirect(route('bk.students.biodata.show', $student));

        $this->assertDatabaseHas('student_profiles', ['student_id' => $student->id, 'nickname' => 'Diperbarui']);
    }

    private function studentUser(): User
    {
        $role = Role::firstOrCreate(['name' => 'siswa', 'guard_name' => 'web']);
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }
}
