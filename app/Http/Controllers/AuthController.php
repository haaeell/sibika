<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'login' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt([
            'email' => $credentials['login'],
            'password' => $credentials['password'],
        ], $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'login' => 'Email atau password salah.',
            ]);
        }

        $request->session()->regenerate();

        return redirect()->intended($this->dashboardRoute());
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    private function dashboardRoute(): string
    {
        $user = Auth::user();

        return match (true) {
            $user->hasRole('super_admin') => route('admin.dashboard'),
            $user->hasRole('bk') => route('bk.dashboard'),
            $user->hasRole('guru') => route('guru.dashboard'),
            $user->hasRole('wali_kelas') => route('wali-kelas.dashboard'),
            $user->hasRole('siswa') => route('siswa.dashboard'),
            default => route('dashboard'),
        };
    }
}
