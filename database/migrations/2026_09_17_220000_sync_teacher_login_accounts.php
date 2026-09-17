<?php

use App\Models\Teacher;
use App\Services\TeacherAccountService;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $accounts = app(TeacherAccountService::class);

        Teacher::query()->orderBy('id')->each(fn (Teacher $teacher) => $accounts->ensureAccount($teacher));
    }

    public function down(): void
    {
        // Login accounts are production data; never delete them on rollback.
    }
};
