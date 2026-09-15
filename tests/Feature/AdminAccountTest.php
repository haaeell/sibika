<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminAccountTest extends TestCase
{
    use RefreshDatabase;

    public function test_superadmin_can_view_account_settings(): void
    {
        $this->actingAs($this->superAdmin())
            ->get(route('admin.account.edit'))
            ->assertOk()
            ->assertSee('Akun Superadmin')
            ->assertSee('Password Saat Ini');
    }

    public function test_non_superadmin_cannot_access_account_settings(): void
    {
        $user = User::factory()->create();
        $user->assignRole(Role::firstOrCreate(['name' => 'bk', 'guard_name' => 'web']));

        $this->actingAs($user)
            ->get(route('admin.account.edit'))
            ->assertForbidden();
    }

    public function test_superadmin_can_change_email_with_current_password(): void
    {
        $user = $this->superAdmin();

        $this->actingAs($user)
            ->put(route('admin.account.update'), [
                'email' => 'admin.baru@example.test',
                'current_password' => 'Password123',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $this->assertSame('admin.baru@example.test', $user->fresh()->email);
    }

    public function test_superadmin_can_change_password_with_current_password(): void
    {
        $user = $this->superAdmin();

        $this->actingAs($user)
            ->put(route('admin.account.update'), [
                'email' => $user->email,
                'current_password' => 'Password123',
                'password' => 'PasswordBaru123',
                'password_confirmation' => 'PasswordBaru123',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $this->assertTrue(Hash::check('PasswordBaru123', $user->fresh()->password));
    }

    public function test_account_update_rejects_wrong_current_password_duplicate_email_and_weak_password(): void
    {
        $user = $this->superAdmin();
        User::factory()->create(['email' => 'sudah.dipakai@example.test']);

        $this->actingAs($user)
            ->put(route('admin.account.update'), [
                'email' => 'sudah.dipakai@example.test',
                'current_password' => 'Salah123',
                'password' => 'lemah',
                'password_confirmation' => 'berbeda',
            ])
            ->assertSessionHasErrors(['email', 'current_password', 'password']);
    }

    public function test_superadmin_can_login_with_updated_email_and_password(): void
    {
        $user = $this->superAdmin();

        $this->actingAs($user)
            ->put(route('admin.account.update'), [
                'email' => 'admin.login.baru@example.test',
                'current_password' => 'Password123',
                'password' => 'PasswordBaru123',
                'password_confirmation' => 'PasswordBaru123',
            ]);

        $this->post(route('logout'));

        $this->post(route('login'), [
            'login' => 'admin.login.baru@example.test',
            'password' => 'PasswordBaru123',
        ])->assertRedirect(route('admin.dashboard'));
    }

    private function superAdmin(): User
    {
        $user = User::factory()->create([
            'email' => 'admin@example.test',
            'password' => 'Password123',
        ]);
        $user->assignRole(Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']));

        return $user;
    }
}
