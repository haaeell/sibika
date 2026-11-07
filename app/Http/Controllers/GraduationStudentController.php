<?php

namespace App\Http\Controllers;

use App\Models\GraduationAnnouncement;
use App\Models\LoginSetting;
use App\Models\Student;
use App\Services\GraduationService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class GraduationStudentController extends Controller
{
    public function __construct(private readonly GraduationService $graduation) {}

    public function index(Request $request): View
    {
        $student = Student::where('user_id', $request->user()->id)->firstOrFail();
        $announcement = $student->cohort_id ? GraduationAnnouncement::where('cohort_id', $student->cohort_id)->first() : null;
        $visible = $this->graduation->visible($announcement);
        $row = $visible ? $this->graduation->row($student) : null;
        $certificateReady = $visible && $row['certificate_ready'] && $this->graduation->certificateConfigured($announcement);

        return view('siswa.graduation.index', compact('student', 'announcement', 'visible', 'row', 'certificateReady'));
    }

    public function certificate(Request $request): Response
    {
        $student = Student::where('user_id', $request->user()->id)->firstOrFail();
        $announcement = $student->cohort_id ? GraduationAnnouncement::where('cohort_id', $student->cohort_id)->first() : null;
        abort_unless($this->graduation->visible($announcement), 404);
        $row = $this->graduation->row($student);
        abort_unless($row['certificate_ready'] && $this->graduation->certificateConfigured($announcement), 404);
        $student->loadMissing(['tkaSelections.tkaSubject', 'cohort']);

        $image = function (string $path): string {
            $disk = Storage::disk('local');
            abort_unless($disk->exists($path), 404);
            $type = match (strtolower(pathinfo($path, PATHINFO_EXTENSION))) {
                'png' => 'png',
                'jpg', 'jpeg' => 'jpeg',
                'webp' => 'webp',
                default => abort(404),
            };

            return 'data:image/'.$type.';base64,'.base64_encode($disk->get($path));
        };
        $referenceImage = fn (string $name): string => 'data:image/jpeg;base64,'.base64_encode(
            file_get_contents(public_path('images/graduation/'.$name))
        );
        $graduationYear = $student->cohort?->graduation_year ?: $announcement->meeting_date->year;

        return Pdf::loadView('siswa.graduation.certificate', [
            'student' => $student,
            'announcement' => $announcement,
            'row' => $row,
            'schoolName' => LoginSetting::current()->school_name,
            'schoolYear' => ($graduationYear - 1).'/'.$graduationYear,
            'chosenSubjects' => $student->tkaSelections->pluck('tkaSubject.name')->filter()->implode(', '),
            'kop' => $referenceImage('kop.jpeg'),
            'footer' => $referenceImage('footer.jpeg'),
            'ttd' => $image($announcement->signature_path),
            'cap' => $image($announcement->stamp_path),
        ])->setPaper('A4', 'portrait')->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => false,
            'defaultFont' => 'DejaVu Sans',
            'dpi' => 96,
        ])->download('Surat_Kelulusan_'.$student->nis.'.pdf');
    }
}
