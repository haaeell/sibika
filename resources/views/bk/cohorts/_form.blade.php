@csrf

<div class="grid gap-4 md:grid-cols-2">
    <x-form.input name="name" label="Nama Angkatan" icon="fa-solid fa-users-rectangle" placeholder="Contoh: Angkatan 19" :value="old('name', $cohort->name)" />
    <x-form.select name="status" label="Status" icon="fa-solid fa-toggle-on">
        <option value="">Pilih status</option>
        @foreach (['active' => 'Aktif', 'graduated' => 'Lulus', 'inactive' => 'Nonaktif'] as $value => $label)
            <option value="{{ $value }}" @selected(old('status', $cohort->status ?: 'active') === $value)>{{ $label }}</option>
        @endforeach
    </x-form.select>
    <x-form.input name="entry_year" label="Tahun Masuk" icon="fa-solid fa-door-open" type="number" min="2000" max="2100" placeholder="2024" :value="old('entry_year', $cohort->entry_year)" />
    <x-form.input name="graduation_year" label="Tahun Lulus" icon="fa-solid fa-graduation-cap" type="number" min="2000" max="2105" placeholder="2027" :value="old('graduation_year', $cohort->graduation_year)" />
</div>

<div class="mt-6 flex flex-wrap items-center gap-2">
    <x-button type="submit"><i class="fa-solid fa-save"></i> Simpan</x-button>
    <x-button variant="secondary" :href="route('bk.cohorts.index')">Batal</x-button>
</div>
