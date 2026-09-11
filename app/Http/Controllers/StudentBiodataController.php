<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateOwnBiodataRequest;
use App\Models\Student;
use App\Models\StudentDocument;
use App\Services\StudentProgressService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class StudentBiodataController extends Controller
{
    public function __construct(private readonly StudentProgressService $progressService)
    {
    }

    public function index(Request $request): View
    {
        $student = $this->studentFor($request)->load(['profile', 'parents', 'documents']);

        return view('siswa.biodata.index', [
            'student' => $student,
            'profile' => $student->profile,
            'parents' => $student->parents->keyBy('parent_type'),
            'progress' => $this->progressService->calculate($student),
        ]);
    }

    public function update(UpdateOwnBiodataRequest $request): RedirectResponse
    {
        $student = $this->studentFor($request);
        $data = $request->validated();

        $student->profile()->updateOrCreate([], $data);

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

        return response()->file(Storage::disk('local')->path($student->profile->photo_path));
    }

    public function uploadDocument(Request $request): RedirectResponse
    {
        $student = $this->studentFor($request);
        $validated = $request->validate([
            'document_type' => ['required', 'string', 'max:100'],
            'document' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ]);

        $file = $validated['document'];
        $student->documents()->create([
            'document_type' => $validated['document_type'],
            'file_path' => $file->store('student-documents/'.$student->id, 'local'),
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'uploaded_by' => $request->user()->id,
        ]);

        return back()->with('success', 'Sertifikat prestasi berhasil diunggah.');
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
}
