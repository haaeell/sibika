<?php

namespace App\Support;

use App\Models\User;

/**
 * Single source of truth pemetaan dashboard per role.
 * Dipakai saat login, route /dashboard, dan menu sidebar.
 */
class DashboardRedirector
{
    public static function for(User $user): string
    {
        return match (true) {
            $user->hasRole('super_admin') => route('admin.dashboard'),
            $user->hasRole('bk') => route('bk.dashboard'),
            $user->hasRole('kepala_sekolah') => route('bk.dashboard'),
            $user->hasRole('pengasuhan') && ! $user->can('pengasuhan.absensi') && $user->can('pengasuhan.pelanggaran') => route('bk.violations.index'),
            $user->hasRole('pengasuhan') => route('bk.attendance.index'),
            $user->hasRole('wali_kelas') => route('wali-kelas.dashboard'),
            $user->hasRole('guru') => route('guru.dashboard'),
            $user->hasRole('siswa') => route('siswa.dashboard'),
            default => route('dashboard'),
        };
    }
}
