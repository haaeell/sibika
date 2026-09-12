<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePasswordIsChanged
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->hasRole('siswa') && (bool) ($user->must_change_password ?? false)) {
            $allowed = [
                'siswa.dashboard',
                'siswa.password.update',
                'logout',
                'academic-year.select',
                'regions.index',
            ];

            if (! $request->routeIs($allowed)) {
                return redirect()->route('siswa.dashboard')
                    ->with('error', 'Demi keamanan, silakan ganti password default (NIS) Anda terlebih dahulu.');
            }
        }

        return $next($request);
    }
}
