<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PengasuhanAccountTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_open_form_and_create_pengasuhan_account(): void
    {
        $this->seed(RoleSeeder::class);
        $admin = User::factory()->create();
        $admin->assignRole('super_admin');

        $this->actingAs($admin)->get(route('admin.pengasuhan-accounts.create'))->assertOk();
        $this->post(route('admin.pengasuhan-accounts.store'), [
            'name' => 'Petugas Pengasuhan',
            'email' => 'pengasuhan@example.com',
            'password' => 'password123',
            'permissions' => ['pengasuhan.absensi'],
        ])->assertRedirect(route('admin.pengasuhan-accounts.index'));

        $user = User::where('email', 'pengasuhan@example.com')->firstOrFail();
        $this->assertTrue($user->hasRole('pengasuhan'));
        $this->assertTrue($user->can('pengasuhan.absensi'));
        $this->assertFalse($user->can('pengasuhan.pelanggaran'));
        $this->get(route('admin.pengasuhan-accounts.index'))->assertOk()->assertSee('pengasuhan@example.com');
    }

    public function test_account_creation_works_without_seeding_role_or_permissions(): void
    {
        $admin = User::factory()->create();
        Role::create(['name' => 'super_admin', 'guard_name' => 'web']);
        $admin->assignRole('super_admin');

        $this->actingAs($admin);
        $this->post(route('admin.pengasuhan-accounts.store'), [
            'name' => 'Petugas Baru',
            'email' => 'petugas@example.com',
            'password' => 'password123',
            'permissions' => ['pengasuhan.pelanggaran'],
        ])->assertRedirect(route('admin.pengasuhan-accounts.index'));

        $user = User::where('email', 'petugas@example.com')->firstOrFail();
        $this->assertTrue($user->hasRole('pengasuhan'));
        $this->assertTrue($user->can('pengasuhan.pelanggaran'));
        $this->assertFalse($user->can('pengasuhan.absensi'));
    }
}
