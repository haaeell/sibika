<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DashboardRedirectTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_redirects_per_role(): void
    {
        $cases = [
            'super_admin' => 'admin.dashboard',
            'bk' => 'bk.dashboard',
            'guru' => 'guru.dashboard',
            'wali_kelas' => 'wali-kelas.dashboard',
            'siswa' => 'siswa.dashboard',
        ];

        foreach ($cases as $role => $route) {
            $user = User::factory()->create();
            $user->assignRole(Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']));

            $this->actingAs($user)->get(route('dashboard'))
                ->assertRedirect(route($route));
        }
    }

    public function test_dashboard_without_role_shows_placeholder(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('dashboard'))->assertOk();
    }

    public function test_guest_dashboard_redirects_to_login(): void
    {
        $this->get(route('dashboard'))->assertRedirect(route('login'));
    }
}
