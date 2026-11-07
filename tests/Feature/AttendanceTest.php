<?php

namespace Tests\Feature;

use App\Exports\AttendanceWorkbook;
use App\Exports\StudentScoreWorkbook;
use App\Models\AttendanceWeek;
use App\Models\Student;
use App\Models\User;
use Database\Seeders\EligibleTestSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AttendanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_import_creates_one_period_with_each_students_own_percentage(): void
    {
        $this->seed(EligibleTestSeeder::class);
        $this->actingAs($this->user('bk'));
        $students = Student::with('schoolClass')->orderBy('id')->take(2)->get();
        $file = UploadedFile::fake()->create('absensi.xlsx', 10, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $rows = [
            ['nama_siswa', 'jumlah_pertemuan', 'jumlah_th'],
            [$students[0]->name, 20, 4],
            [$students[1]->name, 10, 2],
        ];
        Excel::shouldReceive('toCollection')->andReturn($this->workbook($rows));

        $this->post(route('bk.attendance.import'), [
            'week_number' => 15, 'starts_on' => '2026-10-26', 'ends_on' => '2026-10-30', 'file' => $file,
        ])->assertRedirect(route('bk.attendance.index'));
        $this->assertDatabaseCount('attendance_weeks', 1);
        $this->assertDatabaseCount('attendance_absences', 2);
        $this->assertDatabaseHas('attendance_absences', ['student_id' => $students[0]->id, 'meetings' => 20, 'absences' => 4]);
        $this->get(route('bk.attendance.index'))->assertOk()->assertSee('Menampilkan minggu terbaru')->assertSee($students[0]->name);
        $this->get(route('bk.attendance.index', ['week_id' => 'all']))->assertOk()->assertDontSee('Menampilkan minggu terbaru')->assertSee($students[0]->name);
        $this->get(route('bk.attendance.index', ['from' => '2026-10-01', 'to' => '2026-10-31', 'class_id' => $students[0]->class_id]))
            ->assertOk()->assertSee('20,00%')->assertSee('Minggu 15')->assertSee('5 Siswa Tanpa Keterangan Terbanyak')->assertSee('4 TH');

        Excel::fake();
        $this->get(route('bk.attendance.template'))->assertOk();
        Excel::assertDownloaded('template-absensi.xlsx', fn (StudentScoreWorkbook $book) => $book->sheets()[0]->headings() === $rows[0]);
        Excel::fake();
        $this->get(route('bk.attendance.export', ['format' => 'xlsx', 'from' => '2026-10-01', 'to' => '2026-10-31']))->assertOk();
        Excel::assertDownloaded('laporan-absensi-'.now()->format('Ymd-His').'.xlsx', function (AttendanceWorkbook $book): bool {
            [$weekly, $summary] = $book->sheets();

            return $weekly->headings() === ['Minggu ke', 'Rentang Tanggal', 'Siswa', 'NIS', 'Kelas', 'Pertemuan', 'Tidak Hadir', 'Persentase']
                && $summary->headings() === ['Siswa', 'NIS', 'Kelas', 'Total Pertemuan', 'Total Tidak Hadir', 'Persentase']
                && count($weekly->array()) === 2 && count($summary->array()) === 2;
        });
    }

    public function test_bad_rows_duplicate_period_and_overlapping_dates_abort_import(): void
    {
        $this->seed(EligibleTestSeeder::class);
        $this->actingAs($this->user('super_admin'));
        $student = Student::with('schoolClass')->firstOrFail();
        $header = ['nama_siswa', 'jumlah_pertemuan', 'jumlah_th'];
        $row = [$student->name, 20, 4];
        $file = UploadedFile::fake()->create('absensi.xlsx', 10, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $period = ['week_number' => 15, 'starts_on' => '2026-10-26', 'ends_on' => '2026-10-30'];

        Excel::shouldReceive('toCollection')->andReturn($this->workbook([$header, $row, $row]));
        $this->post(route('bk.attendance.import'), [...$period, 'file' => $file])->assertSessionHasErrors('file');
        $this->assertDatabaseCount('attendance_weeks', 0);

        Excel::shouldReceive('toCollection')->andReturn($this->workbook([$header, [$student->name, 5, 6]]));
        $this->post(route('bk.attendance.import'), [...$period, 'file' => $file])->assertSessionHasErrors('file');
        $this->assertDatabaseCount('attendance_weeks', 0);

        AttendanceWeek::create(['week_number' => 14, 'starts_on' => '2026-10-19', 'ends_on' => '2026-10-23']);
        Excel::shouldReceive('toCollection')->andReturn($this->workbook([$header, $row]));
        $this->post(route('bk.attendance.import'), [...$period, 'starts_on' => '2026-10-22', 'ends_on' => '2026-10-30', 'file' => $file])->assertSessionHasErrors('file');
        $this->assertDatabaseCount('attendance_weeks', 1);

        $this->post(route('bk.attendance.import'), [...$period, 'starts_on' => '2026-10-30', 'ends_on' => '2026-11-03', 'file' => $file])->assertSessionHasErrors('ends_on');
        $this->actingAs($student->user)->get(route('bk.attendance.index'))->assertForbidden();
    }

    private function workbook(array $rows): Collection
    {
        return collect([collect(array_map(fn ($row) => collect($row), $rows))]);
    }

    private function user(string $role): User
    {
        $user = User::factory()->create();
        $roleModel = Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        $user->assignRole($roleModel);

        if (in_array($role, ['bk', 'super_admin'])) {
            foreach (['pengasuhan.absensi', 'pengasuhan.pelanggaran'] as $perm) {
                $roleModel->givePermissionTo(Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']));
            }
        }

        return $user;
    }
}
