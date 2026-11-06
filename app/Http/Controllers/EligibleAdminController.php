<?php

namespace App\Http\Controllers;

use App\Models\Cohort;
use App\Models\EligibleAnnouncement;
use App\Models\EligibleResult;
use App\Services\EligibleRankingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class EligibleAdminController extends Controller
{
    public function __construct(private readonly EligibleRankingService $ranking) {}

    public function index(Request $request): View
    {
        $cohorts = Cohort::orderByDesc('entry_year')->get();
        $cohort = Cohort::find($request->integer('cohort_id')) ?? $cohorts->first();
        $announcement = $cohort ? EligibleAnnouncement::where('cohort_id', $cohort->id)->first() : null;
        $rows = $announcement
            ? $announcement->results()->with('student.schoolClass')->orderByRaw('CASE WHEN `rank` IS NULL THEN 1 ELSE 0 END')->orderBy('rank')->orderBy('id')->get()
            : ($cohort ? $this->ranking->forCohort($cohort->id) : collect());

        return view('bk.eligible.index', compact('cohorts', 'cohort', 'announcement', 'rows'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'cohort_id' => ['required', 'integer', 'exists:cohorts,id'],
            'eligible_quota' => ['required', 'integer', 'min:0'],
            'reserve_quota' => ['required', 'integer', 'min:0'],
            'published_at' => ['required', 'date'],
            'eligible_wa_url' => ['nullable', 'url:http,https', 'max:255'],
            'reserve_wa_url' => ['nullable', 'url:http,https', 'max:255'],
        ]);

        DB::transaction(function () use ($data): void {
            $cohort = Cohort::whereKey($data['cohort_id'])->lockForUpdate()->firstOrFail();
            $announcement = EligibleAnnouncement::where('cohort_id', $cohort->id)->lockForUpdate()->first();
            if ($announcement) {
                $rankedCount = $announcement->results()->whereNotNull('rank')->count();
                if ($rankedCount < $data['eligible_quota'] + $data['reserve_quota']) {
                    throw ValidationException::withMessages(['eligible_quota' => 'Total kuota tidak boleh melebihi jumlah siswa dengan nilai lengkap ('.$rankedCount.').']);
                }
                $announcement->update($data);
                $this->recategorize($announcement);

                return;
            }

            if (now()->greaterThanOrEqualTo($data['published_at'])) {
                throw ValidationException::withMessages(['published_at' => 'Jadwal tayang harus di masa depan.']);
            }

            $rows = $this->ranking->forCohort($cohort->id);
            $rankedCount = $rows->whereNotNull('rank')->count();
            if ($rankedCount === 0 || $rankedCount < $data['eligible_quota'] + $data['reserve_quota']) {
                throw ValidationException::withMessages(['eligible_quota' => 'Total kuota tidak boleh melebihi jumlah siswa dengan nilai lengkap ('.$rankedCount.').']);
            }

            $announcement = new EligibleAnnouncement(['cohort_id' => $cohort->id]);
            $announcement->fill($data);
            $announcement->save();
            foreach ($rows as $row) {
                $announcement->results()->create([
                    'student_id' => $row['student']->id,
                    'rank' => $row['rank'],
                    'initial_rank' => $row['rank'],
                    'average' => $row['average'],
                    'total_score' => $row['total_score'],
                    'category' => EligibleRankingService::category($row['rank'], $data['eligible_quota'], $data['reserve_quota']),
                ]);
            }
        });

        return redirect()->route('bk.eligible.index', ['cohort_id' => $data['cohort_id']])->with('success', 'Hasil pengumuman berhasil disimpan.');
    }

    public function move(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'cohort_id' => ['required', 'integer', 'exists:cohorts,id'],
            'student_id' => ['required', 'integer', 'exists:students,id'],
            'rank' => ['required', 'integer', 'min:1'],
        ]);

        DB::transaction(function () use ($data): void {
            Cohort::whereKey($data['cohort_id'])->lockForUpdate()->firstOrFail();
            $announcement = EligibleAnnouncement::where('cohort_id', $data['cohort_id'])->firstOrFail();
            $results = $announcement->results()->whereNotNull('rank')->orderBy('rank')->lockForUpdate()->get();
            $moving = $results->firstWhere('student_id', $data['student_id']);
            if (! $moving || $data['rank'] > $results->count()) {
                throw ValidationException::withMessages(['rank' => 'Pilih siswa dengan nilai lengkap dan peringkat yang tersedia.']);
            }

            $ordered = $results->reject(fn (EligibleResult $result) => $result->id === $moving->id)->values();
            $ordered->splice($data['rank'] - 1, 0, [$moving]);
            foreach ($ordered as $index => $result) {
                if ($result->rank !== $index + 1) {
                    $result->update(['rank' => $index + 1]);
                }
            }
            $this->recategorize($announcement);
        });

        return redirect()->route('bk.eligible.index', ['cohort_id' => $data['cohort_id']])->with('success', 'Peringkat dan kategori diperbarui.');
    }

    public function reset(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'cohort_id' => ['required', 'integer', 'exists:cohorts,id'],
        ]);

        DB::transaction(function () use ($data): void {
            Cohort::whereKey($data['cohort_id'])->lockForUpdate()->firstOrFail();
            $announcement = EligibleAnnouncement::where('cohort_id', $data['cohort_id'])->firstOrFail();
            foreach ($announcement->results()->lockForUpdate()->get() as $result) {
                if ($result->rank !== $result->initial_rank) {
                    $result->update(['rank' => $result->initial_rank]);
                }
            }
            $this->recategorize($announcement);
        });

        return redirect()->route('bk.eligible.index', ['cohort_id' => $data['cohort_id']])->with('success', 'Peringkat dikembalikan ke hasil awal.');
    }

    private function recategorize(EligibleAnnouncement $announcement): void
    {
        foreach ($announcement->results()->get() as $result) {
            $category = EligibleRankingService::category($result->rank, $announcement->eligible_quota, $announcement->reserve_quota);
            if ($result->category !== $category) {
                $result->update(['category' => $category, 'response' => null]);
            }
        }
    }

    public function updateLinks(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'eligible_wa_url' => ['nullable', 'url:http,https', 'max:255'],
            'reserve_wa_url' => ['nullable', 'url:http,https', 'max:255'],
        ]);
        $announcement = EligibleAnnouncement::where('cohort_id', $request->integer('cohort_id'))->firstOrFail();
        $announcement->update($data);

        return back()->with('success', 'Tautan grup WA diperbarui.');
    }
}
