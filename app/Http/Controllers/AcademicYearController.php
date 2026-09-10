<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAcademicYearRequest;
use App\Http\Requests\UpdateAcademicYearRequest;
use App\Models\AcademicYear;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AcademicYearController extends Controller
{
    public function index(): View
    {
        return view('bk.academic-years.index', [
            'academicYears' => AcademicYear::latest('is_active')->latest()->paginate(10),
        ]);
    }

    public function create(): View
    {
        return view('bk.academic-years.create', [
            'academicYear' => new AcademicYear(),
        ]);
    }

    public function store(StoreAcademicYearRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active');

        $this->deactivateOthersIfNeeded($data['is_active']);
        AcademicYear::create($data);

        return redirect()->route('bk.academic-years.index')->with('success', 'Tahun ajaran berhasil ditambahkan.');
    }

    public function edit(AcademicYear $academicYear): View
    {
        return view('bk.academic-years.edit', compact('academicYear'));
    }

    public function update(UpdateAcademicYearRequest $request, AcademicYear $academicYear): RedirectResponse
    {
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active');

        $this->deactivateOthersIfNeeded($data['is_active'], $academicYear);
        $academicYear->update($data);

        return redirect()->route('bk.academic-years.index')->with('success', 'Tahun ajaran berhasil diperbarui.');
    }

    public function destroy(AcademicYear $academicYear): RedirectResponse
    {
        $academicYear->delete();

        return redirect()->route('bk.academic-years.index')->with('success', 'Tahun ajaran berhasil dihapus.');
    }

    private function deactivateOthersIfNeeded(bool $isActive, ?AcademicYear $current = null): void
    {
        if (! $isActive) {
            return;
        }

        AcademicYear::when($current, fn ($query) => $query->whereKeyNot($current->getKey()))
            ->update(['is_active' => false]);
    }
}
