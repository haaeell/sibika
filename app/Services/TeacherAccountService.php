<?php

namespace App\Services;

use App\Models\Teacher;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class TeacherAccountService
{
    public function ensureAccount(Teacher $teacher): User
    {
        return DB::transaction(function () use ($teacher) {
            $teacher->refresh();
            $user = $teacher->user_id ? User::find($teacher->user_id) : null;
            $user ??= User::where('email', $teacher->email)->first();

            if (! $user) {
                $user = User::create([
                    'name' => $teacher->name,
                    'email' => $teacher->email,
                    'password' => 'password',
                    'must_change_password' => false,
                ]);
            } else {
                $updates = ['name' => $teacher->name];
                if ($user->email !== $teacher->email && ! User::where('email', $teacher->email)->whereKeyNot($user->id)->exists()) {
                    $updates['email'] = $teacher->email;
                }
                $user->update($updates);
            }

            Role::findOrCreate('guru', 'web');
            Role::findOrCreate('wali_kelas', 'web');
            $user->assignRole('guru');
            if ($teacher->homeroomClasses()->exists()) {
                $user->assignRole('wali_kelas');
            } else {
                $user->removeRole('wali_kelas');
            }

            if ($teacher->user_id !== $user->id) {
                $teacher->update(['user_id' => $user->id]);
            }

            return $user->refresh();
        });
    }
}
