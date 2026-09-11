<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\University;
use App\Models\User;
use App\Services\StudentProgressService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
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

    public function test_student_can_update_biodata(): void
    {
        $user = $this->studentUser();
        $student = Student::create(['nis' => 'S-002', 'name' => 'Siswa Test', 'user_id' => $user->id]);

        $this->actingAs($user)
            ->put(route('siswa.biodata.update'), [
                'gender' => 'female',
                'birth_place' => 'Bandung',
                'birth_date' => '2008-05-10',
                'phone' => '08123456789',
                'province' => 'Jawa Barat',
                'city' => 'Bandung',
                'district' => 'Coblong',
                'village' => 'Dago',
                'postal_code' => '40135',
                'address' => 'Jalan Test',
                'height_cm' => 165,
                'weight_kg' => 55,
                'medical_history' => '-',
                ...$this->universityChoices(),
                'grade_11_preparation' => 'Sudah belajar rutin',
                'career_concern' => 'Takut tidak lolos',
                'school_achievements' => '-',
                'organization_participation' => 'OSIS',
                'self_improvement_notes' => 'Perlu tingkatkan disiplin',
                'mcu_status' => 'belum',
            ])
            ->assertRedirect(route('siswa.biodata.index'));

        $this->assertDatabaseHas('student_profiles', ['student_id' => $student->id, 'phone' => '08123456789', 'height_cm' => 165, 'mcu_status' => 'belum']);
        $this->assertDatabaseHas('student_profiles', ['student_id' => $student->id, 'university_choice_1_id' => $this->universityChoices()['university_choice_1_id']]);
    }

    public function test_progress_service_reports_empty_biodata_as_zero(): void
    {
        $user = $this->studentUser();
        $student = Student::create(['nis' => 'S-003', 'name' => 'Siswa Test', 'user_id' => $user->id]);

        $this->assertSame(0, app(StudentProgressService::class)->calculate($student)['percentage']);
    }

    public function test_photo_and_certificates_do_not_prevent_biodata_from_reaching_one_hundred_percent(): void
    {
        $user = $this->studentUser();
        $student = Student::create(['nis' => 'S-005', 'name' => 'Siswa Lengkap', 'user_id' => $user->id]);
        $student->profile()->create([
            'gender' => 'male',
            'birth_place' => 'Bandung',
            'birth_date' => '2008-01-01',
            'phone' => '08123456789',
            'province' => 'Jawa Barat',
            'city' => 'Bandung',
            'district' => 'Coblong',
            'village' => 'Dago',
            'postal_code' => '40135',
            'address' => 'Jalan Test',
            'height_cm' => 170,
            'weight_kg' => 60,
            'medical_history' => '-',
            ...$this->universityChoices(),
            'grade_11_preparation' => 'Belajar rutin',
            'career_concern' => 'Persaingan masuk kampus',
            'school_achievements' => '-',
            'organization_participation' => '-',
            'self_improvement_notes' => 'Meningkatkan disiplin',
            'mcu_status' => 'belum',
        ]);

        $progress = app(StudentProgressService::class)->calculate($student->fresh());

        $this->assertSame(100, $progress['percentage']);
        $this->assertSame(21, $progress['completed']);
        $this->assertSame(21, $progress['total']);
    }

    public function test_all_biodata_fields_are_required(): void
    {
        $user = $this->studentUser();
        Student::create(['nis' => 'S-007', 'name' => 'Siswa Wajib', 'user_id' => $user->id]);

        $this->actingAs($user)
            ->put(route('siswa.biodata.update'), [])
            ->assertSessionHasErrors([
                'gender', 'birth_place', 'birth_date', 'phone', 'province', 'city', 'district', 'village',
                'postal_code', 'address', 'height_cm', 'weight_kg', 'medical_history', 'university_choice_1_id',
                'university_choice_2_id', 'grade_11_preparation', 'career_concern', 'school_achievements',
                'organization_participation', 'self_improvement_notes', 'mcu_status',
            ]);
    }

    public function test_student_can_upload_multiple_achievement_certificates(): void
    {
        Storage::fake('local');
        $user = $this->studentUser();
        $student = Student::create(['nis' => 'S-006', 'name' => 'Siswa Prestasi', 'user_id' => $user->id]);

        $this->actingAs($user)
            ->post(route('siswa.biodata.documents.store'), [
                'document_type' => 'Juara Olimpiade Matematika 2026',
                'documents' => [
                    UploadedFile::fake()->create('sertifikat-juara.pdf', 200, 'application/pdf'),
                    UploadedFile::fake()->image('piagam-finalis.png'),
                ],
            ])
            ->assertRedirect();

        $this->assertDatabaseCount('student_documents', 2);
        $this->assertDatabaseHas('student_documents', [
            'student_id' => $student->id,
            'document_type' => 'Juara Olimpiade Matematika 2026',
            'original_name' => 'sertifikat-juara.pdf',
        ]);
        $this->assertDatabaseHas('student_documents', [
            'student_id' => $student->id,
            'document_type' => 'Juara Olimpiade Matematika 2026',
            'original_name' => 'piagam-finalis.png',
        ]);
    }

    public function test_student_can_replace_profile_photo_and_see_the_latest_file(): void
    {
        Storage::fake('local');
        $user = $this->studentUser();
        $student = Student::create(['nis' => 'S-008', 'name' => 'Siswa Foto', 'user_id' => $user->id]);

        $this->actingAs($user)
            ->post(route('siswa.biodata.photo.store'), [
                'photo' => UploadedFile::fake()->image('foto-lama.jpg'),
            ])
            ->assertRedirect();

        $oldPath = $student->fresh()->profile->photo_path;
        Storage::disk('local')->assertExists($oldPath);

        $this->actingAs($user)
            ->post(route('siswa.biodata.photo.store'), [
                'photo' => UploadedFile::fake()->image('foto-baru.png'),
            ])
            ->assertRedirect();

        $newPath = $student->fresh()->profile->photo_path;

        $this->assertNotSame($oldPath, $newPath);
        Storage::disk('local')->assertMissing($oldPath);
        Storage::disk('local')->assertExists($newPath);
        $response = $this->actingAs($user)
            ->get(route('siswa.biodata.photo.show'))
            ->assertOk();

        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
        $this->assertStringContainsString('private', $response->headers->get('Cache-Control'));
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
                'gender' => 'male',
                'birth_place' => 'Bandung',
                'birth_date' => '2008-01-01',
                'phone' => '08123456789',
                'province' => 'Jawa Barat',
                'city' => 'Bandung',
                'district' => 'Coblong',
                'village' => 'Dago',
                'postal_code' => '40135',
                'address' => 'Jalan Test',
                'height_cm' => 170,
                'weight_kg' => 60,
                'medical_history' => '-',
                ...$this->universityChoices(),
                'grade_11_preparation' => 'Belajar rutin',
                'career_concern' => 'Persaingan masuk kampus',
                'school_achievements' => '-',
                'organization_participation' => '-',
                'self_improvement_notes' => 'Meningkatkan disiplin',
                'mcu_status' => 'sudah',
            ])
            ->assertRedirect(route('bk.students.biodata.show', $student));

        $this->assertDatabaseHas('student_profiles', ['student_id' => $student->id, 'height_cm' => 170]);
    }

    private function studentUser(): User
    {
        $role = Role::firstOrCreate(['name' => 'siswa', 'guard_name' => 'web']);
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    private function universityChoices(): array
    {
        $first = University::firstOrCreate(
            ['name' => 'Universitas Indonesia'],
            ['short_name' => 'UI', 'type' => 'negeri', 'is_active' => true]
        );
        $second = University::firstOrCreate(
            ['name' => 'Institut Teknologi Bandung'],
            ['short_name' => 'ITB', 'type' => 'negeri', 'is_active' => true]
        );

        return [
            'university_choice_1_id' => $first->id,
            'university_choice_2_id' => $second->id,
        ];
    }
}
