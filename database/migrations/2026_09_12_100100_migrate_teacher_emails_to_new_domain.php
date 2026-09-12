<?php

use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public const DOMAIN = 'smaplusasthahannas.id';

    /**
     * Migrasi email guru (tabel teachers) + akun wali_kelas tertaut
     * ke domain baru, local-part tetap. Dilewati bila bentrok unique.
     */
    public function up(): void
    {
        Teacher::with('user')->chunkById(200, function ($teachers) {
            foreach ($teachers as $teacher) {
                if (! str_contains((string) $teacher->email, '@')) {
                    continue;
                }

                [$local] = explode('@', (string) $teacher->email, 2);
                $email = strtolower(trim($local)).'@'.self::DOMAIN;

                if ($teacher->email !== $email) {
                    $taken = Teacher::where('email', $email)->where('id', '!=', $teacher->id)->exists();
                    if (! $taken) {
                        $teacher->update(['email' => $email]);
                    }
                }

                $user = $teacher->user
                    ?? User::where('email', $teacher->getOriginal('email'))->first();
                if ($user && $user->email !== $email) {
                    $taken = User::where('email', $email)->where('id', '!=', $user->id)->exists();
                    if (! $taken) {
                        $user->update(['email' => $email]);
                    }
                }
            }
        });
    }

    public function down(): void
    {
        // Tidak dikembalikan otomatis — backup database sebelum migrate.
    }
};
