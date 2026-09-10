@csrf

<div class="grid gap-4 md:grid-cols-2">
    <x-form.input name="code" label="NIP / Kode Guru" icon="fa-solid fa-id-card" placeholder="Contoh: 198012345" :value="old('code', $teacher->code)" />
    <x-form.input name="name" label="Nama Lengkap" icon="fa-solid fa-user-tie" placeholder="Nama guru" :value="old('name', $teacher->name)" />
    <x-form.input name="email" label="Email" icon="fa-solid fa-envelope" type="email" placeholder="guru@sekolah.id" :value="old('email', $teacher->email)" />
    <x-form.input name="phone" label="Nomor HP" icon="fa-solid fa-phone" placeholder="08xxxxxxxxxx" :value="old('phone', $teacher->phone)" />
    <x-form.select name="subject_ids[]" label="Mata Pelajaran" icon="fa-solid fa-book" class="select2" multiple data-placeholder="Pilih mata pelajaran">
        @foreach ($subjects as $subject)
            <option value="{{ $subject->id }}" @selected(in_array($subject->id, old('subject_ids', $teacher->subjects?->pluck('id')->all() ?? [])))>{{ $subject->name }} ({{ $subject->code }})</option>
        @endforeach
    </x-form.select>
    <x-form.select name="status" label="Status" icon="fa-solid fa-toggle-on">
        @foreach (['active' => 'Aktif', 'inactive' => 'Nonaktif'] as $value => $label)
            <option value="{{ $value }}" @selected(old('status', $teacher->status ?: 'active') === $value)>{{ $label }}</option>
        @endforeach
    </x-form.select>
</div>

<div class="mt-6 flex flex-wrap items-center gap-2">
    <x-button type="submit"><i class="fa-solid fa-save"></i> Simpan</x-button>
    <x-button variant="secondary" :href="route('bk.teachers.index')">Batal</x-button>
</div>
