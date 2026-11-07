<?php

namespace Tests\Feature;

use App\Exports\TableExport;
use App\Models\AcademicYear;
use App\Models\Cohort;
use App\Models\CounselingSession;
use App\Models\Major;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CounselingTest extends TestCase
{
    use RefreshDatabase;

    public function test_bk_tracks_multiple_sessions_filters_and_follow_up(): void
    {
        $bk = $this->roleUser('bk');
        $admin = $this->roleUser('super_admin');
        $studentUser = $this->roleUser('siswa');
        [$class, $otherClass] = $this->classes();
        $student = Student::create(['nis' => 'KONS001', 'name' => 'Alya Konseling', 'class_id' => $class->id, 'cohort_id' => Cohort::create(['name' => 'Uji Konseling', 'entry_year' => 2024, 'graduation_year' => 2027, 'status' => 'active'])->id, 'user_id' => $studentUser->id]);
        $data = [
            'class_id' => $class->id,
            'student_id' => $student->id,
            'held_at' => '2026-11-01 10:00',
            'type' => 'belajar',
            'topic' => 'Strategi belajar',
            'summary' => 'Kesulitan mengatur waktu belajar.',
            'outcome' => 'Membuat jadwal belajar.',
            'follow_up' => 'Evaluasi jadwal bersama.',
            'due_at' => '2026-11-05',
        ];

        $this->actingAs($bk)->get(route('bk.counseling.create', ['student_id' => $student->id]))->assertOk()->assertSee('data-class-id="'.$class->id.'"', false)->assertSee('select2');
        $this->actingAs($bk)->post(route('bk.counseling.store'), $data)->assertRedirect(route('bk.counseling.student', $student));
        $session = CounselingSession::firstOrFail();
        $this->assertSame($bk->id, $session->created_by);
        $this->actingAs($admin)->post(route('bk.counseling.store'), [...$data, 'topic' => 'Konseling lanjutan', 'held_at' => '2026-11-03 10:00', 'summary' => 'Evaluasi kondisi sosial.', 'outcome' => 'Siswa memahami langkah berikutnya.', 'follow_up' => null, 'due_at' => null])->assertRedirect();
        $this->get(route('bk.counseling.index'))->assertOk()->assertSee('counseling-table');
        $this->get(route('bk.counseling.data', ['search' => ['value' => 'KONS001'], 'cohort_id' => $student->cohort_id, 'type' => 'belajar', 'status' => 'pending', 'from' => '2026-11-01', 'to' => '2026-11-02']))
            ->assertOk()->assertJsonCount(1, 'data')->assertSee('Strategi belajar')->assertDontSee('Konseling lanjutan');
        $this->get(route('bk.counseling.data', ['search' => ['value' => 'lanjutan']]))
            ->assertOk()->assertJsonCount(1, 'data')->assertSee('Konseling lanjutan');
        $this->get(route('bk.counseling.data', ['class_id' => $otherClass->id]))
            ->assertOk()->assertJsonCount(0, 'data');
        $this->get(route('bk.counseling.data', ['start' => 1, 'length' => 1, 'columns' => [['data' => 'session_date', 'name' => 'held_at']], 'order' => [['column' => 0, 'dir' => 'desc']]]))
            ->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('recordsTotal', 2);
        $this->get(route('bk.counseling.student', $student))->assertOk()->assertSee('Strategi belajar')->assertSee('Konseling lanjutan')
            ->assertSee('Pertemuan ke-1')->assertSee('Pertemuan ke-2');
        $this->get(route('bk.counseling.student', ['student' => $student, 'from' => '2026-11-03', 'to' => '2026-11-03']))
            ->assertOk()->assertSee('Konseling lanjutan')->assertSee('Pertemuan ke-2')->assertDontSee('Strategi belajar');
        $this->get(route('bk.counseling.student', ['student' => $student, 'to' => '2026-11-01']))
            ->assertOk()->assertSee('Strategi belajar')->assertDontSee('Konseling lanjutan');
        $this->get(route('bk.counseling.student', ['student' => $student, 'from' => '2026-11-05']))
            ->assertOk()->assertSee('Tidak ada sesi')->assertDontSee('Strategi belajar');
        $this->get(route('bk.counseling.student', ['student' => $student, 'search' => 'mengatur waktu']))
            ->assertOk()->assertSee('Strategi belajar')->assertSee('Pertemuan ke-1')->assertDontSee('Konseling lanjutan');
        $this->get(route('bk.counseling.student', ['student' => $student, 'search' => 'Evaluasi jadwal']))
            ->assertOk()->assertSee('Strategi belajar')->assertDontSee('Konseling lanjutan');
        $this->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->get(route('bk.counseling.student', ['student' => $student, 'search' => 'mengatur waktu']))
            ->assertOk()->assertSee('data-search-content')->assertSee('Pertemuan ke-1')->assertDontSee('<html', false);
        $this->flushHeaders();
        $this->get(route('bk.counseling.student', ['student' => $student, 'search' => 'tidak ada']))
            ->assertOk()->assertSee('Tidak ada sesi');
        $this->get(route('bk.counseling.student', ['student' => $student, 'from' => '2026-11-03', 'to' => '2026-11-01']))
            ->assertSessionHasErrors('to');
        $this->get(route('bk.counseling.edit', $session))->assertOk()->assertSee('data-class-id="'.$class->id.'"', false);

        $this->post(route('bk.counseling.store'), [...$data, 'class_id' => $otherClass->id])->assertSessionHasErrors('student_id');
        $this->assertSame(2, CounselingSession::count());
        $this->put(route('bk.counseling.update', $session), [...$data, 'class_id' => $otherClass->id])->assertSessionHasErrors('student_id');

        $this->put(route('bk.counseling.update', $session), [...$data, 'topic' => 'Strategi baru'])->assertRedirect();
        $this->assertDatabaseHas('counseling_sessions', ['id' => $session->id, 'topic' => 'Strategi baru', 'created_by' => $bk->id, 'updated_by' => $admin->id]);
        $this->patch(route('bk.counseling.complete', $session))->assertRedirect();
        $this->assertNotNull($session->fresh()->completed_at);
        $this->get(route('bk.counseling.data', ['status' => 'done']))->assertOk()->assertJsonCount(1, 'data')->assertSee('Strategi baru');
        $this->patch(route('bk.counseling.complete', $session))->assertRedirect();
        $this->assertNull($session->fresh()->completed_at);
    }

    public function test_private_notes_are_restricted_and_invalid_inputs_rejected(): void
    {
        $bk = $this->roleUser('bk');
        $studentUser = $this->roleUser('siswa');
        $teacher = $this->roleUser('guru');
        $student = Student::create(['nis' => 'KONS002', 'name' => 'Budi', 'user_id' => $studentUser->id]);
        $session = CounselingSession::create(['student_id' => $student->id, 'created_by' => $bk->id, 'held_at' => now(), 'type' => 'pribadi', 'topic' => 'Rahasia', 'summary' => 'Catatan privat']);
        foreach ([$studentUser, $teacher] as $user) {
            $this->actingAs($user)->get(route('bk.counseling.index'))->assertForbidden();
            $this->get(route('bk.counseling.data'))->assertForbidden();
            $this->get(route('bk.counseling.student', $student))->assertForbidden();
            $this->get(route('bk.counseling.edit', $session))->assertForbidden();
            $this->post(route('bk.counseling.store'), ['student_id' => $student->id])->assertForbidden();
            $this->patch(route('bk.counseling.complete', $session))->assertForbidden();
        }
        $this->actingAs($bk)->post(route('bk.counseling.store'), ['student_id' => $student->id, 'type' => 'invalid'])->assertSessionHasErrors(['held_at', 'type', 'topic', 'summary']);
        $this->patch(route('bk.counseling.complete', $session))->assertStatus(422);
    }

    public function test_student_sees_only_own_full_history_without_edit_controls(): void
    {
        $bk = $this->roleUser('bk');
        $owner = $this->roleUser('siswa');
        $other = $this->roleUser('siswa');
        $student = Student::create(['nis' => 'KONS003', 'name' => 'Alya', 'user_id' => $owner->id]);
        $another = Student::create(['nis' => 'KONS004', 'name' => 'Budi', 'user_id' => $other->id]);
        CounselingSession::create([
            'student_id' => $student->id, 'created_by' => $bk->id, 'held_at' => '2026-11-01 10:00',
            'type' => 'belajar', 'topic' => 'Rencana belajar', 'summary' => 'Kesulitan konsentrasi',
            'outcome' => 'Jadwal baru', 'follow_up' => 'Evaluasi pekan depan',
        ]);
        CounselingSession::create([
            'student_id' => $another->id, 'created_by' => $bk->id, 'held_at' => '2026-11-02 10:00',
            'type' => 'pribadi', 'topic' => 'Rahasia Budi', 'summary' => 'Masalah privat Budi',
        ]);

        $this->actingAs($owner)->get(route('siswa.counseling.index'))
            ->assertOk()->assertSee('Rencana belajar')->assertSee('Kesulitan konsentrasi')
            ->assertSee('Jadwal baru')->assertSee('Evaluasi pekan depan')->assertSee('Pertemuan ke-1')
            ->assertDontSee('Rahasia Budi')->assertDontSee('Masalah privat Budi')
            ->assertDontSee('Tandai selesai')->assertDontSee('bk/counseling/1/edit');

        $this->get(route('siswa.counseling.index', ['search' => 'Rahasia Budi']))
            ->assertOk()->assertSee('Tidak ada sesi')->assertDontSee('Masalah privat Budi');
        $this->get(route('siswa.counseling.index', ['search' => 'konsentrasi']))
            ->assertOk()->assertSee('Rencana belajar');
        $this->actingAs($other)->get(route('siswa.counseling.index'))
            ->assertOk()->assertSee('Rahasia Budi')->assertDontSee('Rencana belajar');
        $this->actingAs($bk)->get(route('siswa.counseling.index'))->assertForbidden();
    }

    public function test_admin_exports_all_matching_sessions_beyond_visible_page(): void
    {
        $bk = $this->roleUser('bk');
        $admin = $this->roleUser('super_admin');
        $studentUser = $this->roleUser('siswa');
        [$class, $otherClass] = $this->classes();
        $student = Student::create(['nis' => 'EXP001', 'name' => 'Alya Konseling', 'class_id' => $class->id, 'user_id' => $studentUser->id]);
        $other = Student::create(['nis' => 'EXP002', 'name' => 'Budi Konseling', 'class_id' => $otherClass->id]);
        foreach (range(1, 3) as $index) {
            CounselingSession::create([
                'student_id' => $student->id, 'created_by' => $bk->id,
                'held_at' => '2026-11-01 10:00', 'type' => 'belajar',
                'topic' => 'Target belajar '.$index, 'summary' => 'Ringkasan pribadi '.$index,
                'outcome' => 'Rencana '.$index, 'follow_up' => 'Evaluasi '.$index,
                'due_at' => '2026-11-05',
            ]);
        }
        CounselingSession::create([
            'student_id' => $other->id, 'created_by' => $bk->id,
            'held_at' => '2026-11-01 10:00', 'type' => 'belajar',
            'topic' => 'Target belajar lain', 'summary' => 'Rahasia siswa lain',
        ]);

        $filters = ['class_id' => $class->id, 'type' => 'belajar', 'from' => '2026-11-01', 'to' => '2026-11-01', 'search' => 'Target belajar'];
        $this->actingAs($bk)->get(route('bk.counseling.data', [
            'class_id' => $class->id, 'search' => ['value' => 'Target belajar'], 'start' => 0, 'length' => 1,
        ]))->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('recordsFiltered', 3);

        Excel::fake();
        $this->get(route('bk.counseling.export', ['format' => 'xlsx', ...$filters]))->assertOk();
        Excel::assertDownloaded('konseling-'.now()->format('Ymd-His').'.xlsx', function (TableExport $export): bool {
            $rows = $export->array();

            return count($rows) === 3
                && collect($rows)->every(fn (array $row) => $row[1] === 'EXP001'
                    && str_contains($row[6], 'Target belajar') && str_contains($row[7], 'Ringkasan pribadi')
                    && $row[11] === 'Lewat tenggat');
        });
        $this->actingAs($admin)->get(route('bk.counseling.export', ['format' => 'pdf', ...$filters]))
            ->assertOk()->assertHeader('content-type', 'application/pdf');
        $this->get(route('bk.counseling.export', ['format' => 'csv']))->assertNotFound();
        $this->get(route('bk.counseling.export', ['format' => 'pdf', 'from' => '2026-11-03', 'to' => '2026-11-01']))
            ->assertSessionHasErrors('to');
        $this->actingAs($studentUser)->get(route('bk.counseling.export', ['format' => 'pdf']))->assertForbidden();
    }

    private function roleUser(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole(Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']));

        return $user;
    }

    private function classes(): array
    {
        $year = AcademicYear::create(['name' => '2026/2027', 'start_year' => 2026, 'end_year' => 2027, 'semester' => 'ganjil']);
        $major = Major::create(['code' => 'KONS', 'name' => 'Konseling']);

        return [
            SchoolClass::create(['name' => 'XI A', 'grade_level' => 'XI', 'academic_year_id' => $year->id, 'major_id' => $major->id]),
            SchoolClass::create(['name' => 'XI B', 'grade_level' => 'XI', 'academic_year_id' => $year->id, 'major_id' => $major->id]),
        ];
    }
}
