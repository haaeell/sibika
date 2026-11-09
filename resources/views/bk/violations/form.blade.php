@component('layouts.app', ['title' => $violation->exists ? 'Edit Pelanggaran' : 'Catat Pelanggaran'])
    <x-page-header :title="$violation->exists ? 'Edit Pelanggaran' : 'Catat Pelanggaran'" description="Kelas mengikuti data siswa saat ini.">
        <x-slot:actions><x-button variant="secondary" :href="route('bk.violations.index')">Kembali</x-button></x-slot:actions>
    </x-page-header>
    <x-card>
        <form method="POST" action="{{ $violation->exists ? route('bk.violations.update', $violation) : route('bk.violations.store') }}" class="space-y-5">
            @csrf
            @if ($violation->exists) @method('PUT') @endif
            <div class="grid gap-5 md:grid-cols-2">
                <x-form.select name="student_id" label="Terlapor (siswa)" icon="fa-solid fa-user" class="select2" data-placeholder="Cari nama atau NIS" required>
                    <option value=""></option>
                    @foreach ($students as $student)<option value="{{ $student->id }}" @selected(old('student_id', $violation->student_id) == $student->id)>{{ $student->name }} · {{ $student->nis }}</option>@endforeach
                </x-form.select>
                <x-form.input name="occurred_at" label="Tanggal kejadian" type="date" required :value="old('occurred_at', $violation->occurred_at?->format('Y-m-d'))" />
                <x-form.input name="pasal_perdupsis" label="Pasal PERDUPSIS" maxlength="255" required :value="old('pasal_perdupsis', $violation->pasal_perdupsis)" />
            </div>
            <x-form.textarea name="sanction" label="Sanksi" rows="4" maxlength="5000" required>{{ old('sanction', $violation->sanction) }}</x-form.textarea>
            <div class="flex justify-end gap-2"><x-button variant="secondary" :href="route('bk.violations.index')">Batal</x-button><x-button type="submit">Simpan</x-button></div>
        </form>
    </x-card>
@endcomponent
