<?php

namespace App\Http\Controllers;

use App\Models\Cohort;
use App\Models\GraduationAnnouncement;
use App\Models\Student;
use App\Services\EligibleRankingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class GraduationAdminController extends Controller
{
    public function __construct(private readonly EligibleRankingService $ranking) {}

    public function index(Request $request): View
    {
        $cohorts = Cohort::orderByDesc('entry_year')->get();
        $cohort = Cohort::find($request->integer('cohort_id')) ?? $cohorts->first();
        $announcement = $cohort ? GraduationAnnouncement::where('cohort_id', $cohort->id)->first() : null;
        $attachmentAvailable = [];
        foreach (['signature', 'stamp'] as $type) {
            $path = $announcement?->{$type.'_path'};
            $attachmentAvailable[$type] = $path && Storage::disk('local')->exists($path);
        }
        $rankings = $cohort ? $this->ranking->forCohort($cohort->id)->keyBy(fn (array $row) => $row['student']->id) : collect();
        $students = $cohort ? Student::where('cohort_id', $cohort->id)->with(['profile', 'parents', 'schoolClass'])->orderBy('name')->get() : collect();

        return view('bk.graduation.index', compact('cohorts', 'cohort', 'announcement', 'attachmentAvailable', 'rankings', 'students'));
    }

    public function attachment(Cohort $cohort, string $type): Response
    {
        abort_unless(in_array($type, ['signature', 'stamp'], true), 404);
        $announcement = GraduationAnnouncement::where('cohort_id', $cohort->id)->firstOrFail();
        $path = $announcement->{$type.'_path'};
        abort_unless($path && Storage::disk('local')->exists($path), 404);
        $mime = match (strtolower(pathinfo($path, PATHINFO_EXTENSION))) {
            'png' => 'image/png',
            'jpg', 'jpeg' => 'image/jpeg',
            'webp' => 'image/webp',
            default => abort(404),
        };

        return response(Storage::disk('local')->get($path), 200, [
            'Content-Type' => $mime,
            'Cache-Control' => 'private, no-store, max-age=0',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'cohort_id' => ['required', 'integer', 'exists:cohorts,id'],
            'published_at' => ['required', 'date'],
            'is_active' => ['required', 'boolean'],
            'meeting_date' => ['nullable', 'date'],
            'issued_place' => ['nullable', 'string', 'max:255'],
            'principal_name' => ['nullable', 'string', 'max:255'],
            'principal_nip' => ['nullable', 'string', 'max:100'],
            'signature' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
            'stamp' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
        ], [
            'signature.uploaded' => 'Tanda tangan gagal diunggah. Pastikan ukuran berkas tidak melebihi 2 MB.',
            'stamp.uploaded' => 'Cap sekolah gagal diunggah. Pastikan ukuran berkas tidak melebihi 2 MB.',
            'signature.max' => 'Ukuran tanda tangan maksimal 2 MB.',
            'stamp.max' => 'Ukuran cap sekolah maksimal 2 MB.',
            'signature.image' => 'Tanda tangan harus berupa gambar PNG, JPG, atau WebP.',
            'stamp.image' => 'Cap sekolah harus berupa gambar PNG, JPG, atau WebP.',
            'signature.mimes' => 'Tanda tangan harus berupa gambar PNG, JPG, atau WebP.',
            'stamp.mimes' => 'Cap sekolah harus berupa gambar PNG, JPG, atau WebP.',
        ]);
        $announcement = GraduationAnnouncement::firstOrNew(['cohort_id' => $data['cohort_id']]);
        $oldPaths = [];
        $newPaths = [];
        foreach (['signature', 'stamp'] as $field) {
            if ($request->hasFile($field)) {
                $path = $request->file($field)->store('graduation', 'local');
                $newPaths[] = $path;
                if ($announcement->{$field.'_path'}) {
                    $oldPaths[] = $announcement->{$field.'_path'};
                }
                $data[$field.'_path'] = $path;
            }
        }
        try {
            $announcement->fill($data)->save();
        } catch (\Throwable $e) {
            Storage::disk('local')->delete($newPaths);
            throw $e;
        }
        Storage::disk('local')->delete($oldPaths);

        $message = $newPaths
            ? 'Pengaturan kelulusan dan berkas pengesahan yang dipilih berhasil disimpan.'
            : 'Pengaturan kelulusan berhasil disimpan.';

        return redirect()->route('bk.graduation.index', ['cohort_id' => $announcement->cohort_id])->with('success', $message);
    }
}
