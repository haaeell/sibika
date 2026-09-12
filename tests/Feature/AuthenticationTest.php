<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_can_be_rendered(): void
    {
        $this->get('/login')->assertOk();
    }

    public function test_users_can_login_with_email(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@example.test',
            'password' => 'password',
        ]);

        $this->post('/login', [
            'login' => 'admin@example.test',
            'password' => 'password',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_students_can_login_with_nis_or_nisn(): void
    {
        foreach (['nis', 'nisn'] as $field) {
            $user = User::factory()->create(['password' => 'password']);
            $user->assignRole(Role::firstOrCreate(['name' => 'siswa', 'guard_name' => 'web']));
            $student = Student::create([
                'nis' => 'S-'.$field,
                'nisn' => 'N-'.$field,
                'name' => 'Siswa '.$field,
                'user_id' => $user->id,
            ]);

            $this->post('/login', [
                'login' => $student->{$field},
                'password' => 'password',
            ])->assertRedirect(route('siswa.dashboard'));

            $this->assertAuthenticatedAs($user);
            $this->post('/logout');
        }
    }

    public function test_users_cannot_login_with_wrong_password(): void
    {
        User::factory()->create([
            'email' => 'admin@example.test',
            'password' => 'password',
        ]);

        $this->post('/login', [
            'login' => 'admin@example.test',
            'password' => 'wrong-password',
        ])->assertSessionHasErrors('login');

        $this->assertGuest();
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/logout')
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_dashboard_requires_authentication(): void
    {
        $this->get('/dashboard')->assertRedirect(route('login'));
    }
}
