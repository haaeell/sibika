<?php

namespace App\Services;

use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class StudentAccountService
{
    /**
     * Format email akun siswa: {NISN}@smaplusasthahannas.id,
     * fallback ke NIS bila NISN kosong.
     */
    public static function emailFor(?string $nisn, string $nis): string
    {
        $local = trim((string) ($nisn !== null && trim($nisn) !== '' ? $nisn : $nis));

        return strtolower($local).'@smaplusasthahannas.id';
    }

    /**
     * Buatkan atau sinkronkan akun login siswa.
     * Password awal = NIS. Flag must_change_password=true hanya untuk akun baru / reset.
     */
    public function ensureAccount(Student $student, bool $forceResetPassword = false): User
    {
        return DB::transaction(function () use ($student, $forceResetPassword) {
            $student->refresh();
            $email = self::emailFor($student->nisn, $student->nis);

            $user = $student->user_id ? User::find($student->user_id) : null;
            $user ??= User::where('email', $email)->first();

            if (! $user) {
                $user = User::create([
                    'name' => $student->name,
                    'email' => $email,
                    'password' => $student->nis, // di-hash via cast 'hashed'
                    'must_change_password' => true,
                ]);
                $user->syncRoles(['siswa']);
                $student->update(['user_id' => $user->id]);

                return $user;
            }

            $updates = [];
            if ($user->name !== $student->name) {
                $updates['name'] = $student->name;
            }
            if ($user->email !== $email) {
                $conflict = User::where('email', $email)->where('id', '!=', $user->id)->exists();
                if (! $conflict) {
                    $updates['email'] = $email;
                }
            }
            if ($forceResetPassword) {
                $updates['password'] = $student->nis;
                $updates['must_change_password'] = true;
            }
            if ($updates !== []) {
                $user->update($updates);
            }
            if (! $user->hasRole('siswa')) {
                $user->syncRoles(['siswa']);
            }
            if ($student->user_id !== $user->id) {
                $student->update(['user_id' => $user->id]);
            }

            return $user->refresh();
        });
    }

    public function resetToDefault(Student $student): User
    {
        return $this->ensureAccount($student, true);
    }
}
