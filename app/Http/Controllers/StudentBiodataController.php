<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateOwnBiodataRequest;
use App\Models\Student;
use App\Models\StudentDocument;
use App\Models\University;
use App\Services\StudentProgressService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class StudentBiodataController extends Controller
{
    public function __construct(private readonly StudentProgressService $progressService) {}

    public function index(Request $request): View
    {
        $student = $this->studentFor($request)->load(['profile.universityChoice1', 'profile.universityChoice2', 'profile.universityChoice3', 'parents', 'documents', 'achievements.documents', 'organizations']);

        return view('siswa.biodata.index', [
            'student' => $student,
            'profile' => $student->profile,
            'parents' => $student->parents->keyBy('parent_type'),
            'progress' => $this->progressService->calculate($student),
            'universities' => University::where('is_active', true)->orderBy('type')->orderBy('name')->get()->groupBy('type'),
        ]);
    }

    public function update(UpdateOwnBiodataRequest $request): RedirectResponse
    {
        $student = $this->studentFor($request);
        $data = $request->validated();
        $achievements = $data['achievements'] ?? [];
        $organizations = $data['organizations'] ?? [];
        unset($data['photo'], $data['ijazah_smp'], $data['akte'], $data['kartu_keluarga'], $data['achievements'], $data['organizations']);
        $this->clearMajorsForGovernmentSchools($data);

        $profile = $student->profile()->updateOrCreate([], $data);
        $this->storeSubmittedFiles($request, $student, $profile);
        $this->syncAchievements($request, $student, $achievements);
        $this->syncOrganizations($student, $organizations);

        return redirect()->route('siswa.biodata.index')->with('success', 'Biodata berhasil diperbarui.');
    }

    public function uploadPhoto(Request $request): RedirectResponse
    {
        $student = $this->studentFor($request);
        $validated = $request->validate([
            'photo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $profile = $student->profile()->firstOrCreate([]);
        if ($profile->photo_path) {
            Storage::disk('local')->delete($profile->photo_path);
        }
        $profile->update(['photo_path' => $validated['photo']->store('student-photos', 'local')]);

        return back()->with('success', 'Foto profil berhasil diperbarui.');
    }

    public function photo(Request $request)
    {
        $student = $this->studentFor($request)->load('profile');
        abort_unless($student->profile?->photo_path && Storage::disk('local')->exists($student->profile->photo_path), 404);

        $response = response()->file(Storage::disk('local')->path($student->profile->photo_path));
        $response->headers->set('Cache-Control', 'private, no-store, no-cache, must-revalidate');
        $response->headers->set('Pragma', 'no-cache');

        return $response;
    }

    public function uploadDocument(Request $request): RedirectResponse
    {
        $student = $this->studentFor($request);
        $validated = $request->validate([
            'document_type' => ['required', 'string', 'max:100'],
            'documents' => ['required', 'array', 'min:1'],
            'documents.*' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'],
        ], [
            'documents.*.max' => 'Ukuran dokumen maksimal 2 MB.',
        ]);

        $labels = ['ijazah' => 'Ijazah SMP', 'ijazah_smp' => 'Ijazah SMP', 'akte' => 'Akte', 'kartu_keluarga' => 'Kartu Keluarga'];
        $documentType = $labels[$validated['document_type']] ?? $validated['document_type'];

        foreach ($validated['documents'] as $file) {
            $student->documents()->create([
                'document_type' => $documentType,
                'file_path' => $file->store('student-documents/'.$student->id, 'local'),
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
                'uploaded_by' => $request->user()->id,
            ]);
        }

        return back()->with('success', count($validated['documents']).' dokumen berhasil diunggah.');
    }

    public function destroyDocument(Request $request, StudentDocument $document): RedirectResponse
    {
        abort_unless($document->student_id === $this->studentFor($request)->id, 403);

        Storage::disk('local')->delete($document->file_path);
        $document->delete();

        return back()->with('success', 'Sertifikat berhasil dihapus.');
    }

    public function downloadDocument(Request $request, StudentDocument $document)
    {
        abort_unless($document->student_id === $this->studentFor($request)->id, 403);
        abort_unless(Storage::disk('local')->exists($document->file_path), 404);

        return Storage::disk('local')->download($document->file_path, $document->original_name);
    }

    private function studentFor(Request $request): Student
    {
        return Student::where('user_id', $request->user()->id)->firstOrFail();
    }

    public function clearMajorsForGovernmentSchools(array &$data): void
    {
        $universities = University::whereIn('id', array_filter([
            $data['university_choice_1_id'] ?? null,
            $data['university_choice_2_id'] ?? null,
            $data['university_choice_3_id'] ?? null,
        ]))->pluck('type', 'id');

        foreach ([1, 2, 3] as $choice) {
            if (($universities[$data['university_choice_'.$choice.'_id'] ?? null] ?? null) === 'kedinasan') {
                $data['university_major_choice_'.$choice] = null;
            }
        }
    }

    protected function storeSubmittedFiles(Request $request, Student $student, $profile): void
    {
        if ($request->hasFile('photo')) {
            if ($profile->photo_path) {
                Storage::disk('local')->delete($profile->photo_path);
            }
            $profile->update(['photo_path' => $request->file('photo')->store('student-photos', 'local')]);
        }

        foreach (['ijazah_smp' => 'Ijazah SMP', 'akte' => 'Akte', 'kartu_keluarga' => 'Kartu Keluarga'] as $field => $label) {
            if ($request->hasFile($field)) {
                $student->documents()->where('document_type', $label)->get()->each(function (StudentDocument $document): void {
                    Storage::disk('local')->delete($document->file_path);
                    $document->delete();
                });
                $file = $request->file($field);
                $student->documents()->create([
                    'document_type' => $label,
                    'file_path' => $file->store('student-documents/'.$student->id, 'local'),
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getMimeType(),
                    'file_size' => $file->getSize(),
                    'uploaded_by' => $request->user()->id,
                ]);
            }
        }

    }

    private function syncAchievements(Request $request, Student $student, array $achievements): void
    {
        $student->achievements()->with('documents')->get()->each(function ($achievement): void {
            $achievement->documents->each(function (StudentDocument $document): void {
                Storage::disk('local')->delete($document->file_path);
                $document->delete();
            });
            $achievement->delete();
        });

        foreach ($achievements as $index => $achievementData) {
            $hasCertificate = $request->hasFile("achievements.$index.certificate");
            $hasDetail = collect($achievementData)->except('certificate')->filter(fn ($value) => filled($value))->isNotEmpty();

            if (! $hasDetail && ! $hasCertificate) {
                continue;
            }

            $achievement = $student->achievements()->create([
                'type' => $achievementData['type'] ?? null,
                'name' => $achievementData['name'] ?? null,
                'level' => $achievementData['level'] ?? null,
                'year' => $achievementData['year'] ?? null,
            ]);

            if ($hasCertificate) {
                $file = $request->file("achievements.$index.certificate");
                $student->documents()->create([
                    'achievement_id' => $achievement->id,
                    'document_type' => 'Sertifikat Prestasi',
                    'file_path' => $file->store('student-documents/'.$student->id, 'local'),
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getMimeType(),
                    'file_size' => $file->getSize(),
                    'uploaded_by' => $request->user()->id,
                ]);
            }
        }
    }

    private function syncOrganizations(Student $student, array $organizations): void
    {
        if ($student->profile?->organization_status === 'tidak') {
            $student->organizations()->delete();
            return;
        }

        if ($student->profile?->organization_status !== 'ya') {
            return;
        }

        $student->organizations()->delete();

        foreach ($organizations as $organizationData) {
            if (collect($organizationData)->filter(fn ($value) => filled($value))->isEmpty()) {
                continue;
            }

            $student->organizations()->create([
                'name' => $organizationData['name'] ?? null,
                'position' => $organizationData['position'] ?? null,
                'level' => $organizationData['level'] ?? null,
                'year' => $organizationData['year'] ?? null,
            ]);
        }
    }
}
