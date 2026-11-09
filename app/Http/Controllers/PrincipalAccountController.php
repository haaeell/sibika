<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PrincipalAccountController extends Controller
{
    public function index(): View
    {
        return view('admin.principals.index', ['users' => User::role('kepala_sekolah')->orderBy('name')->get()]);
    }

    public function create(): View
    {
        return view('admin.principals.form', ['user' => null]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
        ]);
        DB::transaction(function () use ($data): void {
            $user = User::create($data);
            $role = Role::firstOrCreate(['name' => 'kepala_sekolah', 'guard_name' => 'web']);
            $user->assignRole($role);
            foreach (['pengasuhan.absensi', 'pengasuhan.pelanggaran'] as $permission) {
                $role->givePermissionTo(Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']));
            }
        });

        return redirect()->route('admin.principals.index')->with('success', 'Akun kepala sekolah dibuat.');
    }

    public function edit(User $user): View
    {
        abort_unless($user->hasRole('kepala_sekolah'), 404);

        return view('admin.principals.form', compact('user'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        abort_unless($user->hasRole('kepala_sekolah'), 404);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'password' => ['nullable', 'string', 'min:8'],
        ]);
        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }
        $user->update($data);

        return redirect()->route('admin.principals.index')->with('success', 'Akun kepala sekolah diperbarui.');
    }

    public function destroy(User $user): RedirectResponse
    {
        abort_unless($user->hasRole('kepala_sekolah'), 404);
        $user->delete();

        return back()->with('success', 'Akun kepala sekolah dihapus.');
    }
}
