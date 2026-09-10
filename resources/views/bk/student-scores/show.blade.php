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
            $hasSubmitted = $data['scores']->contains(fn ($score) => $score->status === 'submitted');
            $statusBadge = function (?string $status): string {
                return match ($status) {
                    'draft' => '<span class="inline-flex rounded-full bg-blue-50 px-2.5 py-1 text-xs font-bold text-blue-700">Draft</span>',
                    'submitted' => '<span class="inline-flex rounded-full bg-amber-50 px-2.5 py-1 text-xs font-bold text-amber-700">Diajukan</span>',
                    'verified' => '<span class="inline-flex rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-700">Terverifikasi</span>',
                    'rejected' => '<span class="inline-flex rounded-full bg-rose-50 px-2.5 py-1 text-xs font-bold text-rose-700">Ditolak</span>',
                    default => '<span class="inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-600">Belum Diisi</span>',
                };
            };
        @endphp
        <div id="admin-score-semester-{{ $semester }}" class="{{ $loop->first ? '' : 'hidden' }}" data-semester-panel>
            <x-card title="Semester {{ $semester }}" description="Detail nilai per mata pelajaran.">
                @if ($data['settings']->isEmpty())
                    <x-empty-state icon="fa-solid fa-chart-line" title="Setting belum tersedia" description="Belum ada mapel untuk semester ini." />
                @else
                    @if ($hasSubmitted)
                        <div class="mb-5 grid gap-3 rounded-2xl bg-slate-50 p-4 lg:grid-cols-[1fr_1fr_auto]">
                            <form action="{{ route('bk.student-scores.verify', [$student, $semester]) }}" method="POST" class="js-confirm-form flex gap-2" data-confirm-title="Verifikasi nilai semester {{ $semester }}?" data-confirm-text="Semua nilai yang diajukan pada semester ini akan diverifikasi." data-confirm-button="Ya, verifikasi" data-confirm-icon="success">
                                @csrf
                                <x-form.input name="note" icon="fa-regular fa-note-sticky" placeholder="Catatan verifikasi opsional" />
                                <x-button type="submit"><i class="fa-solid fa-check"></i> Verifikasi</x-button>
                            </form>
                            <form action="{{ route('bk.student-scores.reject', [$student, $semester]) }}" method="POST" class="js-confirm-form flex gap-2" data-confirm-title="Tolak nilai semester {{ $semester }}?" data-confirm-text="Siswa dapat memperbaiki nilai setelah ditolak." data-confirm-button="Ya, tolak">
                                @csrf
                                <x-form.input name="note" icon="fa-regular fa-note-sticky" placeholder="Catatan penolakan wajib" required />
                                <x-button type="submit" variant="danger"><i class="fa-solid fa-xmark"></i> Tolak</x-button>
                            </form>
                        </div>
                    @endif
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[760px] text-left text-sm">
                            <thead class="border-b border-slate-200 text-xs uppercase text-slate-500"><tr><th class="px-4 py-3 font-bold">Mapel</th><th class="px-4 py-3 font-bold">Rata-rata</th><th class="px-4 py-3 font-bold">Nilai</th><th class="px-4 py-3 font-bold">Status</th><th class="px-4 py-3 font-bold">Catatan</th></tr></thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach ($data['settings'] as $setting)
                                    @php
                                        $score = $data['scores']->get($setting->subject_id);
                                        $included = app(\App\Services\StudentScoreService::class)->isIncludedInAverage($setting);
                                    @endphp
                                    <tr>
                                        <td class="px-4 py-3 font-bold text-slate-900">{{ $setting->subject?->name }}</td>
                                        <td class="px-4 py-3"><span class="inline-flex rounded-full px-2.5 py-1 text-xs font-bold {{ $included ? 'bg-blue-50 text-blue-700' : 'bg-amber-50 text-amber-700' }}">{{ $included ? 'Dihitung' : 'Tidak dihitung' }}</span></td>
                                        <td class="px-4 py-3">{{ filled($score?->score) ? number_format((float) $score->score, 2) : '-' }}</td>
                                        <td class="px-4 py-3">{!! $statusBadge($score?->status) !!}</td>
                                        <td class="px-4 py-3 text-slate-500">{{ $score?->verification_note ?? '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </x-card>
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
