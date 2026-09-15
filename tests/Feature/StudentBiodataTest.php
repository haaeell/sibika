<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\TkaSubject;
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
            ->assertSee('Biodata Saya')
            ->assertSee('Apakah kamu memiliki prestasi akademik atau non akademik?')
            ->assertSee('Tes Kemampuan Akademik (TKA)')
            ->assertSee('Tambah TKA Lain')
            ->assertSee('Tambah Prestasi Lain')
            ->assertSee('Tambah Organisasi Lain');
    }

    public function test_student_dashboard_shows_active_shortcuts_without_soon_cards(): void
    {
        $user = $this->studentUser();
        Student::create(['nis' => 'S-009', 'name' => 'Siswa Dashboard', 'user_id' => $user->id]);

        $this->actingAs($user)
            ->get(route('siswa.dashboard'))
            ->assertOk()
            ->assertSee('Akses Cepat')
            ->assertSee('Biodata Saya')
            ->assertSee('Nilai Semester')
            ->assertDontSee('Segera Hadir')
            ->assertDontSee('Soon');
    }

    public function test_student_score_page_hides_average_and_rank_cards(): void
    {
        $user = $this->studentUser();
        Student::create(['nis' => 'S-010', 'name' => 'Siswa Nilai', 'user_id' => $user->id]);

        $this->actingAs($user)
            ->get(route('siswa.scores.index'))
            ->assertOk()
            ->assertSee('Nilai Semester')
            ->assertDontSee('Rata-rata Keseluruhan')
            ->assertDontSee('Ranking Kelas')
            ->assertDontSee('Ranking Jurusan');
    }

    public function test_student_can_update_biodata(): void
    {
        Storage::fake('local');
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
                'university_major_choice_1' => 'Teknik Informatika',
                ...$this->parentFields(),
                'organization_status' => 'ya',
                'organizations' => [['name' => 'OSIS', 'position' => 'Ketua', 'level' => 'sekolah', 'year' => 2026]],
                'self_improvement_notes' => 'Perlu tingkatkan disiplin',
                'mcu_status' => 'belum',
                'photo' => UploadedFile::fake()->image('foto.jpg'),
                'ijazah_smp' => UploadedFile::fake()->create('ijazah.pdf', 200, 'application/pdf'),
                'akte' => UploadedFile::fake()->create('akte.pdf', 200, 'application/pdf'),
                'kartu_keluarga' => UploadedFile::fake()->create('kk.pdf', 200, 'application/pdf'),
                'achievement_status' => 'ya',
                'achievements' => [['type' => 'akademik', 'name' => 'Juara Test', 'level' => 'kab_kota', 'year' => 2026, 'certificate' => UploadedFile::fake()->create('sertifikat.pdf', 200, 'application/pdf')]],
            ])
            ->assertRedirect(route('siswa.biodata.index'));

        $this->assertDatabaseHas('student_profiles', ['student_id' => $student->id, 'phone' => '08123456789', 'height_cm' => 165, 'mcu_status' => 'belum']);
        $this->assertDatabaseHas('student_profiles', ['student_id' => $student->id, 'university_choice_1_id' => $this->universityChoices()['university_choice_1_id']]);
        $this->assertDatabaseHas('student_profiles', ['student_id' => $student->id, 'university_major_choice_1' => 'Teknik Informatika']);
        $this->assertDatabaseHas('student_documents', ['student_id' => $student->id, 'document_type' => 'Ijazah SMP', 'original_name' => 'ijazah.pdf']);
        $this->assertDatabaseHas('student_achievements', ['student_id' => $student->id, 'name' => 'Juara Test']);
        $this->assertDatabaseHas('student_organizations', ['student_id' => $student->id, 'name' => 'OSIS']);
        $this->assertDatabaseHas('student_documents', ['student_id' => $student->id, 'document_type' => 'Sertifikat Prestasi', 'original_name' => 'sertifikat.pdf']);
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
            ...$this->parentFields(),
            'organization_status' => 'tidak',
            'self_improvement_notes' => 'Meningkatkan disiplin',
            'mcu_status' => 'belum',
        ]);
        $tka = TkaSubject::create(['name' => 'Matematika TKA', 'is_active' => true]);
        $student->tkaSelections()->create(['tka_subject_id' => $tka->id]);
        foreach (['Ijazah SMP', 'Akte', 'Kartu Keluarga'] as $type) {
            $student->documents()->create(['document_type' => $type, 'file_path' => $type.'.pdf', 'original_name' => $type.'.pdf', 'mime_type' => 'application/pdf', 'file_size' => 1]);
        }

        $progress = app(StudentProgressService::class)->calculate($student->fresh());

        $this->assertSame(100, $progress['percentage']);
        $this->assertSame(30, $progress['completed']);
        $this->assertSame(30, $progress['total']);
    }

    public function test_student_can_save_partial_biodata(): void
    {
        $user = $this->studentUser();
        $student = Student::create(['nis' => 'S-007', 'name' => 'Siswa Draft', 'user_id' => $user->id]);

        $this->actingAs($user)
            ->put(route('siswa.biodata.update'), [
                'phone' => '08123456789',
                'mcu_status' => 'sudah',
            ])
            ->assertRedirect(route('siswa.biodata.index'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('student_profiles', [
            'student_id' => $student->id,
            'phone' => '08123456789',
            'mcu_status' => 'sudah',
            'mcu_count' => null,
            'mcu_last_date' => null,
        ]);
    }

    public function test_university_choices_must_be_unique(): void
    {
        $user = $this->studentUser();
        Student::create(['nis' => 'S-008', 'name' => 'Siswa Kampus Sama', 'user_id' => $user->id]);
        $choices = $this->universityChoices();

        $this->actingAs($user)
            ->put(route('siswa.biodata.update'), [
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
                'height_cm' => 165,
                'weight_kg' => 55,
                'medical_history' => '-',
                'university_choice_1_id' => $choices['university_choice_1_id'],
                'university_choice_2_id' => $choices['university_choice_1_id'],
                'university_choice_3_id' => $choices['university_choice_3_id'],
                ...$this->parentFields(),
                'organization_status' => 'tidak',
                'self_improvement_notes' => 'Meningkatkan disiplin',
                'mcu_status' => 'belum',
            ])
            ->assertSessionHasErrors('university_choice_1_id');
    }

    public function test_mcu_done_can_be_saved_without_count_and_last_date(): void
    {
        $user = $this->studentUser();
        $student = Student::create(['nis' => 'S-012', 'name' => 'Siswa MCU', 'user_id' => $user->id]);

        $this->actingAs($user)
            ->put(route('siswa.biodata.update'), [
                ...$this->validBiodataPayload(),
                'mcu_status' => 'sudah',
            ])
            ->assertRedirect(route('siswa.biodata.index'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('student_profiles', [
            'student_id' => $student->id,
            'mcu_status' => 'sudah',
            'mcu_count' => null,
            'mcu_last_date' => null,
        ]);
    }

    public function test_government_school_choice_clears_major(): void
    {
        Storage::fake('local');
        $user = $this->studentUser();
        $student = Student::create(['nis' => 'S-013', 'name' => 'Siswa Kedinasan', 'user_id' => $user->id]);
        $kedinasan = University::firstOrCreate(['name' => 'Politeknik Keuangan Negara STAN'], ['type' => 'kedinasan', 'is_active' => true]);

        $this->actingAs($user)
            ->put(route('siswa.biodata.update'), [
                ...$this->validBiodataPayload(),
                'university_choice_1_id' => $kedinasan->id,
                'university_major_choice_1' => 'Akuntansi',
            ])
            ->assertRedirect(route('siswa.biodata.index'));

        $this->assertDatabaseHas('student_profiles', ['student_id' => $student->id, 'university_choice_1_id' => $kedinasan->id, 'university_major_choice_1' => null]);
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

    public function test_achievement_status_tidak_deletes_existing_achievements_and_certificates(): void
    {
        Storage::fake('local');
        $user = $this->studentUser();
        $student = Student::create(['nis' => 'S-014', 'name' => 'Siswa Prestasi Dihapus', 'user_id' => $user->id]);
        $achievement = $student->achievements()->create(['type' => 'akademik', 'name' => 'Juara Lama', 'level' => 'kab_kota', 'year' => 2025]);
        Storage::disk('local')->put('student-documents/'.$student->id.'/sertifikat-lama.pdf', 'lama');
        $student->documents()->create([
            'achievement_id' => $achievement->id,
            'document_type' => 'Sertifikat Prestasi',
            'file_path' => 'student-documents/'.$student->id.'/sertifikat-lama.pdf',
            'original_name' => 'sertifikat-lama.pdf',
            'mime_type' => 'application/pdf',
            'file_size' => 4,
            'uploaded_by' => $user->id,
        ]);

        $this->actingAs($user)
            ->put(route('siswa.biodata.update'), [
                ...$this->validBiodataPayload(),
                'achievement_status' => 'tidak',
            ])
            ->assertRedirect(route('siswa.biodata.index'));

        $this->assertDatabaseHas('student_profiles', ['student_id' => $student->id, 'achievement_status' => 'tidak']);
        $this->assertDatabaseMissing('student_achievements', ['student_id' => $student->id, 'name' => 'Juara Lama']);
        $this->assertDatabaseMissing('student_documents', ['student_id' => $student->id, 'original_name' => 'sertifikat-lama.pdf']);
        Storage::disk('local')->assertMissing('student-documents/'.$student->id.'/sertifikat-lama.pdf');
    }

    public function test_achievement_status_ya_saves_achievements(): void
    {
        Storage::fake('local');
        $user = $this->studentUser();
        $student = Student::create(['nis' => 'S-015', 'name' => 'Siswa Prestasi Baru', 'user_id' => $user->id]);

        $this->actingAs($user)
            ->put(route('siswa.biodata.update'), [
                ...$this->validBiodataPayload(),
                'achievement_status' => 'ya',
                'achievements' => [[
                    'type' => 'non_akademik',
                    'name' => 'Juara Baru',
                    'level' => 'provinsi',
                    'year' => 2026,
                    'certificate' => UploadedFile::fake()->create('sertifikat-baru.pdf', 200, 'application/pdf'),
                ]],
            ])
            ->assertRedirect(route('siswa.biodata.index'));

        $this->assertDatabaseHas('student_profiles', ['student_id' => $student->id, 'achievement_status' => 'ya']);
        $this->assertDatabaseHas('student_achievements', ['student_id' => $student->id, 'name' => 'Juara Baru']);
        $this->assertDatabaseHas('student_documents', ['student_id' => $student->id, 'document_type' => 'Sertifikat Prestasi', 'original_name' => 'sertifikat-baru.pdf']);
    }

    public function test_missing_achievement_status_preserves_existing_achievements(): void
    {
        Storage::fake('local');
        $user = $this->studentUser();
        $student = Student::create(['nis' => 'S-016', 'name' => 'Siswa Prestasi Lama', 'user_id' => $user->id]);
        $student->achievements()->create(['type' => 'akademik', 'name' => 'Juara Bertahan', 'level' => 'kab_kota', 'year' => 2025]);

        $this->actingAs($user)
            ->put(route('siswa.biodata.update'), $this->validBiodataPayload())
            ->assertRedirect(route('siswa.biodata.index'));

        $this->assertDatabaseHas('student_achievements', ['student_id' => $student->id, 'name' => 'Juara Bertahan']);
    }

    public function test_student_can_save_multiple_tka_subjects(): void
    {
        $user = $this->studentUser();
        $student = Student::create(['nis' => 'S-018', 'name' => 'Siswa TKA', 'user_id' => $user->id]);
        $first = TkaSubject::create(['name' => 'Matematika TKA', 'is_active' => true]);
        $second = TkaSubject::create(['name' => 'Bahasa Indonesia TKA', 'is_active' => true]);

        $this->actingAs($user)
            ->put(route('siswa.biodata.update'), [
                ...$this->validBiodataPayload(),
                'tka_subjects' => [$first->id, $second->id],
            ])
            ->assertRedirect(route('siswa.biodata.index'));

        $this->assertDatabaseHas('student_tka_subjects', ['student_id' => $student->id, 'tka_subject_id' => $first->id]);
        $this->assertDatabaseHas('student_tka_subjects', ['student_id' => $student->id, 'tka_subject_id' => $second->id]);

        $progress = app(StudentProgressService::class)->calculate($student->fresh('tkaSelections'));

        $this->assertTrue($progress['sections']['tka']);
    }

    public function test_tka_subjects_must_be_unique(): void
    {
        $user = $this->studentUser();
        Student::create(['nis' => 'S-019', 'name' => 'Siswa TKA Ganda', 'user_id' => $user->id]);
        $subject = TkaSubject::create(['name' => 'Bahasa Inggris TKA', 'is_active' => true]);

        $this->actingAs($user)
            ->put(route('siswa.biodata.update'), [
                ...$this->validBiodataPayload(),
                'tka_subjects' => [$subject->id, $subject->id],
            ])
            ->assertSessionHasErrors('tka_subjects');
    }

    public function test_inactive_tka_subjects_are_rejected(): void
    {
        $user = $this->studentUser();
        Student::create(['nis' => 'S-020', 'name' => 'Siswa TKA Nonaktif', 'user_id' => $user->id]);
        $subject = TkaSubject::create(['name' => 'Mapel Lama TKA', 'is_active' => false]);

        $this->actingAs($user)
            ->put(route('siswa.biodata.update'), [
                ...$this->validBiodataPayload(),
                'tka_subjects' => [$subject->id],
            ])
            ->assertSessionHasErrors('tka_subjects.0');
    }

    public function test_tka_sync_replaces_previous_selections(): void
    {
        $user = $this->studentUser();
        $student = Student::create(['nis' => 'S-021', 'name' => 'Siswa Ganti TKA', 'user_id' => $user->id]);
        $old = TkaSubject::create(['name' => 'Mapel Lama Dipilih', 'is_active' => true]);
        $new = TkaSubject::create(['name' => 'Mapel Baru Dipilih', 'is_active' => true]);
        $student->tkaSelections()->create(['tka_subject_id' => $old->id]);

        $this->actingAs($user)
            ->put(route('siswa.biodata.update'), [
                ...$this->validBiodataPayload(),
                'tka_subjects' => [$new->id],
            ])
            ->assertRedirect(route('siswa.biodata.index'));

        $this->assertDatabaseMissing('student_tka_subjects', ['student_id' => $student->id, 'tka_subject_id' => $old->id]);
        $this->assertDatabaseHas('student_tka_subjects', ['student_id' => $student->id, 'tka_subject_id' => $new->id]);
    }

    public function test_missing_tka_subjects_preserves_existing_selections(): void
    {
        $user = $this->studentUser();
        $student = Student::create(['nis' => 'S-022', 'name' => 'Siswa TKA Bertahan', 'user_id' => $user->id]);
        $subject = TkaSubject::create(['name' => 'Mapel Bertahan', 'is_active' => true]);
        $student->tkaSelections()->create(['tka_subject_id' => $subject->id]);
        $payload = $this->validBiodataPayload();
        unset($payload['tka_subjects']);

        $this->actingAs($user)
            ->put(route('siswa.biodata.update'), $payload)
            ->assertRedirect(route('siswa.biodata.index'));

        $this->assertDatabaseHas('student_tka_subjects', ['student_id' => $student->id, 'tka_subject_id' => $subject->id]);
    }

    public function test_tka_is_required_for_complete_progress(): void
    {
        $user = $this->studentUser();
        $student = Student::create(['nis' => 'S-023', 'name' => 'Siswa Tanpa TKA', 'user_id' => $user->id]);

        $this->actingAs($user)
            ->put(route('siswa.biodata.update'), array_merge($this->validBiodataPayload(), ['tka_subjects' => []]))
            ->assertRedirect(route('siswa.biodata.index'));

        $progress = app(StudentProgressService::class)->calculate($student->fresh('tkaSelections'));

        $this->assertFalse($progress['sections']['tka']);
        $this->assertLessThan(100, $progress['percentage']);
    }

    public function test_bk_achievement_status_tidak_deletes_existing_achievements(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();
        $user->assignRole(Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']));
        $student = Student::create(['nis' => 'S-017', 'name' => 'Siswa BK Prestasi']);
        $student->achievements()->create(['type' => 'akademik', 'name' => 'Juara BK Lama', 'level' => 'kab_kota', 'year' => 2025]);

        $this->actingAs($user)
            ->put(route('bk.students.biodata.update', $student), ['achievement_status' => 'tidak'])
            ->assertRedirect(route('bk.students.biodata.show', $student));

        $this->assertDatabaseHas('student_profiles', ['student_id' => $student->id, 'achievement_status' => 'tidak']);
        $this->assertDatabaseMissing('student_achievements', ['student_id' => $student->id, 'name' => 'Juara BK Lama']);
    }

    public function test_bk_can_sync_student_tka_subjects(): void
    {
        $user = User::factory()->create();
        $user->assignRole(Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']));
        $student = Student::create(['nis' => 'S-024', 'name' => 'Siswa BK TKA']);
        $subject = TkaSubject::create(['name' => 'Mapel BK TKA', 'is_active' => true]);

        $this->actingAs($user)
            ->put(route('bk.students.biodata.update', $student), ['tka_subjects' => [$subject->id]])
            ->assertRedirect(route('bk.students.biodata.show', $student));

        $this->assertDatabaseHas('student_tka_subjects', ['student_id' => $student->id, 'tka_subject_id' => $subject->id]);

        $this->actingAs($user)
            ->get(route('bk.students.biodata.show', $student))
            ->assertOk()
            ->assertSee('Mapel BK TKA');
    }

    public function test_student_can_upload_personal_documents_without_counting_as_certificates(): void
    {
        Storage::fake('local');
        $user = $this->studentUser();
        $student = Student::create(['nis' => 'S-011', 'name' => 'Siswa Dokumen', 'user_id' => $user->id]);

        foreach (['ijazah' => 'Ijazah SMP', 'akte' => 'Akte', 'kartu_keluarga' => 'Kartu Keluarga'] as $type => $label) {
            $this->actingAs($user)
                ->post(route('siswa.biodata.documents.store'), [
                    'document_type' => $type,
                    'documents' => [UploadedFile::fake()->create($type.'.pdf', 200, 'application/pdf')],
                ])
                ->assertRedirect();

            $this->assertDatabaseHas('student_documents', [
                'student_id' => $student->id,
                'document_type' => $label,
                'original_name' => $type.'.pdf',
            ]);
        }

        $student->refresh()->load('documents');
        $reportRow = app(\App\Services\BiodataReportService::class)->generate([])['rows']->firstWhere('student.id', $student->id);

        $this->assertSame(0, $reportRow['certificate_count']);
    }

    public function test_legacy_personal_document_types_count_as_complete(): void
    {
        Storage::fake('local');
        $user = $this->studentUser();
        $student = Student::create(['nis' => 'S-012', 'name' => 'Siswa Legacy Dokumen', 'user_id' => $user->id]);

        $this->actingAs($user)
            ->put(route('siswa.biodata.update'), $this->validBiodataPayload())
            ->assertRedirect(route('siswa.biodata.index'));

        $student->documents()->delete();
        foreach (['ijazah_smp' => 'ijazah.pdf', 'akte' => 'akte.pdf', 'kartu_keluarga' => 'kk.pdf'] as $type => $name) {
            $student->documents()->create(['document_type' => $type, 'file_path' => $name, 'original_name' => $name, 'mime_type' => 'application/pdf', 'file_size' => 1]);
        }

        $progress = app(StudentProgressService::class)->calculate($student->fresh('documents', 'profile', 'organizations'));

        $this->assertTrue($progress['sections']['documents']);
        $this->assertSame(100, $progress['percentage']);

        $this->actingAs($user)
            ->get(route('siswa.biodata.index'))
            ->assertOk()
            ->assertSee('data-progress-initial="1"', false);
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
                ...$this->parentFields(),
                'organization_status' => 'tidak',
                'self_improvement_notes' => 'Meningkatkan disiplin',
                'mcu_status' => 'sudah',
                'mcu_count' => 2,
                'mcu_last_date' => '2026-09-15',
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
        $third = University::firstOrCreate(
            ['name' => 'Universitas Padjadjaran'],
            ['short_name' => 'Unpad', 'type' => 'negeri', 'is_active' => true]
        );

        return [
            'university_choice_1_id' => $first->id,
            'university_choice_2_id' => $second->id,
            'university_choice_3_id' => $third->id,
        ];
    }

    private function validBiodataPayload(): array
    {
        $tka = TkaSubject::firstOrCreate(
            ['name' => 'Matematika TKA'],
            ['is_active' => true]
        );

        return [
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
            'mcu_status' => 'belum',
            ...$this->universityChoices(),
            ...$this->parentFields(),
            'organization_status' => 'tidak',
            'self_improvement_notes' => 'Meningkatkan disiplin',
            'tka_subjects' => [$tka->id],
            'ijazah_smp' => UploadedFile::fake()->create('ijazah.pdf', 200, 'application/pdf'),
            'akte' => UploadedFile::fake()->create('akte.pdf', 200, 'application/pdf'),
            'kartu_keluarga' => UploadedFile::fake()->create('kk.pdf', 200, 'application/pdf'),
        ];
    }

    private function parentFields(): array
    {
        return [
            'parent_father_name' => 'Bapak Test',
            'parent_father_occupation' => 'Wiraswasta',
            'parent_mother_name' => 'Ibu Test',
            'parent_mother_occupation' => 'Guru',
            'parent_phone' => '081111111111',
            'parent_address' => 'Alamat orang tua',
        ];
    }
}
