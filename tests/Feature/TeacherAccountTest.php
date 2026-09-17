<?php

namespace Tests\Feature;

use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TeacherAccountTest extends TestCase
{
    use RefreshDatabase;

    public function test_creating_teacher_creates_login_account(): void
    {
        $bk = User::factory()->create();
        $bk->assignRole(Role::firstOrCreate(['name' => 'bk', 'guard_name' => 'web']));

        $this->actingAs($bk)->post(route('bk.teachers.store'), [
            'code' => 'GURU-001', 'name' => 'Guru Baru', 'email' => 'guru.baru@example.test', 'status' => 'active',
        ])->assertRedirect(route('bk.teachers.index'));

        $teacher = Teacher::where('code', 'GURU-001')->firstOrFail();
        $user = User::where('email', 'guru.baru@example.test')->firstOrFail();

        $this->assertSame($user->id, $teacher->user_id);
        $this->assertTrue($user->hasRole('guru'));
        $this->assertTrue(Hash::check('password', $user->password));
        $this->assertFalse($user->must_change_password);
    }
}
