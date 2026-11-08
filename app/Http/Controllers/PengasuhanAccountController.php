<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PengasuhanAccountController extends Controller
{
    public function index(): View
    {
        $role = Role::where('name', 'pengasuhan')->where('guard_name', 'web')->first();
        $users = $role ? $role->users()->latest()->get() : collect();

        return view('pengasuhan.index', compact('users'));
    }

    public function create(): View
    {
        $permissions = collect(['pengasuhan.absensi', 'pengasuhan.pelanggaran'])
            ->map(fn (string $name) => Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']));
        $user = null;

        return view('pengasuhan.form', compact('permissions', 'user'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['in:pengasuhan.absensi,pengasuhan.pelanggaran'],
        ]);

        DB::transaction(function () use ($data): void {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => bcrypt($data['password']),
            ]);

            $user->assignRole(Role::firstOrCreate(['name' => 'pengasuhan', 'guard_name' => 'web']));
            $user->givePermissionTo(array_map(
                fn (string $name) => Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']),
                $data['permissions'] ?? []
            ));
        });

        return redirect()->route('admin.pengasuhan-accounts.index')->with('success', 'Akun pengasuhan berhasil dibuat.');
    }

    public function edit(User $user): View
    {
        abort_unless($user->hasRole('pengasuhan'), 403);

        $permissions = Permission::whereIn('name', ['pengasuhan.absensi', 'pengasuhan.pelanggaran'])->orderBy('name')->get();
        $selected = $user->permissions->pluck('name')->toArray();

        return view('pengasuhan.form', compact('user', 'permissions', 'selected'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        abort_unless($user->hasRole('pengasuhan'), 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'password' => ['nullable', 'string', 'min:8'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['in:pengasuhan.absensi,pengasuhan.pelanggaran'],
        ]);

        $user->update([
            'name' => $data['name'],
            'email' => $data['email'],
        ] + (filled($data['password']) ? ['password' => bcrypt($data['password'])] : []));

        $user->syncPermissions($data['permissions'] ?? []);

        return redirect()->route('admin.pengasuhan-accounts.index')->with('success', 'Akun pengasuhan berhasil diperbarui.');
    }

    public function destroy(User $user): RedirectResponse
    {
        abort_unless($user->hasRole('pengasuhan'), 403);

        $user->delete();

        return back()->with('success', 'Akun pengasuhan berhasil dihapus.');
    }
}
