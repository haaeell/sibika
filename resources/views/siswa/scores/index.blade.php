@component('layouts.app', ['title' => 'Nilai Semester'])
    @php
        $readonly = $scores->contains(fn ($score) => in_array($score->status, ['submitted', 'verified'], true));
        $needsMajor = $semester >= 3 && ! $student->schoolClass?->major_id;
    @endphp

    <x-page-header title="Nilai Semester" description="Isi nilai semester 1 sampai 5 sesuai mapel umum dan jurusan." />

    <x-card>
        <form method="GET" action="{{ route('siswa.scores.index') }}" class="flex flex-wrap items-end gap-3">
            <x-form.select name="semester" label="Pilih Semester" icon="fa-solid fa-layer-group">
                @for ($item = 1; $item <= 5; $item++)
                    <option value="{{ $item }}" @selected($semester === $item)>Semester {{ $item }}</option>
                @endfor
            </x-form.select>
            <x-button type="submit" variant="secondary"><i class="fa-solid fa-filter"></i> Tampilkan</x-button>
        </form>
    </x-card>

    @if ($needsMajor)
        <x-card>
            <div class="flex items-start gap-3 text-amber-700">
                <i class="fa-solid fa-triangle-exclamation mt-1"></i>
                <div>
                    <p class="font-bold">Jurusan belum ditentukan.</p>
                    <p class="mt-1 text-sm text-amber-600">Semester 3 sampai 5 membutuhkan jurusan siswa agar mapel jurusan bisa tampil.</p>
                </div>
            </div>
        </x-card>
    @endif

    <x-card title="Daftar Nilai" description="Simpan sebagai draft dulu, lalu ajukan ketika semua mapel wajib sudah lengkap.">
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
                        <x-form.input
                            name="scores[{{ $setting->subject_id }}]"
                            label="{{ $setting->subject->name }}{{ $setting->is_required ? ' *' : '' }}"
                            icon="fa-solid fa-chart-simple"
                            type="number"
                            min="0"
                            max="100"
                            step="0.01"
                            placeholder="0 - 100"
                            :value="old('scores.'.$setting->subject_id, $score?->score)"
                            :disabled="$readonly"
                        />
                    @endforeach
                </div>
                <x-form.error name="scores" />
                <div class="flex flex-wrap items-center gap-2">
                    <x-button type="submit" :disabled="$readonly"><i class="fa-solid fa-save"></i> Simpan Draft</x-button>
                </div>
            </form>

            <form action="{{ route('siswa.scores.submit') }}" method="POST" class="mt-3">
                @csrf
                <input type="hidden" name="semester" value="{{ $semester }}">
                <x-button type="submit" variant="secondary" :disabled="$readonly"><i class="fa-solid fa-paper-plane"></i> Ajukan Verifikasi</x-button>
            </form>
        @endif
    </x-card>
@endcomponent
