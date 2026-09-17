@component('layouts.app', ['title' => 'Detail Nilai'])
    <x-page-header title="Detail Nilai" description="{{ $student->name }} - {{ $student->nis }}">
        <x-slot:actions><x-button variant="secondary" :href="($isMonitoring ?? false) ? route('wali-kelas.scores.index') : route('bk.student-scores.index')"><i class="fa-solid fa-arrow-left"></i> Kembali</x-button></x-slot:actions>
    </x-page-header>

    <x-card>
        <div class="grid gap-4 md:grid-cols-4">
            <div><p class="text-xs font-bold uppercase text-slate-400">Siswa</p><p class="mt-1 font-extrabold text-slate-900">{{ $student->name }}</p></div>
            <div><p class="text-xs font-bold uppercase text-slate-400">Kelas</p><p class="mt-1 font-extrabold text-slate-900">{{ $student->schoolClass?->name ?? '-' }}</p></div>
            <div><p class="text-xs font-bold uppercase text-slate-400">Jurusan</p><p class="mt-1 font-extrabold text-slate-900">{{ $student->schoolClass?->major?->name ?? '-' }}</p></div>
        </div>
    </x-card>

    <div class="grid gap-4 md:grid-cols-3">
        <x-card>
            <p class="text-sm font-bold text-slate-500">Rata-rata Keseluruhan</p>
            <p class="mt-2 text-3xl font-extrabold text-slate-900">{{ is_null($summary['average']) ? '-' : number_format($summary['average'], 2) }}</p>
            <p class="mt-1 text-xs font-semibold text-slate-400">Nilai tersimpan, mapel dihitung</p>
        </x-card>
        <x-card>
            <p class="text-sm font-bold text-slate-500">Ranking Angkatan</p>
            <p class="mt-2 text-3xl font-extrabold text-slate-900">{{ $summary['cohort_rank'] ?? '-' }}</p>
            <p class="mt-1 text-xs font-semibold text-slate-400">dari {{ $summary['cohort_total'] }} siswa</p>
        </x-card>
        <x-card>
            <p class="text-sm font-bold text-slate-500">Ranking Kelas</p>
            <p class="mt-2 text-3xl font-extrabold text-slate-900">{{ $summary['class_rank'] ?? '-' }}</p>
            <p class="mt-1 text-xs font-semibold text-slate-400">dari {{ $summary['class_total'] }} siswa</p>
        </x-card>
        <x-card>
            <p class="text-sm font-bold text-slate-500">Ranking Jurusan</p>
            <p class="mt-2 text-3xl font-extrabold text-slate-900">{{ $summary['major_rank'] ?? '-' }}</p>
            <p class="mt-1 text-xs font-semibold text-slate-400">dari {{ $summary['major_total'] }} siswa</p>
        </x-card>
    </div>

    <x-scores.average-trend-chart id="admin-average-trend" :averages="$averages" />

    <x-card>
        <div class="flex flex-wrap gap-2" data-semester-tabs>
            @foreach ($semesters as $semester => $data)
                <button type="button" class="js-semester-tab rounded-xl px-4 py-2 text-sm font-extrabold transition {{ $loop->first ? 'bg-blue-900 text-white' : 'bg-slate-100 text-slate-600 hover:bg-blue-50 hover:text-blue-900' }}" data-semester-target="admin-score-semester-{{ $semester }}">
                    Semester {{ $semester }}
                </button>
            @endforeach
        </div>
    </x-card>

    @foreach ($semesters as $semester => $data)
        @php
        @endphp
        <div id="admin-score-semester-{{ $semester }}" class="{{ $loop->first ? '' : 'hidden' }}" data-semester-panel>
            <x-card title="Semester {{ $semester }}" description="Detail nilai per mata pelajaran.">
                @if ($data['settings']->isEmpty())
                    <x-empty-state icon="fa-solid fa-chart-line" title="Setting belum tersedia" description="Belum ada mapel untuk semester ini." />
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[760px] text-left text-sm">
                            <thead class="border-b border-slate-200 text-xs uppercase text-slate-500"><tr><th class="px-4 py-3 font-bold">Mapel</th><th class="px-4 py-3 font-bold">Rata-rata</th><th class="px-4 py-3 font-bold">Nilai</th></tr></thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach ($data['settings'] as $setting)
                                    @php
                                        $score = $data['scores']->get($setting->subject_id);
                                        $included = $data['included'][$setting->subject_id] ?? true;
                                    @endphp
                                    <tr>
                                        <td class="px-4 py-3 font-bold text-slate-900">{{ $setting->subject?->name }}</td>
                                        <td class="px-4 py-3"><span class="inline-flex rounded-full px-2.5 py-1 text-xs font-bold {{ $included ? 'bg-blue-50 text-blue-700' : 'bg-amber-50 text-amber-700' }}">{{ $included ? 'Dihitung' : 'Tidak dihitung' }}</span></td>
                                        <td class="px-4 py-3">{{ filled($score?->score) ? number_format((float) $score->score, 2) : '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </x-card>

            <x-scores.semester-line-chart :id="'admin-score-chart-'.$semester" :settings="$data['settings']" :scores="$data['scores']" title="Grafik Semester {{ $semester }}" />
        </div>
    @endforeach

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                window.$(document).on('click', '.js-semester-tab', function () {
                    const button = window.$(this);
                    const target = button.data('semester-target');

                    button.closest('[data-semester-tabs]').find('.js-semester-tab').removeClass('bg-blue-900 text-white').addClass('bg-slate-100 text-slate-600 hover:bg-blue-50 hover:text-blue-900');
                    button.removeClass('bg-slate-100 text-slate-600 hover:bg-blue-50 hover:text-blue-900').addClass('bg-blue-900 text-white');
                    window.$('[data-semester-panel]').addClass('hidden');
                    window.$('#' + target).removeClass('hidden');
                });
            });
        </script>
    @endpush
@endcomponent
