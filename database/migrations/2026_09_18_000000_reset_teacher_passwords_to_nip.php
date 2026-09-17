<?php

use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Teacher::query()->orderBy('id')->each(function (Teacher $teacher): void {
            $user = $teacher->user_id ? User::find($teacher->user_id) : User::where('email', $teacher->email)->first();

            $user?->update(['password' => $teacher->code, 'must_change_password' => true]);
        });
    }

    public function down(): void
    {
        // Password lama tidak dapat dipulihkan dari hash.
    }
};
