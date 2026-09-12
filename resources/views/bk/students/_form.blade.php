@csrf

<div class="mb-4 rounded-xl border border-blue-200 bg-blue-50 px-4 py-3 text-sm font-semibold text-blue-900">
    <i class="fa-solid fa-circle-info mr-1.5"></i>
    Akun login dibuat otomatis: email <code>{NISN}@smaplusasthahannas.id</code> (atau NIS bila NISN kosong), password awal = <b>NIS</b>. Siswa wajib menggantinya saat login pertama.
</div>

<div class="grid gap-4 md:grid-cols-2">
    <x-form.input name="nis" label="NIS" icon="fa-solid fa-id-card" placeholder="Nomor induk siswa" :value="old('nis', $student->nis)" />
    <x-form.input name="nisn" label="NISN" icon="fa-solid fa-fingerprint" placeholder="Nomor induk siswa nasional" :value="old('nisn', $student->nisn)" />
    <x-form.input name="name" label="Nama Lengkap" icon="fa-solid fa-user-graduate" placeholder="Nama siswa" :value="old('name', $student->name)" />
    <x-form.select name="status" label="Status" icon="fa-solid fa-toggle-on">
        @foreach (['active' => 'Aktif', 'graduated' => 'Lulus', 'inactive' => 'Nonaktif'] as $value => $label)
            <option value="{{ $value }}" @selected(old('status', $student->status ?: 'active') === $value)>{{ $label }}</option>
        @endforeach
    </x-form.select>
    <x-form.select name="class_id" label="Kelas" icon="fa-solid fa-school">
        <option value="">Belum ditempatkan</option>
        @foreach ($schoolClasses as $schoolClass)
            <option value="{{ $schoolClass->id }}" @selected((string) old('class_id', $student->class_id) === (string) $schoolClass->id)>{{ $schoolClass->name }} - {{ $schoolClass->academicYear?->name }}</option>
        @endforeach
    </x-form.select>
    <x-form.select name="cohort_id" label="Angkatan" icon="fa-solid fa-layer-group">
        <option value="">Belum ditentukan</option>
        @foreach ($cohorts as $cohort)
            <option value="{{ $cohort->id }}" @selected((string) old('cohort_id', $student->cohort_id) === (string) $cohort->id)>{{ $cohort->name }}</option>
        @endforeach
    </x-form.select>
</div>

<div class="mt-6 flex flex-wrap items-center gap-2">
    <x-button type="submit"><i class="fa-solid fa-save"></i> Simpan</x-button>
    <x-button variant="secondary" :href="route('bk.students.index')">Batal</x-button>
</div>
