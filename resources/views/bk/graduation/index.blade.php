@component('layouts.app', ['title' => 'Pengumuman Kelulusan'])
    <x-page-header title="Pengumuman Kelulusan" description="Atur waktu tayang, data surat, dan pantau kesiapan siswa per angkatan." />

    <div class="space-y-5">
        <div class="flex flex-wrap items-end justify-between gap-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
            <form method="GET" action="{{ route('bk.graduation.index') }}" class="flex flex-wrap items-end gap-3">
                <label class="block text-xs font-bold text-slate-600">Angkatan
                    <select name="cohort_id" class="mt-1.5 block h-10 min-w-52 rounded-xl border border-slate-200 bg-white px-3 text-sm font-semibold text-slate-800 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        @foreach ($cohorts as $option)
                            <option value="{{ $option->id }}" @selected($cohort?->id === $option->id)>{{ $option->name }}</option>
                        @endforeach
                    </select>
                </label>
                <x-button type="submit"><i class="fa-solid fa-filter" aria-hidden="true"></i> Tampilkan</x-button>
            </form>
            @if ($cohort)
                <div class="flex flex-wrap gap-2">
                    <x-button variant="secondary" :href="route('bk.graduation.export', ['cohort_id' => $cohort->id, 'format' => 'pdf'])"><i class="fa-regular fa-file-pdf text-rose-600" aria-hidden="true"></i> PDF</x-button>
                    <x-button variant="secondary" :href="route('bk.graduation.export', ['cohort_id' => $cohort->id, 'format' => 'xlsx'])"><i class="fa-regular fa-file-excel text-emerald-700" aria-hidden="true"></i> Excel</x-button>
                </div>
            @endif
        </div>

        @if ($cohort)
            @php
                $graduation = app(\App\Services\GraduationService::class);
                $certificateConfigured = $announcement && $graduation->certificateConfigured($announcement);
                $visible = $graduation->visible($announcement);
                $studentRows = $students->map(function ($student) use ($rankings, $certificateConfigured) {
                    $score = $rankings->get($student->id);
                    $parent = $student->parents->firstWhere('parent_type', 'father')?->name
                        ?: $student->parents->firstWhere('parent_type', 'mother')?->name
                        ?: $student->profile?->parent_father_name
                        ?: $student->profile?->parent_mother_name;
                    $studentComplete = filled($student->nisn) && filled($student->profile?->birth_place)
                        && filled($student->profile?->birth_date) && filled($parent) && filled($score['average'] ?? null);

                    return ['student' => $student, 'score' => $score, 'ready' => $studentComplete && $certificateConfigured,
                        'missing' => ! $studentComplete ? 'Data siswa atau nilai belum lengkap' : 'Data pengesahan surat belum lengkap'];
                });
                $readyCount = $studentRows->where('ready', true)->count();
            @endphp

            @if (session('success'))
                <div id="graduation-feedback" role="status" class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">
                    <i class="fa-solid fa-circle-check mr-1" aria-hidden="true"></i>{{ session('success') }}
                </div>
            @elseif ($errors->any())
                <div id="graduation-feedback" role="alert" class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                    <p class="font-bold">Pengaturan belum tersimpan.</p>
                    <ul class="mt-1 list-disc space-y-0.5 pl-5">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif

            <div class="grid gap-3 sm:grid-cols-3">
                @foreach ([
                    ['label' => 'Total siswa', 'value' => $students->count(), 'icon' => 'fa-users', 'color' => 'bg-blue-50 text-blue-800'],
                    ['label' => 'Surat siap', 'value' => $readyCount, 'icon' => 'fa-file-circle-check', 'color' => 'bg-emerald-50 text-emerald-700'],
                    ['label' => 'Perlu dilengkapi', 'value' => $students->count() - $readyCount, 'icon' => 'fa-circle-exclamation', 'color' => 'bg-amber-50 text-amber-700'],
                ] as $stat)
                    <div class="flex items-center justify-between rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                        <div><p class="text-xs font-bold uppercase tracking-wide text-slate-500">{{ $stat['label'] }}</p><p class="mt-1 text-2xl font-extrabold tabular-nums text-slate-900">{{ $stat['value'] }}</p></div>
                        <span class="flex size-11 shrink-0 items-center justify-center rounded-xl {{ $stat['color'] }}"><i class="fa-solid {{ $stat['icon'] }}" aria-hidden="true"></i></span>
                    </div>
                @endforeach
            </div>

            <form method="POST" enctype="multipart/form-data" action="{{ route('bk.graduation.store') }}" class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm" data-graduation-form>
                @csrf
                <input type="hidden" name="cohort_id" value="{{ $cohort->id }}">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-4 py-4 sm:px-5">
                    <div><h2 class="font-bold text-slate-900">Pengaturan pengumuman</h2><p class="mt-0.5 text-xs text-slate-500">Kelola publikasi dan kelengkapan surat untuk {{ $cohort->name }}.</p></div>
                    <x-badge :variant="$visible ? 'success' : ($announcement?->is_active ? 'warning' : 'neutral')">
                        {{ $visible ? 'Sudah tayang' : ($announcement?->is_active ? 'Terjadwal' : 'Nonaktif') }}
                    </x-badge>
                </div>

                <section class="border-b border-slate-100 px-4 py-5 sm:px-5" aria-labelledby="graduation-publish-title">
                    <div class="mb-4 flex items-center gap-3">
                        <span class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-800"><i class="fa-solid fa-calendar-days" aria-hidden="true"></i></span>
                        <div><h3 id="graduation-publish-title" class="text-sm font-bold text-slate-900">Publikasi hasil</h3><p class="text-xs text-slate-500">Siswa melihat hasil ketika status aktif dan waktu tayang telah tiba.</p></div>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <label class="block text-xs font-bold text-slate-600">Waktu tayang
                            <input class="mt-1.5 block h-10 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm font-medium text-slate-800 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100" type="datetime-local" name="published_at" required value="{{ old('published_at', $announcement?->published_at?->format('Y-m-d\TH:i')) }}">
                        </label>
                        <label class="block text-xs font-bold text-slate-600">Status pengumuman
                            <select class="mt-1.5 block h-10 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm font-medium text-slate-800 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100" name="is_active">
                                <option value="0" @selected(! old('is_active', $announcement?->is_active ?? false))>Nonaktif</option>
                                <option value="1" @selected(old('is_active', $announcement?->is_active ?? false))>Aktif</option>
                            </select>
                        </label>
                    </div>
                </section>

                <section class="border-b border-slate-100 px-4 py-5 sm:px-5" aria-labelledby="graduation-letter-title">
                    <div class="mb-4 flex items-center gap-3">
                        <span class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-indigo-700"><i class="fa-solid fa-file-signature" aria-hidden="true"></i></span>
                        <div><h3 id="graduation-letter-title" class="text-sm font-bold text-slate-900">Data surat kelulusan</h3><p class="text-xs text-slate-500">Data ini tercantum pada surat yang diunduh siswa.</p></div>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                        <label class="block text-xs font-bold text-slate-600">Tanggal rapat kelulusan
                            <input class="mt-1.5 block h-10 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm font-medium text-slate-800 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100" type="date" name="meeting_date" value="{{ old('meeting_date', $announcement?->meeting_date?->format('Y-m-d')) }}">
                        </label>
                        <label class="block text-xs font-bold text-slate-600">Tempat terbit surat
                            <input class="mt-1.5 block h-10 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm font-medium text-slate-800 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100" name="issued_place" maxlength="255" value="{{ old('issued_place', $announcement?->issued_place) }}" placeholder="Kab. Subang">
                        </label>
                        <label class="block text-xs font-bold text-slate-600">Nama kepala sekolah
                            <input class="mt-1.5 block h-10 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm font-medium text-slate-800 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100" name="principal_name" maxlength="255" value="{{ old('principal_name', $announcement?->principal_name) }}" placeholder="Nama lengkap">
                        </label>
                        <label class="block text-xs font-bold text-slate-600">NIP kepala sekolah
                            <input class="mt-1.5 block h-10 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm font-medium text-slate-800 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100" name="principal_nip" maxlength="100" value="{{ old('principal_nip', $announcement?->principal_nip) }}" placeholder="Opsional">
                        </label>
                    </div>
                </section>

                <section class="px-4 py-5 sm:px-5" aria-labelledby="graduation-files-title">
                    <div class="mb-4 flex items-center gap-3">
                        <span class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-amber-50 text-amber-700"><i class="fa-solid fa-stamp" aria-hidden="true"></i></span>
                        <div><h3 id="graduation-files-title" class="text-sm font-bold text-slate-900">Pengesahan surat</h3><p class="text-xs text-slate-500">Unggah PNG, JPG, atau WebP, maksimal 2 MB per berkas.</p></div>
                    </div>
                    <div class="grid gap-3 sm:grid-cols-2">
                        @foreach ([['field' => 'signature', 'label' => 'Tanda tangan kepala sekolah', 'saved' => $announcement?->signature_path], ['field' => 'stamp', 'label' => 'Cap sekolah', 'saved' => $announcement?->stamp_path]] as $file)
                            @php $hasPreview = $attachmentAvailable[$file['field']] ?? false; @endphp
                            <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-3" data-graduation-preview-card>
                                <label for="graduation-{{ $file['field'] }}" class="block text-xs font-bold text-slate-700">{{ $file['label'] }}</label>
                                <div class="mt-2 flex h-32 items-center justify-center overflow-hidden rounded-lg border border-slate-200 bg-white p-2">
                                    <img @if ($hasPreview) src="{{ route('bk.graduation.attachment', ['cohort' => $cohort->id, 'type' => $file['field']]) }}" @endif alt="Pratinjau {{ strtolower($file['label']) }}" class="max-h-full max-w-full object-contain {{ $hasPreview ? '' : 'hidden' }}" data-graduation-preview-image>
                                    <span class="text-center text-xs text-slate-400 {{ $hasPreview ? 'hidden' : '' }}" data-graduation-preview-empty>Belum ada pratinjau</span>
                                </div>
                                <input id="graduation-{{ $file['field'] }}" class="mt-3 block w-full text-xs text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-blue-900 file:px-3 file:py-2 file:text-xs file:font-semibold file:text-white hover:file:bg-blue-800" type="file" name="{{ $file['field'] }}" accept="image/png,image/jpeg,image/webp" data-graduation-preview-input>
                                <p class="mt-2 text-[11px] {{ $hasPreview ? 'font-semibold text-emerald-700' : 'text-slate-500' }}" data-graduation-preview-note>{{ $hasPreview ? 'Berkas tersimpan · pilih berkas baru untuk mengganti' : ($file['saved'] ? 'Berkas tersimpan tidak ditemukan · unggah ulang' : 'Belum ada berkas') }}</p>
                                @error($file['field']) <p role="alert" class="mt-1 text-xs font-semibold text-rose-700">{{ $message }}</p> @enderror
                            </div>
                        @endforeach
                    </div>
                    @unless ($certificateConfigured)
                        <p class="mt-3 text-xs text-amber-800"><i class="fa-solid fa-circle-info mr-1" aria-hidden="true"></i>Lengkapi data surat dan berkas pengesahan agar surat siswa siap diunduh.</p>
                    @endunless
                </section>

                <div class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 bg-slate-50/70 px-4 py-4 sm:px-5">
                    @if ($errors->any()) <p role="alert" class="text-sm font-semibold text-rose-700">{{ $errors->first() }}</p> @else <p class="text-xs text-slate-500">Perubahan berlaku setelah pengaturan disimpan.</p> @endif
                    <x-button type="submit" data-graduation-save><i class="fa-solid fa-floppy-disk" aria-hidden="true"></i> Simpan pengaturan</x-button>
                </div>
            </form>

            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-4 py-4 sm:px-5">
                    <div><h2 class="font-bold text-slate-900">Daftar kelulusan</h2><p class="mt-0.5 text-xs text-slate-500">{{ $students->count() }} siswa · semua dinyatakan lulus</p></div>
                    <div class="flex flex-wrap items-center gap-2">
                        <x-badge variant="success">Semua Lulus</x-badge>
                        <label class="relative block"><span class="sr-only">Cari siswa</span><i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-xs text-slate-400" aria-hidden="true"></i><input id="graduation-search" class="h-10 w-56 max-w-full rounded-xl border border-slate-200 bg-white pl-9 pr-3 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100" type="search" placeholder="Cari nama, NIS, kelas..." aria-controls="graduation-table"></label>
                    </div>
                </div>
                <p class="px-4 py-2 text-xs text-slate-500 sm:hidden">Geser tabel untuk melihat semua kolom.</p>
                <div class="overflow-x-auto">
                    <table id="graduation-table" class="w-full min-w-[760px] text-left text-sm">
                        <thead class="bg-slate-50 text-xs font-bold uppercase tracking-wide text-slate-500"><tr><th scope="col" class="px-4 py-3">Siswa</th><th scope="col" class="px-4 py-3">Kelas</th><th scope="col" class="px-4 py-3 text-right">Total Nilai</th><th scope="col" class="px-4 py-3 text-right">Rata-rata</th><th scope="col" class="px-4 py-3 text-center">Peringkat</th><th scope="col" class="px-4 py-3">Surat</th></tr></thead>
                        <tbody>
                            @forelse ($studentRows as $row)
                                @php $student = $row['student']; $score = $row['score']; @endphp
                                <tr class="border-t border-slate-100 transition hover:bg-slate-50" data-graduation-row>
                                    <td class="px-4 py-3"><p class="font-bold text-slate-900">{{ $student->name }}</p><p class="mt-0.5 text-xs text-slate-500">{{ $student->nis }}</p></td>
                                    <td class="px-4 py-3 text-slate-600">{{ $student->schoolClass?->name ?? '—' }}</td>
                                    <td class="px-4 py-3 text-right font-semibold tabular-nums text-slate-800">{{ isset($score['total_score']) ? number_format((float) $score['total_score'], 2, ',', '.') : '—' }}</td>
                                    <td class="px-4 py-3 text-right font-semibold tabular-nums text-slate-800">{{ isset($score['average']) ? number_format((float) $score['average'], 2, ',', '.') : '—' }}</td>
                                    <td class="px-4 py-3 text-center font-bold tabular-nums text-slate-800">{{ isset($score['rank']) ? '#'.$score['rank'] : '—' }}</td>
                                    <td class="px-4 py-3"><x-badge :variant="$row['ready'] ? 'success' : 'warning'">{{ $row['ready'] ? 'Siap' : 'Belum lengkap' }}</x-badge>@unless ($row['ready']) <p class="mt-1 text-[11px] text-slate-500">{{ $row['missing'] }}</p> @endunless</td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="px-4 py-10 text-center text-sm text-slate-500">Belum ada siswa pada angkatan ini.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                    <p id="graduation-empty" class="hidden px-4 py-8 text-center text-sm text-slate-500">Siswa tidak ditemukan.</p>
                </div>
            </div>
        @endif
    </div>

    <script>
        const graduationForm = document.querySelector('[data-graduation-form]');
        const graduationSave = document.querySelector('[data-graduation-save]');
        let pendingGraduationImages = 0;

        async function prepareGraduationImage(file) {
            const sourceUrl = URL.createObjectURL(file);
            const source = new Image();
            source.src = sourceUrl;
            try {
                await source.decode();
                const mime = ['image/jpeg', 'image/webp'].includes(file.type) ? file.type : 'image/png';
                let maxEdge = 1200;
                for (let attempt = 0; attempt < 5; attempt++) {
                    const scale = Math.min(1, maxEdge / Math.max(source.naturalWidth, source.naturalHeight));
                    const canvas = document.createElement('canvas');
                    canvas.width = Math.max(1, Math.round(source.naturalWidth * scale));
                    canvas.height = Math.max(1, Math.round(source.naturalHeight * scale));
                    const context = canvas.getContext('2d');
                    if (mime === 'image/jpeg') {
                        context.fillStyle = '#fff';
                        context.fillRect(0, 0, canvas.width, canvas.height);
                    }
                    context.drawImage(source, 0, 0, canvas.width, canvas.height);
                    const blob = await new Promise(resolve => canvas.toBlob(resolve, mime, 0.85));
                    if (blob && blob.size <= 1900000) {
                        const extension = { 'image/jpeg': 'jpg', 'image/webp': 'webp', 'image/png': 'png' }[blob.type];
                        if (!extension) throw new Error('Format gambar tidak didukung.');
                        return new File([blob], file.name.replace(/\.[^.]+$/, '') + '.' + extension, { type: blob.type });
                    }
                    maxEdge = Math.round(maxEdge * 0.75);
                }
                throw new Error('Gambar masih terlalu besar setelah diperkecil.');
            } finally {
                URL.revokeObjectURL(sourceUrl);
            }
        }

        document.querySelectorAll('[data-graduation-preview-card]').forEach(card => {
            const input = card.querySelector('[data-graduation-preview-input]');
            const image = card.querySelector('[data-graduation-preview-image]');
            const empty = card.querySelector('[data-graduation-preview-empty]');
            const note = card.querySelector('[data-graduation-preview-note]');
            const savedSrc = image.getAttribute('src');
            const savedNote = note.textContent;
            const savedNoteClass = note.className;
            let objectUrl;
            let selectionId = 0;

            input.addEventListener('change', async () => {
                const currentSelection = ++selectionId;
                if (objectUrl) URL.revokeObjectURL(objectUrl);
                objectUrl = null;
                const file = input.files?.[0];
                if (file && ['image/png', 'image/jpeg', 'image/webp'].includes(file.type)) {
                    objectUrl = URL.createObjectURL(file);
                    image.src = objectUrl;
                    image.classList.remove('hidden');
                    empty.classList.add('hidden');
                    note.className = 'mt-2 text-[11px] font-semibold text-blue-700';
                    if (file.size <= 1900000) {
                        note.textContent = 'Berkas baru dipilih · simpan untuk menerapkan';
                        return;
                    }

                    note.textContent = 'Menyiapkan gambar agar bisa diunggah...';
                    pendingGraduationImages++;
                    graduationSave.disabled = true;
                    try {
                        const prepared = await prepareGraduationImage(file);
                        if (currentSelection !== selectionId) return;
                        const transfer = new DataTransfer();
                        transfer.items.add(prepared);
                        input.files = transfer.files;
                        note.textContent = 'Gambar berhasil diperkecil · simpan untuk menerapkan';
                    } catch (error) {
                        if (currentSelection !== selectionId) return;
                        input.value = '';
                        if (objectUrl) URL.revokeObjectURL(objectUrl);
                        objectUrl = null;
                        if (savedSrc) image.src = savedSrc;
                        image.classList.toggle('hidden', !savedSrc);
                        empty.classList.toggle('hidden', !!savedSrc);
                        note.className = 'mt-2 text-[11px] font-semibold text-rose-700';
                        note.textContent = 'Gambar terlalu besar dan gagal diperkecil. Pilih PNG, JPG, atau WebP lain di bawah 2 MB.';
                    } finally {
                        pendingGraduationImages--;
                        graduationSave.disabled = pendingGraduationImages > 0;
                    }
                } else {
                    if (savedSrc) image.src = savedSrc;
                    image.classList.toggle('hidden', !savedSrc);
                    empty.classList.toggle('hidden', !!savedSrc);
                    note.className = file ? 'mt-2 text-[11px] font-semibold text-rose-700' : savedNoteClass;
                    note.textContent = file ? 'Format gambar tidak didukung. Pilih PNG, JPG, atau WebP.' : savedNote;
                    if (file) input.value = '';
                }
            });
        });

        graduationForm?.addEventListener('submit', event => {
            if (pendingGraduationImages > 0) event.preventDefault();
        });

        const graduationSearch = document.getElementById('graduation-search');
        graduationSearch?.addEventListener('input', () => {
            const query = graduationSearch.value.toLocaleLowerCase().trim();
            let shown = 0;
            document.querySelectorAll('[data-graduation-row]').forEach(row => {
                const matches = row.textContent.toLocaleLowerCase().includes(query);
                row.classList.toggle('hidden', !matches);
                if (matches) shown++;
            });
            document.getElementById('graduation-empty').classList.toggle('hidden', shown > 0 || !query);
        });
    </script>
@endcomponent
