<?php

namespace Tests\Feature;

use App\Models\LoginSetting;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class StudentHelpWhatsappTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_sees_configured_whatsapp_help_button(): void
    {
        LoginSetting::create([...LoginSetting::defaults(), 'help_text' => 'Butuh bantuan?', 'help_whatsapp_number' => '081234567890']);
        $user = User::factory()->create();
        $user->assignRole(Role::firstOrCreate(['name' => 'siswa', 'guard_name' => 'web']));
        Student::create(['nis' => 'WA-001', 'name' => 'Siswa WA', 'user_id' => $user->id]);

        $this->actingAs($user)->get(route('siswa.scores.index'))
            ->assertOk()
            ->assertSee('Butuh bantuan?')
            ->assertSee('https://wa.me/6281234567890', false);
    }
}
