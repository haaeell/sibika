<?php

namespace Tests\Feature;

use App\Models\Cohort;
use App\Models\EligibleAnnouncement;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class EligibleScheduleEditTest extends TestCase
{
    use RefreshDatabase;

    public function test_live_announcement_can_be_rescheduled_without_losing_results_or_responses(): void
    {
        $bk = User::factory()->create();
        $bk->assignRole(Role::firstOrCreate(['name' => 'bk', 'guard_name' => 'web']));
        $studentUser = User::factory()->create();
        $studentUser->assignRole(Role::firstOrCreate(['name' => 'siswa', 'guard_name' => 'web']));
        $cohort = Cohort::create(['name' => 'Angkatan Uji', 'entry_year' => 2023, 'graduation_year' => 2026, 'status' => 'active']);
        $student = Student::create(['nis' => 'SCH-001', 'name' => 'Siswa Uji', 'cohort_id' => $cohort->id, 'user_id' => $studentUser->id]);
        $announcement = EligibleAnnouncement::create([
            'cohort_id' => $cohort->id,
            'eligible_quota' => 1,
            'reserve_quota' => 0,
            'published_at' => now()->subDay(),
            'eligible_wa_url' => 'https://chat.whatsapp.com/eligible',
        ]);
        $result = $announcement->results()->create([
            'student_id' => $student->id,
            'rank' => 1,
            'initial_rank' => 1,
            'average' => 90,
            'total_score' => 450,
            'category' => 'eligible',
            'response' => 'diterima',
        ]);

        $this->actingAs($bk)->get(route('bk.eligible.index', ['cohort_id' => $cohort->id]))
            ->assertOk()
            ->assertSee('Jadwal tayang')
            ->assertDontSee('readonly', false);

        $future = now()->addDay()->format('Y-m-d\TH:i');
        $data = [
            'cohort_id' => $cohort->id,
            'eligible_quota' => 1,
            'reserve_quota' => 0,
            'published_at' => $future,
            'eligible_wa_url' => 'https://chat.whatsapp.com/eligible',
        ];
        $this->post(route('bk.eligible.store'), $data)->assertSessionHas('success');
        $this->assertSame($future, $announcement->fresh()->published_at->format('Y-m-d\TH:i'));
        $this->assertDatabaseHas('eligible_results', ['id' => $result->id, 'rank' => 1, 'response' => 'diterima']);
        $this->actingAs($studentUser)->get(route('siswa.eligible.index'))->assertOk()->assertSee('Menunggu pengumuman');

        $past = now()->subHour()->format('Y-m-d\TH:i');
        $this->actingAs($bk)->post(route('bk.eligible.store'), [...$data, 'published_at' => $past])->assertSessionHas('success');
        $this->assertSame($past, $announcement->fresh()->published_at->format('Y-m-d\TH:i'));
        $this->actingAs($studentUser)->get(route('siswa.eligible.index'))
            ->assertSee('Pilihan tersimpan: Diterima')
            ->assertSee('https://chat.whatsapp.com/eligible');
        $this->assertDatabaseHas('eligible_results', ['id' => $result->id, 'rank' => 1, 'response' => 'diterima']);
    }
}
