<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class StudentImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_import_batch_creates_ten_hashed_student_accounts(): void
    {
        $user = User::factory()->create();
        $user->assignRole(Role::firstOrCreate(['name' => 'bk', 'guard_name' => 'web']));
        Role::firstOrCreate(['name' => 'siswa', 'guard_name' => 'web']);
        $token = str_repeat('a', 40);
        $rows = collect(range(1, 10))->map(fn (int $number) => ["NIS-{$number}", '', "Siswa {$number}", '', '', 'active'])->all();
        Cache::put("student-import:{$user->id}:{$token}", [
            'rows' => $rows,
            'header_offset' => 1,
            'success' => 0,
            'updated' => 0,
            'failures' => [],
        ], now()->addMinutes(30));

        $response = $this->actingAs($user)
            ->postJson(route('bk.students.import.batch'), ['token' => $token, 'offset' => 0])
            ->assertOk()
            ->assertJsonPath('done', true)
            ->assertJsonPath('processed', 10);

        $this->assertSame(10, $response->json('success'), json_encode($response->json('failures')));

        $student = Student::where('nis', 'NIS-1')->firstOrFail();
        $this->assertTrue(Hash::check('NIS-1', $student->user->password));
    }
}
