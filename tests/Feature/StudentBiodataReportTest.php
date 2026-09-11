<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\University;
use App\Models\User;
use App\Services\BiodataReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class StudentBiodataReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_bk_and_super_admin_can_view_biodata_report(): void
    {
        foreach (['bk', 'super_admin'] as $roleName) {
            $user = User::factory()->create();
            $user->assignRole(Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']));

            $this->actingAs($user)
                ->get(route('bk.biodata.report'))
                ->assertOk()
                ->assertSee('Laporan Biodata Siswa')
                ->assertSee('Rekap Detail Siswa');
        }
    }

    public function test_student_cannot_view_biodata_report(): void
    {
        $user = User::factory()->create();
        $user->assignRole(Role::firstOrCreate(['name' => 'siswa', 'guard_name' => 'web']));

        $this->actingAs($user)
            ->get(route('bk.biodata.report'))
            ->assertForbidden();
    }

    public function test_report_aggregates_complete_and_incomplete_students(): void
    {
        $complete = Student::create(['nis' => 'R-001', 'name' => 'Siswa Lengkap']);
        $complete->profile()->create($this->completeProfile());

        $incomplete = Student::create(['nis' => 'R-002', 'name' => 'Siswa Belum Lengkap']);
        $incomplete->profile()->create(['gender' => 'female', 'mcu_status' => 'belum']);

        $report = app(BiodataReportService::class)->generate([]);

        $this->assertSame(2, $report['summary']['total']);
        $this->assertSame(1, $report['summary']['complete']);
        $this->assertSame(1, $report['summary']['incomplete']);
        $this->assertSame(55, $report['summary']['average_progress']);
        $this->assertSame(20, $report['rows']->firstWhere('student.id', $incomplete->id)['missing']->count());
        $this->assertSame(['Jawa Barat'], $report['charts']['province']['labels']);
        $this->assertSame([1], $report['charts']['province']['values']);
        $this->assertSame(['Bandung'], $report['charts']['city']['labels']);
        $this->assertSame([1], $report['charts']['city']['values']);
    }

    public function test_report_filters_students_by_completeness_and_mcu_status(): void
    {
        $complete = Student::create(['nis' => 'R-003', 'name' => 'MCU Selesai']);
        $complete->profile()->create($this->completeProfile());

        $incomplete = Student::create(['nis' => 'R-004', 'name' => 'MCU Belum']);
        $incomplete->profile()->create(['gender' => 'male', 'mcu_status' => 'belum']);

        $report = app(BiodataReportService::class)->generate([
            'completeness' => 'complete',
            'mcu_status' => 'sudah',
        ]);

        $this->assertSame(1, $report['summary']['total']);
        $this->assertSame('MCU Selesai', $report['rows']->first()['student']->name);
    }

    private function completeProfile(): array
    {
        return [
            'gender' => 'male',
            'birth_place' => 'Bandung',
            'birth_date' => '2008-01-01',
            'phone' => '08123456789',
            'province' => 'Jawa Barat',
            'city' => 'Bandung',
            'district' => 'Coblong',
            'village' => 'Dago',
            'postal_code' => '40135',
            'address' => 'Jalan Test',
            'height_cm' => 170,
            'weight_kg' => 60,
            'medical_history' => '-',
            'university_choice_1_id' => University::firstOrCreate(
                ['name' => 'Universitas Indonesia'],
                ['short_name' => 'UI', 'type' => 'negeri', 'is_active' => true]
            )->id,
            'university_choice_2_id' => University::firstOrCreate(
                ['name' => 'Institut Teknologi Bandung'],
                ['short_name' => 'ITB', 'type' => 'negeri', 'is_active' => true]
            )->id,
            'grade_11_preparation' => 'Belajar rutin',
            'career_concern' => 'Persaingan masuk kampus',
            'school_achievements' => '-',
            'organization_status' => 'ya',
            'organization_name' => 'OSIS',
            'self_improvement_notes' => 'Meningkatkan disiplin',
            'mcu_status' => 'sudah',
        ];
    }
}
