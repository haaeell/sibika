@component('layouts.app', ['title' => 'Detail Nilai'])
    <x-page-header title="Detail Nilai" description="{{ $student->name }} - {{ $student->nis }}">
        <x-slot:actions><x-button variant="secondary" :href="route('bk.student-scores.index')"><i class="fa-solid fa-arrow-left"></i> Kembali</x-button></x-slot:actions>
    </x-page-header>

    <x-card>
        <div class="grid gap-4 md:grid-cols-3">
            <div><p class="text-xs font-bold uppercase text-slate-400">Siswa</p><p class="mt-1 font-extrabold text-slate-900">{{ $student->name }}</p></div>
            <div><p class="text-xs font-bold uppercase text-slate-400">Kelas</p><p class="mt-1 font-extrabold text-slate-900">{{ $student->schoolClass?->name ?? '-' }}</p></div>
            <div><p class="text-xs font-bold uppercase text-slate-400">Jurusan</p><p class="mt-1 font-extrabold text-slate-900">{{ $student->schoolClass?->major?->name ?? '-' }}</p></div>
        </div>
    </x-card>

    <div class="space-y-4">
        @foreach ($semesters as $semester => $data)
            <x-card>
                <h2 class="mb-4 text-lg font-extrabold text-slate-900">Semester {{ $semester }}</h2>
                @if ($data['settings']->isEmpty())
                    <x-empty-state icon="fa-solid fa-chart-line" title="Setting belum tersedia" description="Belum ada mapel untuk semester ini." />
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[760px] text-left text-sm">
                            <thead class="border-b border-slate-200 text-xs uppercase text-slate-500"><tr><th class="px-4 py-3 font-bold">Mapel</th><th class="px-4 py-3 font-bold">Nilai</th><th class="px-4 py-3 font-bold">Status</th><th class="px-4 py-3 font-bold">Catatan</th></tr></thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach ($data['settings'] as $setting)
                                    @php $score = $data['scores']->get($setting->subject_id); @endphp
                                    <tr>
                                        <td class="px-4 py-3 font-bold text-slate-900">{{ $setting->subject?->name }}</td>
                                        <td class="px-4 py-3">{{ filled($score?->score) ? number_format((float) $score->score, 2) : '-' }}</td>
                                        <td class="px-4 py-3">{{ $score?->status ? str($score->status)->headline() : 'Belum Diisi' }}</td>
                                        <td class="px-4 py-3 text-slate-500">{{ $score?->verification_note ?? '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </x-card>
        @endforeach
    </div>
@endcomponent
