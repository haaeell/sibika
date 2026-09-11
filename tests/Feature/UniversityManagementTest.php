<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\University;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UniversityManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_bk_can_open_master_university_from_sidebar(): void
    {
        $this->actingAs($this->bkUser())
            ->get(route('bk.universities.index'))
            ->assertOk()
            ->assertSee('Master Kampus')
            ->assertSee(route('bk.universities.index'));
    }

    public function test_bk_can_create_update_filter_and_delete_university(): void
    {
        $user = $this->bkUser();

        $this->actingAs($user)->post(route('bk.universities.store'), [
            'name' => 'Universitas Contoh Indonesia',
            'short_name' => 'UCI',
            'type' => 'swasta',
            'is_active' => '1',
        ])->assertRedirect(route('bk.universities.index'));

        $university = University::where('short_name', 'UCI')->firstOrFail();
        $this->assertTrue($university->is_active);

        $this->actingAs($user)->put(route('bk.universities.update', $university), [
            'name' => 'Universitas Contoh Nusantara',
            'short_name' => 'UCN',
            'type' => 'negeri',
        ])->assertRedirect(route('bk.universities.index'));

        $this->actingAs($user)
            ->getJson(route('bk.universities.data', ['type' => ['negeri']]))
            ->assertOk()
            ->assertJsonFragment(['name' => 'Universitas Contoh Nusantara']);

        $this->actingAs($user)
            ->delete(route('bk.universities.destroy', $university))
            ->assertRedirect(route('bk.universities.index'));

        $this->assertDatabaseMissing('universities', ['id' => $university->id]);
    }

    public function test_university_selected_by_student_cannot_be_deleted(): void
    {
        $university = University::create([
            'name' => 'Kampus Pilihan Siswa',
            'short_name' => 'KPS',
            'type' => 'kedinasan',
            'is_active' => true,
        ]);
        $student = Student::create(['nis' => 'K-001', 'name' => 'Siswa Kampus']);
        $student->profile()->create(['university_choice_1_id' => $university->id]);

        $this->actingAs($this->bkUser())
            ->from(route('bk.universities.index'))
            ->delete(route('bk.universities.destroy', $university))
            ->assertRedirect(route('bk.universities.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('universities', ['id' => $university->id]);
    }

    private function bkUser(): User
    {
        $user = User::factory()->create();
        $user->assignRole(Role::firstOrCreate(['name' => 'bk', 'guard_name' => 'web']));

        return $user;
    }
}
