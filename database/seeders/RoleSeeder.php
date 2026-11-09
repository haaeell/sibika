<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            'super_admin',
            'bk',
            'pengasuhan',
            'kepala_sekolah',
            'wali_kelas',
            'guru',
            'siswa',
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate([
                'name' => $role,
                'guard_name' => 'web',
            ]);
        }

        $permissions = [
            'pengasuhan.absensi',
            'pengasuhan.pelanggaran',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        $bk = Role::where('name', 'bk')->where('guard_name', 'web')->first();
        if ($bk) {
            $bk->givePermissionTo($permissions);
        }

        $superAdmin = Role::where('name', 'super_admin')->where('guard_name', 'web')->first();
        if ($superAdmin) {
            $superAdmin->givePermissionTo($permissions);
        }
        Role::where('name', 'kepala_sekolah')->where('guard_name', 'web')->first()?->givePermissionTo($permissions);
    }
}
