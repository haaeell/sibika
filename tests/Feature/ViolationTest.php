<?php

namespace Tests\Feature;

use App\Exports\StudentScoreWorkbook;
use App\Models\AcademicYear;
use App\Models\Major;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentViolation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ViolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_bk_records_historic_class_and_students_only_see_own_history(): void
    {
        $bk = $this->user('bk');
        $admin = $this->user('super_admin');
        $owner = $this->user('siswa');
        $other = $this->user('siswa');
        $student = Student::create(['nis' => 'P001', 'name' => 'Alya', 'user_id' => $owner->id]);
        Student::create(['nis' => 'P002', 'name' => 'Budi', 'user_id' => $other->id]);

        $this->actingAs($bk)->post(route('bk.violations.store'), [
            'student_id' => $student->id, 'class_at_incident' => 'X IPA 1', 'occurred_at' => '2025-02-03',
            'pasal_perdupsis' => 'Pasal 7', 'sanction' => 'Pembinaan tertulis',
        ])->assertRedirect(route('bk.violations.index'));
        $record = StudentViolation::firstOrFail();
        $this->assertSame('X IPA 1', $record->class_at_incident);
        $this->assertSame($bk->id, $record->created_by);
        $this->actingAs($bk)->get(route('bk.violations.index'))->assertOk()
            ->assertSee('id="violation-table"', false)->assertSee('id="warning-letter-modal"', false)
            ->assertSee('>No<', false);
        $this->actingAs($admin)->put(route('bk.violations.update', $record), [
            'student_id' => $student->id, 'class_at_incident' => 'X IPA 1', 'occurred_at' => '2025-02-03',
            'pasal_perdupsis' => 'Pasal 7', 'sanction' => 'Pembinaan lanjutan',
        ])->assertRedirect();
        $this->assertDatabaseHas('student_violations', ['id' => $record->id, 'created_by' => $bk->id, 'updated_by' => $admin->id]);
        $this->get(route('bk.violations.data', ['class_at_incident' => 'X IPA 1', 'from' => '2025-02-03', 'to' => '2025-02-03']))
            ->assertOk()->assertJsonCount(1, 'data')->assertSee('Pasal 7')->assertSee('data-warning-letter-url');
        $this->get(route('bk.violations.data', ['from' => '2025-02-04']))->assertOk()->assertJsonCount(0, 'data');
        $this->get(route('bk.violations.data', ['search' => ['value' => 'Alya']]))->assertOk()->assertJsonCount(1, 'data');
        $this->get(route('bk.violations.data', ['start' => 0, 'length' => 1, 'columns' => [['data' => 'incident', 'name' => 'occurred_at']], 'order' => [['column' => 0, 'dir' => 'desc']]]))
            ->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('recordsTotal', 1)->assertJsonPath('data.0.DT_RowIndex', 1);

        $this->actingAs($owner)->get(route('siswa.violations.index'))->assertOk()->assertSee('Pembinaan lanjutan')->assertDontSee('Edit');
        $this->get(route('bk.violations.index'))->assertForbidden();
        $this->get(route('bk.violations.data'))->assertForbidden();
        $this->post(route('bk.violations.store'), [])->assertForbidden();
        $this->actingAs($other)->get(route('siswa.violations.index'))->assertOk()->assertDontSee('Pasal 7');
    }

    public function test_template_has_expected_columns(): void
    {
        $bk = $this->user('bk');
        $this->actingAs($bk);
        Excel::fake();
        $this->get(route('bk.violations.template'))->assertOk();
        Excel::assertDownloaded('template-pelanggaran.xlsx', function (StudentScoreWorkbook $export): bool {
            [$data, $guide] = $export->sheets();

            return $data->headings() === ['nama_siswa', 'kelas_saat_ini', 'kelas_saat_kejadian', 'tanggal_kejadian', 'pasal_perdupsis', 'sanksi']
                && $data->array()[0] === ['ISI_NAMA_SISWA', 'XI IPA 1', 'X IPA 1', '03-02-2026', 'Pasal 7', 'Pembinaan tertulis']
                && $guide->headings() === ['Kolom', 'Cara mengisi'];
        });
    }

    public function test_import_is_atomic_and_reports_row_errors(): void
    {
        $bk = $this->user('bk');
        $class = $this->schoolClass();
        Student::create(['nis' => 'P010', 'name' => 'Citra', 'class_id' => $class->id]);
        $this->actingAs($bk);

        $file = UploadedFile::fake()->create('pelanggaran.xlsx', 10, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        Excel::shouldReceive('toCollection')->twice()->andReturn(collect([collect([
            collect(['nama_siswa', 'kelas_saat_ini', 'kelas_saat_kejadian', 'tanggal_kejadian', 'pasal_perdupsis', 'sanksi']),
            collect(['Citra', 'XI Uji', 'X-1', '03-02-2025', 'Pasal 4', 'Teguran']),
            collect(['Tidak Ada', 'XI Uji', 'X-2', '04/02/2025', 'Pasal 5', 'Pembinaan']),
        ])]), collect([collect([
            collect(['nama_siswa', 'kelas_saat_ini', 'kelas_saat_kejadian', 'tanggal_kejadian', 'pasal_perdupsis', 'sanksi']),
            collect(['Citra', 'XI Uji', 'X-1', '03-02-2025', 'Pasal 4', 'Teguran']),
            collect(['Citra', 'XI Uji', 'XI-1', '04/02/2026', 'Pasal 5', 'Pembinaan']),
        ])]));
        $this->post(route('bk.violations.import'), ['file' => $file])->assertSessionHasErrors('file')->assertSessionHas('import_errors');
        $this->assertStringContainsString('Baris 3:', session('import_errors')[0]);
        $this->assertSame(0, StudentViolation::count());

        $this->post(route('bk.violations.import'), ['file' => $file])->assertRedirect(route('bk.violations.index'))->assertSessionHas('success');
        $this->assertSame(2, StudentViolation::count());
        $this->assertDatabaseHas('student_violations', ['class_at_incident' => 'X-1', 'pasal_perdupsis' => 'Pasal 4']);

        Student::create(['nis' => 'P011', 'name' => 'Citra', 'class_id' => $class->id]);
        Excel::shouldReceive('toCollection')->andReturn(collect([collect([
            collect(['nama_siswa', 'kelas_saat_ini', 'kelas_saat_kejadian', 'tanggal_kejadian', 'pasal_perdupsis', 'sanksi']),
            collect(['Citra', 'XI Uji', 'X-1', '04-02-2026', 'Pasal 6', 'Pembinaan']),
        ])]));
        $this->post(route('bk.violations.import'), ['file' => $file])->assertSessionHasErrors('file');
        $this->assertStringContainsString('lebih dari satu', session('import_errors')[0]);
        $this->assertSame(2, StudentViolation::count());
    }

    public function test_warning_letter_is_private_pdf_and_replacement_removes_old_file(): void
    {
        Storage::fake('local');
        $bk = $this->user('bk');
        $owner = $this->user('siswa');
        $other = $this->user('siswa');
        $student = Student::create(['nis' => 'P020', 'name' => 'Dina', 'user_id' => $owner->id]);
        Student::create(['nis' => 'P021', 'name' => 'Eka', 'user_id' => $other->id]);
        $payload = [
            'student_id' => $student->id, 'class_at_incident' => 'XI IPA 1', 'occurred_at' => '2026-02-03',
            'pasal_perdupsis' => 'Pasal 8', 'sanction' => 'Surat peringatan',
        ];
        $this->actingAs($bk)->post(route('bk.violations.store'), $payload)->assertRedirect();
        $violation = StudentViolation::firstOrFail();
        $this->post(route('bk.violations.warning-letter.upload', $violation), ['warning_letter' => UploadedFile::fake()->create('sp-1.pdf', 100, 'application/pdf')])->assertRedirect();
        $violation->refresh();
        Storage::disk('local')->assertExists($violation->warning_letter_path);
        $this->actingAs($owner)->get(route('siswa.violations.index'))->assertSee('Unduh Surat Peringatan');
        $this->get(route('siswa.violations.warning-letter', $violation))->assertOk();
        $this->actingAs($other)->get(route('siswa.violations.warning-letter', $violation))->assertForbidden();
        $this->actingAs($bk)->post(route('bk.violations.warning-letter.upload', $violation), ['warning_letter' => UploadedFile::fake()->create('bukan-pdf.jpg', 100, 'image/jpeg')])->assertSessionHasErrors('warning_letter');

        $oldPath = $violation->warning_letter_path;
        $this->post(route('bk.violations.warning-letter.upload', $violation), ['warning_letter' => UploadedFile::fake()->create('sp-2.pdf', 100, 'application/pdf')])->assertRedirect();
        Storage::disk('local')->assertMissing($oldPath);
        Storage::disk('local')->assertExists($violation->fresh()->warning_letter_path);
    }

    private function user(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole(Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']));

        return $user;
    }

    private function schoolClass(): SchoolClass
    {
        $year = AcademicYear::create(['name' => '2026/2027', 'start_year' => 2026, 'end_year' => 2027, 'semester' => 'ganjil']);
        $major = Major::create(['code' => 'VLN', 'name' => 'Pelanggaran Uji']);

        return SchoolClass::create(['name' => 'XI Uji', 'grade_level' => 'XI', 'academic_year_id' => $year->id, 'major_id' => $major->id]);
    }
}
