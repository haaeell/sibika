@component('layouts.app', ['title' => 'Nilai Semester'])
    <x-page-header title="Nilai Semester" description="Isi nilai semester 1 sampai 5 sesuai mapel umum dan jurusan." />

    <div class="grid gap-4 md:grid-cols-3">
        <x-card>
            <p class="text-sm font-bold text-slate-500">Rata-rata Keseluruhan</p>
            <p class="mt-2 text-3xl font-extrabold text-slate-900">{{ is_null($summary['average']) ? '-' : number_format($summary['average'], 2) }}</p>
            <p class="mt-1 text-xs font-semibold text-slate-400">Nilai verified, mapel dihitung</p>
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

    <x-card>
        <div class="flex flex-wrap gap-2" data-semester-tabs>
            @foreach ($semesters as $semester => $data)
                <button type="button" class="js-semester-tab rounded-xl px-4 py-2 text-sm font-extrabold transition {{ $semester === $activeSemester ? 'bg-blue-900 text-white' : 'bg-slate-100 text-slate-600 hover:bg-blue-50 hover:text-blue-900' }}" data-semester-target="student-score-semester-{{ $semester }}">
                    Semester {{ $semester }}
                </button>
            @endforeach
        </div>
    </x-card>

    @foreach ($semesters as $semester => $data)
        @php
            $settings = $data['settings'];
            $scores = $data['scores'];
            $readonly = $scores->contains(fn ($score) => in_array($score->status, ['submitted', 'verified'], true));
            $needsMajor = $semester >= 3 && ! $student->schoolClass?->major_id;
        @endphp

        <div id="student-score-semester-{{ $semester }}" class="{{ $semester === $activeSemester ? '' : 'hidden' }}" data-semester-panel>
            @if ($needsMajor)
                <x-card>
                    <div class="flex items-start gap-3 text-amber-700">
                        <i class="fa-solid fa-triangle-exclamation mt-1"></i>
                        <div><p class="font-bold">Jurusan belum ditentukan.</p><p class="mt-1 text-sm text-amber-600">Semester 3 sampai 5 membutuhkan jurusan siswa agar mapel jurusan bisa tampil.</p></div>
                    </div>
                </x-card>
            @endif

            <x-card title="Semester {{ $semester }}" description="Simpan sebagai draft dulu, lalu ajukan ketika semua mapel wajib sudah lengkap.">
                @if ($settings->isEmpty())
                    <x-empty-state icon="fa-solid fa-chart-line" title="Setting nilai belum tersedia" description="Hubungi BK untuk mengatur mapel semester ini." />
                @else
                    @if ($scores->first()?->status)
                        <div class="mb-4 rounded-xl bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-600">Status: {{ str($scores->first()->status)->headline() }}</div>
                    @endif

                    <form action="{{ route('siswa.scores.save') }}" method="POST" class="space-y-4">
                        @csrf
                        <input type="hidden" name="semester" value="{{ $semester }}">
                        <div class="grid gap-4 md:grid-cols-2">
                            @foreach ($settings as $setting)
                                @php $score = $scores->get($setting->subject_id); @endphp
                                <x-form.input name="scores[{{ $setting->subject_id }}]" label="{{ $setting->subject->name }}{{ $setting->is_required ? ' *' : '' }}" icon="fa-solid fa-chart-simple" type="number" min="0" max="100" step="0.01" placeholder="0 - 100" :value="old('scores.'.$setting->subject_id, $score?->score)" :disabled="$readonly" />
                            @endforeach
                        </div>
                        <x-form.error name="scores" />
                        <x-button type="submit" :disabled="$readonly"><i class="fa-solid fa-save"></i> Simpan Draft</x-button>
                    </form>

                    <form action="{{ route('siswa.scores.submit') }}" method="POST" class="mt-3">
                        @csrf
                        <input type="hidden" name="semester" value="{{ $semester }}">
                        <x-button type="submit" variant="secondary" :disabled="$readonly"><i class="fa-solid fa-paper-plane"></i> Ajukan Verifikasi</x-button>
                    </form>
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
