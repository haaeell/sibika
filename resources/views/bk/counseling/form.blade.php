@component('layouts.app', ['title' => $session->exists ? 'Edit Sesi Konseling' : 'Catat Sesi Konseling'])
    <x-page-header :title="$session->exists ? 'Edit Sesi Konseling' : 'Catat Sesi Konseling'" description="Rekam pertemuan, hasil pembahasan, dan langkah berikutnya.">
        <x-slot:actions><x-button variant="secondary" :href="$session->exists ? route('bk.counseling.student', $session->student_id) : route('bk.counseling.index')"><i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Kembali</x-button></x-slot:actions>
    </x-page-header>

    @php
        $selectedStudent = old('student_id', $session->student_id ?? request('student_id'));
        $selectedClass = old('class_id', $students->firstWhere('id', (int) $selectedStudent)?->class_id);
    @endphp
    <form method="POST" action="{{ $session->exists ? route('bk.counseling.update', $session) : route('bk.counseling.store') }}" class="mx-auto max-w-5xl space-y-5">
        @csrf
        @if ($session->exists) @method('PUT') @endif

        <x-card title="Siswa & sesi" description="Pilih kelas terlebih dahulu, kemudian cari siswa berdasarkan nama atau NIS.">
            <div class="grid gap-5 md:grid-cols-2">
                <x-form.select name="class_id" label="Kelas" icon="fa-solid fa-school" required>
                    <option value="">Pilih kelas</option>
                    @foreach ($classes as $class)
                        <option value="{{ $class->id }}" @selected((string) $selectedClass === (string) $class->id)>{{ $class->name }} · {{ $class->academicYear?->name ?? 'Tanpa tahun ajaran' }}</option>
                    @endforeach
                </x-form.select>
                <x-form.select name="student_id" label="Siswa" icon="fa-solid fa-user" class="select2" data-placeholder="Cari nama atau NIS" required :disabled="! $selectedClass">
                    <option value=""></option>
                    @foreach ($students as $student)
                        <option value="{{ $student->id }}" data-class-id="{{ $student->class_id }}" @selected((string) $selectedStudent === (string) $student->id)>{{ $student->name }} · {{ $student->nis }}</option>
                    @endforeach
                </x-form.select>
                <x-form.input name="held_at" label="Tanggal dan waktu sesi" type="datetime-local" icon="fa-regular fa-calendar" required :value="old('held_at', $session->held_at?->format('Y-m-d\TH:i') ?? now()->format('Y-m-d\TH:i'))" />
                <x-form.select name="type" label="Jenis konseling" icon="fa-solid fa-list" required>
                    <option value="">Pilih jenis</option>
                    @foreach ($types as $type)
                        <option value="{{ $type }}" @selected(old('type', $session->type) === $type)>{{ ucfirst($type) }}</option>
                    @endforeach
                </x-form.select>
                <div class="md:col-span-2"><x-form.input name="topic" label="Topik" icon="fa-solid fa-tag" maxlength="150" required :value="old('topic', $session->topic)" placeholder="Contoh: Penyesuaian belajar" /></div>
            </div>
        </x-card>

        <x-card title="Catatan pertemuan" description="Ringkas masalah dan keputusan yang diambil selama sesi.">
            <div class="space-y-5">
                <x-form.textarea name="summary" label="Ringkasan masalah" icon="fa-solid fa-pen-to-square" rows="4" maxlength="5000" required>{{ old('summary', $session->summary) }}</x-form.textarea>
                <x-form.textarea name="outcome" label="Hasil pembahasan" icon="fa-solid fa-clipboard-check" rows="3" maxlength="5000">{{ old('outcome', $session->outcome) }}</x-form.textarea>
            </div>
        </x-card>

        <x-card title="Tindak lanjut" description="Kosongkan jika sesi tidak membutuhkan tindak lanjut.">
            <div class="grid gap-5 md:grid-cols-[2fr_1fr]">
                <x-form.textarea name="follow_up" label="Rencana tindak lanjut" icon="fa-solid fa-list-check" rows="3" maxlength="5000">{{ old('follow_up', $session->follow_up) }}</x-form.textarea>
                <x-form.input name="due_at" label="Tenggat" type="date" icon="fa-regular fa-calendar-check" :value="old('due_at', $session->due_at?->format('Y-m-d'))" help="Opsional, jika sudah ada jadwal." />
            </div>
        </x-card>

        <div class="flex justify-end gap-2"><x-button variant="secondary" :href="$session->exists ? route('bk.counseling.student', $session->student_id) : route('bk.counseling.index')">Batal</x-button><x-button type="submit"><i class="fa-solid fa-floppy-disk" aria-hidden="true"></i> Simpan sesi</x-button></div>
    </form>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const $ = window.$;
                const classes = $('#class_id');
                const students = $('#student_id');
                const options = students.find('option').clone();
                const initialStudent = @json((string) $selectedStudent);

                function filterStudents(preserve = false) {
                    const classId = classes.val();
                    const selected = preserve ? initialStudent : '';
                    students.empty().append(options.filter(function () {
                        return !this.value || (classId && $(this).attr('data-class-id') === classId);
                    }).clone());
                    students.prop('disabled', !classId).val(selected).trigger('change');
                }

                classes.on('change', () => filterStudents());
                filterStudents(true);
            });
        </script>
    @endpush
@endcomponent
