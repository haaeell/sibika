<?php

namespace Tests\Feature;

use App\Models\Cohort;
use App\Models\GraduationAnnouncement;
use App\Models\Student;
use App\Models\User;
use Database\Seeders\EligibleTestSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class GraduationAnnouncementTest extends TestCase
{
    use RefreshDatabase;

    public function test_schedule_privacy_and_certificate_require_complete_student_and_school_data(): void
    {
        Storage::fake('local');
        $this->seed(EligibleTestSeeder::class);
        $bk = User::factory()->create();
        $bk->assignRole(Role::firstOrCreate(['name' => 'bk', 'guard_name' => 'web']));
        $cohort = Cohort::where('name', 'Angkatan Uji Eligible 2026')->firstOrFail();
        $student = Student::where('cohort_id', $cohort->id)->firstOrFail();

        $this->actingAs($student->user)->get(route('siswa.graduation.index'))->assertOk()->assertSee('Menunggu pengumuman');
        $this->get(route('siswa.graduation.certificate'))->assertNotFound();
        $this->get(route('bk.graduation.index'))->assertForbidden();

        $scheduledAt = now()->addDay();
        $this->actingAs($bk)->post(route('bk.graduation.store'), [
            'cohort_id' => $cohort->id,
            'published_at' => $scheduledAt->format('Y-m-d H:i:s'),
            'is_active' => '1',
        ])->assertSessionHas('success');
        $this->actingAs($student->user)->get(route('siswa.graduation.index'))
            ->assertSee('Menunggu pengumuman')
            ->assertSee($scheduledAt->locale('id')->translatedFormat('j F Y, H.i').' WIB')
            ->assertDontSee('DINYATAKAN LULUS');
        $this->get(route('siswa.graduation.certificate'))->assertNotFound();

        $this->actingAs($bk)->post(route('bk.graduation.store'), [
            'cohort_id' => $cohort->id,
            'published_at' => now()->subMinute()->format('Y-m-d H:i:s'),
            'is_active' => '1',
        ])->assertSessionHas('success');
        $this->actingAs($student->user)->get(route('siswa.graduation.index'))->assertOk()->assertSee('LULUS')->assertSee('Surat belum tersedia');
        $this->get(route('siswa.graduation.certificate'))->assertNotFound();

        $this->actingAs($bk)->post(route('bk.graduation.store'), [
            'cohort_id' => $cohort->id,
            'published_at' => now()->subMinute()->format('Y-m-d H:i:s'),
            'is_active' => '1',
            'meeting_date' => '2026-05-04',
            'issued_place' => 'Kab. Subang',
            'principal_name' => 'Kepala Sekolah Uji',
            'signature' => UploadedFile::fake()->image('signature.png'),
            'stamp' => UploadedFile::fake()->image('stamp.png'),
        ])->assertSessionHas('success');
        $this->get(route('bk.graduation.index', ['cohort_id' => $cohort->id]))
            ->assertOk()->assertSee('Alya Pratama')
            ->assertSee('berkas pengesahan yang dipilih berhasil disimpan')
            ->assertSee('Pratinjau tanda tangan kepala sekolah')
            ->assertSee('Pratinjau cap sekolah');
        foreach (['signature', 'stamp'] as $type) {
            $this->get(route('bk.graduation.attachment', ['cohort' => $cohort, 'type' => $type]))
                ->assertOk()->assertHeader('content-type', 'image/png');
        }
        $savedAnnouncement = GraduationAnnouncement::where('cohort_id', $cohort->id)->firstOrFail();
        $savedStamp = $savedAnnouncement->stamp_path;
        $savedSignature = $savedAnnouncement->signature_path;
        $this->post(route('bk.graduation.store'), [
            'cohort_id' => $cohort->id,
            'published_at' => now()->subMinute()->format('Y-m-d H:i:s'),
            'is_active' => '1',
            'stamp' => UploadedFile::fake()->image('cap-terlalu-besar.png')->size(2200),
        ])->assertSessionHasErrors('stamp');
        $this->get(route('bk.graduation.index', ['cohort_id' => $cohort->id]))
            ->assertSee('Pengaturan belum tersimpan')
            ->assertSee('Ukuran cap sekolah maksimal 2 MB');
        $this->assertSame($savedStamp, GraduationAnnouncement::where('cohort_id', $cohort->id)->firstOrFail()->stamp_path);
        $this->post(route('bk.graduation.store'), [
            'cohort_id' => $cohort->id,
            'published_at' => now()->subMinute()->format('Y-m-d H:i:s'),
            'is_active' => '1',
            'signature' => UploadedFile::fake()->image('signature-baru.webp'),
            'stamp' => UploadedFile::fake()->image('cap-baru.webp'),
        ])->assertSessionHas('success');
        $updatedAnnouncement = GraduationAnnouncement::where('cohort_id', $cohort->id)->firstOrFail();
        $this->assertNotSame($savedSignature, $updatedAnnouncement->signature_path);
        $this->assertNotSame($savedStamp, $updatedAnnouncement->stamp_path);
        $this->assertStringEndsWith('.webp', $updatedAnnouncement->signature_path);
        $this->assertStringEndsWith('.webp', $updatedAnnouncement->stamp_path);
        Storage::disk('local')->assertExists([$updatedAnnouncement->signature_path, $updatedAnnouncement->stamp_path]);
        Storage::disk('local')->assertMissing([$savedSignature, $savedStamp]);
        foreach (['signature', 'stamp'] as $type) {
            $this->get(route('bk.graduation.attachment', ['cohort' => $cohort, 'type' => $type]))
                ->assertOk()->assertHeader('content-type', 'image/webp');
        }
        $this->get(route('bk.graduation.export', ['cohort_id' => $cohort->id, 'format' => 'pdf']))->assertOk()->assertHeader('content-type', 'application/pdf');
        $this->get(route('bk.graduation.export', ['cohort_id' => $cohort->id, 'format' => 'xlsx']))->assertOk();
        $this->actingAs($student->user)->get(route('siswa.graduation.index'))
            ->assertSee('Unduh Surat Keterangan Lulus')
            ->assertSee('Jakarta, 1 Januari 2008');
        $this->get(route('bk.graduation.attachment', ['cohort' => $cohort, 'type' => 'signature']))->assertForbidden();
        $this->get(route('bk.graduation.export', ['cohort_id' => $cohort->id, 'format' => 'pdf']))->assertForbidden();
        $this->get(route('siswa.graduation.certificate'))->assertOk()->assertHeader('content-type', 'application/pdf');
        $this->actingAs($bk)->get(route('siswa.graduation.certificate'))->assertForbidden();

        $student->profile()->update(['birth_place' => null]);
        $this->actingAs($student->user)->get(route('siswa.graduation.index'))->assertSee('Surat belum tersedia');
        $this->get(route('siswa.graduation.certificate'))->assertNotFound();
        $student->profile()->update(['birth_place' => 'Jakarta']);

        $student->scores()->where('semester_number', 5)->delete();
        $this->actingAs($student->user)->get(route('siswa.graduation.index'))->assertSee('LULUS')->assertSee('Surat belum tersedia');
        $this->get(route('siswa.graduation.certificate'))->assertNotFound();

        $this->actingAs($bk)->post(route('bk.graduation.store'), [
            'cohort_id' => $cohort->id,
            'published_at' => now()->subMinute()->format('Y-m-d H:i:s'),
            'is_active' => '0',
        ])->assertSessionHas('success');
        $this->actingAs($student->user)->get(route('siswa.graduation.index'))->assertSee('Menunggu pengumuman');
        $this->get(route('siswa.graduation.certificate'))->assertNotFound();
    }
}
