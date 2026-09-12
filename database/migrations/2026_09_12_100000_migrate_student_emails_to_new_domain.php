<?php

use App\Models\Student;
use App\Models\User;
use App\Services\StudentAccountService;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Migrasi email akun siswa ke format baru {NISN}@smaplusasthahannas.id
     * (fallback NIS bila NISN kosong). Baris yang email barunya sudah dipakai
     * akun lain dilewati agar tidak melanggar unique.
     */
    public function up(): void
    {
        Student::with('user')->chunkById(200, function ($students) {
            foreach ($students as $student) {
                $user = $student->user;
                if (! $user) {
                    continue;
                }

                $email = StudentAccountService::emailFor($student->nisn, $student->nis);
                if ($user->email === $email) {
                    continue;
                }

                $taken = User::where('email', $email)->where('id', '!=', $user->id)->exists();
                if ($taken) {
                    continue;
                }

                $user->update(['email' => $email]);
            }
        });
    }

    public function down(): void
    {
        // Tidak dikembalikan otomatis — backup database sebelum migrate.
    }
};
