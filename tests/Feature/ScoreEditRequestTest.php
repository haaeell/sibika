<?php

namespace Tests\Feature;

use App\Models\Notification;
use App\Models\ScoreEditRequest;
use App\Models\ScoreSubjectSetting;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ScoreEditRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_save_first_time_then_locked(): void
    {
        [$student, $subject] = $this->seedScore();
        $siswa = $student->user;

        $this->actingAs($siswa)->post(route('siswa.scores.save'), [
            'semester' => 1,
            'scores' => [$subject->id => 80],
        ])->assertRedirect();

        // Simpan kedua tanpa izin → ditolak.
        $this->actingAs($siswa)->post(route('siswa.scores.save'), [
            'semester' => 1,
            'scores' => [$subject->id => 90],
        ])->assertRedirect()->assertSessionHas('error');

        $this->assertDatabaseHas('student_scores', [
            'student_id' => $student->id, 'subject_id' => $subject->id, 'semester_number' => 1, 'score' => 80,
        ]);
    }

    public function test_full_approval_flow_allows_single_save(): void
    {
        [$student, $subject] = $this->seedScore();
        $siswa = $student->user;
        $bk = $this->bkUser();

        $this->actingAs($siswa)->post(route('siswa.scores.save'), [
            'semester' => 1, 'scores' => [$subject->id => 80],
        ])->assertRedirect();

        // Ajukan edit → notif ke BK.
        $this->actingAs($siswa)->post(route('siswa.scores.request-edit'), [
            'semester' => 1, 'reason' => 'Salah input, seharusnya 85 bukan 80.',
        ])->assertRedirect()->assertSessionHas('success');

        $editRequest = ScoreEditRequest::firstOrFail();
        $this->assertSame('pending', $editRequest->status);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $bk->id, 'type' => Notification::TYPE_SCORE_EDIT_REQUESTED,
        ]);

        // BK setujui → notif ke siswa.
        $this->actingAs($bk)->post(route('bk.score-edit-requests.approve', $editRequest))
            ->assertRedirect()->assertSessionHas('success');
        $this->assertDatabaseHas('notifications', [
            'user_id' => $siswa->id, 'type' => Notification::TYPE_SCORE_EDIT_APPROVED,
        ]);

        // Siswa bisa simpan sekali.
        $this->actingAs($siswa)->post(route('siswa.scores.save'), [
            'semester' => 1, 'scores' => [$subject->id => 85],
        ])->assertRedirect()->assertSessionHas('success');
        $this->assertDatabaseHas('student_scores', ['student_id' => $student->id, 'score' => 85]);

        // Simpan lagi → terkunci kembali.
        $this->actingAs($siswa)->post(route('siswa.scores.save'), [
            'semester' => 1, 'scores' => [$subject->id => 90],
        ])->assertRedirect()->assertSessionHas('error');
    }

    public function test_reject_requires_note_and_notifies_student(): void
    {
        [$student] = $this->seedScore();
        $siswa = $student->user;
        $bk = $this->bkUser();

        $editRequest = ScoreEditRequest::create([
            'student_id' => $student->id, 'semester_number' => 1,
            'reason' => 'Mohon dibuka karena salah input nilai.', 'status' => 'pending',
        ]);

        // Tanpa catatan → gagal validasi.
        $this->actingAs($bk)->post(route('bk.score-edit-requests.reject', $editRequest), [
            'review_note' => '',
        ])->assertSessionHasErrors('review_note');

        $this->actingAs($bk)->post(route('bk.score-edit-requests.reject', $editRequest), [
            'review_note' => 'Nilai sudah sesuai rapor.',
        ])->assertRedirect()->assertSessionHas('success');

        $this->assertDatabaseHas('notifications', [
            'user_id' => $siswa->id, 'type' => Notification::TYPE_SCORE_EDIT_REJECTED,
        ]);
    }

    public function test_cannot_request_twice_while_pending(): void
    {
        [$student] = $this->seedScore();
        $siswa = $student->user;

        ScoreEditRequest::create([
            'student_id' => $student->id, 'semester_number' => 1,
            'reason' => 'Pengajuan pertama masih menunggu.', 'status' => 'pending',
        ]);

        // Butuh nilai terisi agar sampai ke cek duplikat.
        $student->scores()->create(['subject_id' => Subject::first()->id, 'semester_number' => 1, 'score' => 70]);

        $this->actingAs($siswa)->post(route('siswa.scores.request-edit'), [
            'semester' => 1, 'reason' => 'Pengajuan kedua yang seharusnya ditolak.',
        ])->assertRedirect()->assertSessionHas('error');
    }

    /** @return array{Student, Subject} */
    private function seedScore(): array
    {
        Role::firstOrCreate(['name' => 'siswa', 'guard_name' => 'web']);

        $subject = Subject::create(['code' => 'MAT Seeded', 'name' => 'Matematika Uji', 'category' => 'general', 'is_active' => true]);
        ScoreSubjectSetting::create([
            'subject_id' => $subject->id, 'major_id' => null, 'semester_number' => 1,
            'is_required' => true, 'include_in_average' => true, 'is_active' => true,
        ]);

        $user = User::factory()->create();
        $user->assignRole('siswa');
        $student = Student::create(['nis' => 'REQ'.time().random_int(100, 999), 'nisn' => null, 'name' => 'Siswa Uji', 'status' => 'active', 'user_id' => $user->id]);

        return [$student->fresh(), $subject];
    }

    private function bkUser(): User
    {
        $user = User::factory()->create();
        $user->assignRole(Role::firstOrCreate(['name' => 'bk', 'guard_name' => 'web']));

        return $user;
    }
}
