@csrf
<div class="grid gap-4 md:grid-cols-2">
    <x-form.select name="semester_number" label="Semester" icon="fa-solid fa-layer-group" help="Semester 1-2 untuk mapel umum. Semester 3-5 bisa umum atau per jurusan.">
        <option value="">Pilih semester</option>
        @for ($semester = 1; $semester <= 5; $semester++)
            <option value="{{ $semester }}" @selected((string) old('semester_number', $setting->semester_number) === (string) $semester)>Semester {{ $semester }}</option>
        @endfor
    </x-form.select>
    @if ($setting->exists)
        <x-form.select name="subject_id" label="Mata Pelajaran" icon="fa-solid fa-book">
            <option value="">Pilih mata pelajaran</option>
            @foreach ($subjects as $subject)
                <option value="{{ $subject->id }}" @selected((string) old('subject_id', $setting->subject_id) === (string) $subject->id)>{{ $subject->name }} ({{ $subject->code }})</option>
            @endforeach
        </x-form.select>
    @endif
    <x-form.select name="major_id" label="Jurusan" icon="fa-solid fa-code-branch" help="Kosongkan untuk mapel umum/lintas jurusan.">
        <option value="">Umum / semua jurusan</option>
        @foreach ($majors as $major)
            <option value="{{ $major->id }}" @selected((string) old('major_id', $setting->major_id) === (string) $major->id)>{{ $major->name }} ({{ $major->code }})</option>
        @endforeach
    </x-form.select>
</div>
@unless ($setting->exists)
    <div class="mt-4 rounded-2xl border border-slate-200 bg-white p-4">
        <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
            <div>
                <p class="text-sm font-bold text-slate-800">Mata Pelajaran</p>
                <p class="text-xs font-medium text-slate-500">Default semua mapel aktif tercentang.</p>
            </div>
            <label class="inline-flex items-center gap-2 text-sm font-bold text-blue-900">
                <input type="checkbox" class="js-check-all-subjects size-4 rounded border-slate-300 text-blue-800 focus:ring-blue-700" checked>
                Pilih semua
            </label>
        </div>
        <div class="grid gap-2 md:grid-cols-2 xl:grid-cols-3">
            @foreach ($subjects as $subject)
                <label class="flex items-center gap-3 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm font-semibold text-slate-700">
                    <input type="checkbox" name="subject_ids[]" value="{{ $subject->id }}" class="js-subject-checkbox size-4 rounded border-slate-300 text-blue-800 focus:ring-blue-700" @checked(in_array((string) $subject->id, old('subject_ids', $subjects->pluck('id')->map(fn ($id) => (string) $id)->all()), true))>
                    <span>{{ $subject->name }} <span class="text-xs text-slate-400">({{ $subject->code }})</span></span>
                </label>
            @endforeach
        </div>
        <x-form.error name="subject_ids" />
    </div>
@endunless
<div class="mt-4 grid gap-3 md:grid-cols-2">
    <label class="flex items-start gap-3 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-700"><input type="checkbox" name="is_required" value="1" class="mt-0.5 size-5 rounded border-slate-300 text-blue-800 focus:ring-blue-700" @checked(old('is_required', $setting->is_required))><span>Mapel wajib<span class="block text-xs font-medium text-slate-500">Wajib diisi sebelum nilai diajukan.</span></span></label>
    <label class="flex items-start gap-3 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-700"><input type="checkbox" name="is_active" value="1" class="mt-0.5 size-5 rounded border-slate-300 text-blue-800 focus:ring-blue-700" @checked(old('is_active', $setting->is_active))><span>Setting aktif<span class="block text-xs font-medium text-slate-500">Nonaktif tidak tampil di input nilai siswa.</span></span></label>
</div>
<div class="mt-6 flex flex-wrap items-center gap-2"><x-button type="submit"><i class="fa-solid fa-save"></i> Simpan</x-button><x-button variant="secondary" :href="route('bk.score-subject-settings.index')">Batal</x-button></div>

@unless ($setting->exists)
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                window.$('.js-check-all-subjects').on('change', function () {
                    window.$('.js-subject-checkbox').prop('checked', this.checked);
                });
            });
        </script>
    @endpush
@endunless
