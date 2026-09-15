<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\TkaSubject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TkaSubjectTest extends TestCase
{
    use RefreshDatabase;

    public function test_bk_can_open_tka_subject_master(): void
    {
        $this->actingAs($this->bkUser())
            ->get(route('bk.tka-subjects.index'))
            ->assertOk()
            ->assertSee('Mapel TKA');
    }

    public function test_bk_can_create_update_and_delete_tka_subject(): void
    {
        $user = $this->bkUser();

        $this->actingAs($user)->post(route('bk.tka-subjects.store'), [
            'code' => 'TKA-MAT',
            'name' => 'Matematika TKA',
            'is_active' => '1',
        ])->assertRedirect(route('bk.tka-subjects.index'));

        $subject = TkaSubject::where('code', 'TKA-MAT')->firstOrFail();
        $this->assertTrue($subject->is_active);

        $this->actingAs($user)->put(route('bk.tka-subjects.update', $subject), [
            'code' => 'TKA-MAT',
            'name' => 'Matematika TKA Baru',
        ])->assertRedirect(route('bk.tka-subjects.index'));

        $this->actingAs($user)
            ->delete(route('bk.tka-subjects.destroy', $subject))
            ->assertRedirect(route('bk.tka-subjects.index'));

        $this->assertDatabaseMissing('tka_subjects', ['id' => $subject->id]);
    }

    public function test_tka_subject_code_and_name_must_be_unique(): void
    {
        TkaSubject::create(['code' => 'TKA-BIN', 'name' => 'Bahasa Indonesia TKA', 'is_active' => true]);

        $this->actingAs($this->bkUser())->post(route('bk.tka-subjects.store'), [
            'code' => 'TKA-BIN',
            'name' => 'Bahasa Indonesia TKA',
        ])->assertSessionHasErrors(['code', 'name']);
    }

    public function test_tka_subject_selected_by_student_cannot_be_deleted(): void
    {
        $subject = TkaSubject::create(['code' => 'TKA-ENG', 'name' => 'Bahasa Inggris TKA', 'is_active' => true]);
        $student = Student::create(['nis' => 'T-001', 'name' => 'Siswa TKA']);
        $student->tkaSelections()->create(['tka_subject_id' => $subject->id]);

        $this->actingAs($this->bkUser())
            ->from(route('bk.tka-subjects.index'))
            ->delete(route('bk.tka-subjects.destroy', $subject))
            ->assertRedirect(route('bk.tka-subjects.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('tka_subjects', ['id' => $subject->id]);
    }

    private function bkUser(): User
    {
        $user = User::factory()->create();
        $user->assignRole(Role::firstOrCreate(['name' => 'bk', 'guard_name' => 'web']));

        return $user;
    }
}
