@csrf

<div class="grid gap-4 md:grid-cols-2">
    <x-form.input name="name" label="Nama Tahun Ajaran" placeholder="Contoh: 2026 / 2027" :value="old('name', $academicYear->name)" />
    <x-form.select name="semester" label="Semester">
        <option value="">Pilih semester</option>
        @foreach (['ganjil' => 'Ganjil', 'genap' => 'Genap'] as $value => $label)
            <option value="{{ $value }}" @selected(old('semester', $academicYear->semester) === $value)>{{ $label }}</option>
        @endforeach
    </x-form.select>
    <x-form.input name="start_year" label="Tahun Mulai" type="number" min="2000" max="2100" placeholder="2026" :value="old('start_year', $academicYear->start_year)" />
    <x-form.input name="end_year" label="Tahun Selesai" type="number" min="2000" max="2101" placeholder="2027" :value="old('end_year', $academicYear->end_year)" />
</div>

<label class="mt-4 flex items-start gap-3 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-700">
    <input type="checkbox" name="is_active" value="1" class="mt-0.5 size-5 rounded border-slate-300 text-blue-800 focus:ring-blue-700" @checked(old('is_active', $academicYear->is_active))>
    <span>
        Jadikan aktif
        <span class="block text-xs font-medium text-slate-500">Tahun ajaran aktif lain otomatis dinonaktifkan.</span>
    </span>
</label>

<div class="mt-6 flex flex-wrap items-center gap-2">
    <x-button type="submit">
        <i class="fa-solid fa-save"></i>
        Simpan
    </x-button>
    <x-button variant="secondary" :href="route('bk.academic-years.index')">Batal</x-button>
</div>
