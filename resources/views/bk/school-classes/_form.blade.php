@csrf

<div class="grid gap-4 md:grid-cols-2">
    <x-form.input name="name" label="Nama Kelas" icon="fa-solid fa-school" placeholder="Contoh: XII IPA 1" :value="old('name', $schoolClass->name)" />
    <x-form.select name="grade_level" label="Tingkat" icon="fa-solid fa-layer-group">
        <option value="">Pilih tingkat</option>
        @foreach (['X' => 'X', 'XI' => 'XI', 'XII' => 'XII'] as $value => $label)
            <option value="{{ $value }}" @selected(old('grade_level', $schoolClass->grade_level) === $value)>{{ $label }}</option>
        @endforeach
    </x-form.select>
    <x-form.input name="major" label="Jurusan" icon="fa-solid fa-book-open" placeholder="Contoh: IPA" :value="old('major', $schoolClass->major)" />
    <x-form.select name="academic_year_id" label="Tahun Ajaran" icon="fa-solid fa-calendar-days">
        <option value="">Pilih tahun ajaran</option>
        @foreach ($academicYears as $academicYear)
            <option value="{{ $academicYear->id }}" @selected((string) old('academic_year_id', $schoolClass->academic_year_id) === (string) $academicYear->id)>{{ $academicYear->name }}</option>
        @endforeach
    </x-form.select>
    <x-form.select name="homeroom_teacher_id" label="Wali Kelas" icon="fa-solid fa-chalkboard-user">
        <option value="">Belum ditentukan</option>
        @foreach ($teachers as $teacher)
            <option value="{{ $teacher->id }}" @selected((string) old('homeroom_teacher_id', $schoolClass->homeroom_teacher_id) === (string) $teacher->id)>{{ $teacher->name }} ({{ $teacher->code }})</option>
        @endforeach
    </x-form.select>
</div>

<div class="mt-6 flex flex-wrap items-center gap-2">
    <x-button type="submit"><i class="fa-solid fa-save"></i> Simpan</x-button>
    <x-button variant="secondary" :href="route('bk.school-classes.index')">Batal</x-button>
</div>
