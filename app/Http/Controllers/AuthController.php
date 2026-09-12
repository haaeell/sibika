<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $login = trim($credentials['login']);
        $email = filter_var($login, FILTER_VALIDATE_EMAIL)
            ? $login
            : Student::where('nis', $login)->orWhere('nisn', $login)->with('user')->first()?->user?->email;

        if (! $email || ! Auth::attempt(['email' => $email, 'password' => $credentials['password']], $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'login' => 'Email, NIS, NISN, atau password salah.',
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
