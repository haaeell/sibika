@component('layouts.app', ['title' => 'Biodata Siswa'])
    <x-page-header title="Biodata Siswa" description="Detail data siswa untuk monitoring BK.">
        <x-slot:actions>
            <x-button :href="route('bk.students.biodata.edit', $student)"><i class="fa-solid fa-pen"></i> Edit Biodata</x-button>
            <x-button variant="secondary" :href="route('bk.students.index')"><i class="fa-solid fa-arrow-left"></i> Kembali</x-button>
        </x-slot:actions>
    </x-page-header>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_20rem]">
        <x-card>
            <div class="flex flex-wrap items-start justify-between gap-4 border-b border-slate-100 pb-6">
                <div class="flex gap-4">
                    <span class="hidden size-12 items-center justify-center rounded-xl bg-blue-900 text-white sm:flex"><i class="fa-solid fa-id-card"></i></span>
                    <div>
                        <p class="flex items-center gap-1.5 text-xs font-bold uppercase tracking-widest text-blue-800"><i class="fa-solid fa-hashtag text-[10px]"></i> {{ $student->nis }}</p>
                        <h2 class="mt-1 text-2xl font-extrabold tracking-tight text-slate-900">{{ $student->name }}</h2>
                        <p class="mt-1 flex flex-wrap items-center gap-1.5 text-sm font-medium text-slate-500"><i class="fa-solid fa-school text-xs text-slate-400"></i> {{ $student->schoolClass?->name ?? 'Belum ditempatkan' }} <span class="text-slate-300">·</span> <i class="fa-solid fa-layer-group text-xs text-slate-400"></i> {{ $student->cohort?->name ?? 'Tanpa angkatan' }}</p>
                    </div>
                </div>
                <span class="inline-flex items-center gap-1.5 rounded-full bg-blue-50 px-3 py-1.5 text-xs font-bold text-blue-800"><i class="fa-solid fa-chart-simple text-[11px]"></i> {{ $progress['percentage'] }}% biodata</span>
            </div>

            <div class="flex flex-col items-center border-b border-slate-100 py-4">
                @if ($profile?->photo_path && \Illuminate\Support\Facades\Storage::disk('local')->exists($profile->photo_path))
                    <img src="{{ route('bk.students.biodata.photo.show', $student) }}" alt="Foto profil {{ $student->name }}" class="size-16 rounded-xl border border-white object-cover shadow-sm sm:size-20">
                @else
                    <div class="flex size-16 items-center justify-center rounded-xl border border-slate-200 bg-slate-50 text-slate-400 shadow-sm sm:size-20">
                        <i class="fa-solid fa-user-graduate text-lg"></i>
                    </div>
                @endif
                <p class="mt-2 text-xs font-bold text-slate-900">{{ $profile?->photo_path ? 'Foto Profil' : 'Belum ada foto profil' }}</p>
                <p class="text-[11px] text-slate-500">{{ $student->name }}</p>
            </div>

            @php
                $mcuLabels = ['belum' => 'Belum', 'proses' => 'Proses', 'sudah' => 'Sudah'];
                $sectionMeta = [
                    'Data Pribadi' => ['icon' => 'fa-user', 'bg' => 'bg-blue-50', 'fg' => 'text-blue-700'],
                    'Alamat' => ['icon' => 'fa-map-location-dot', 'bg' => 'bg-sky-50', 'fg' => 'text-sky-700'],
                    'Data Fisik & Kesehatan' => ['icon' => 'fa-heart-pulse', 'bg' => 'bg-rose-50', 'fg' => 'text-rose-700'],
                    'Pilihan Kampus' => ['icon' => 'fa-graduation-cap', 'bg' => 'bg-indigo-50', 'fg' => 'text-indigo-700'],
                    'TKA' => ['icon' => 'fa-list-check', 'bg' => 'bg-violet-50', 'fg' => 'text-violet-700'],
                    'Persiapan & Karir' => ['icon' => 'fa-bullseye', 'bg' => 'bg-amber-50', 'fg' => 'text-amber-700'],
                    'Aktivitas & Evaluasi Diri' => ['icon' => 'fa-trophy', 'bg' => 'bg-emerald-50', 'fg' => 'text-emerald-700'],
                ];
                $sections = [
                    'Data Pribadi' => [
                        'Jenis Kelamin' => ['male' => 'Laki-laki', 'female' => 'Perempuan'][$profile?->gender] ?? null,
                        'Tempat, Tanggal Lahir' => trim(($profile?->birth_place ?? '').($profile?->birth_date ? ', '.$profile->birth_date->format('d M Y') : '')),
                        'No WA Aktif' => $profile?->phone,
                    ],
                    'Alamat' => [
                        'Provinsi' => $profile?->province,
                        'Kota / Kabupaten' => $profile?->city,
                        'Kecamatan' => $profile?->district,
                        'Kelurahan / Desa' => $profile?->village,
                        'Kode Pos' => $profile?->postal_code,
                        'Alamat Rumah' => $profile?->address,
                    ],
                    'Data Fisik & Kesehatan' => [
                        'Tinggi Badan' => $profile?->height_cm ? $profile->height_cm.' cm' : null,
                        'Berat Badan' => $profile?->weight_kg ? $profile->weight_kg.' kg' : null,
                        'Riwayat Kesehatan/Penyakit' => $profile?->medical_history,
                        'Status MCU Mandiri' => trim(($mcuLabels[$profile?->mcu_status] ?? $profile?->mcu_status ?? '').($profile?->mcu_status === 'sudah' ? ' - '.$profile?->mcu_count.' kali, terakhir '.$profile?->mcu_last_date?->format('d M Y') : '')),
                    ],
                    'Biodata Orang Tua' => [
                        'Nama Ayah' => $profile?->parent_father_name,
                        'Pekerjaan Ayah' => $profile?->parent_father_occupation,
                        'Nama Ibu' => $profile?->parent_mother_name,
                        'Pekerjaan Ibu' => $profile?->parent_mother_occupation,
                        'Nomor Orang Tua' => $profile?->parent_phone,
                        'Alamat Orang Tua' => $profile?->parent_address,
                    ],
                    'Pilihan Kampus' => [
                        'Pilihan 1 (Kampus)' => $profile?->universityChoice1?->name,
                        'Jurusan Pilihan 1' => $profile?->university_major_choice_1,
                        'Pilihan 2 (Kampus)' => $profile?->universityChoice2?->name,
                        'Jurusan Pilihan 2' => $profile?->university_major_choice_2,
                        'Pilihan 3 (Kampus)' => $profile?->universityChoice3?->name,
                        'Jurusan Pilihan 3' => $profile?->university_major_choice_3,
                    ],
                    'TKA' => [
                        'Mapel TKA Dipilih' => $student->tkaSelections->map(fn ($selection) => $selection->tkaSubject?->name)->filter()->join(', ') ?: null,
                    ],
                    'Aktivitas & Evaluasi Diri' => [
                        'Mengikuti Organisasi' => $profile?->organization_status ? ucfirst($profile->organization_status) : null,
                        'Hal yang Perlu Ditingkatkan' => $profile?->self_improvement_notes,
                    ],
                ];
            @endphp

            @foreach ($sections as $title => $items)
                @php $meta = $sectionMeta[$title] ?? ['icon' => 'fa-circle-info', 'bg' => 'bg-slate-100', 'fg' => 'text-slate-600']; @endphp
                <section class="border-b border-slate-100 py-6 last:border-b-0">
                    <div class="mb-4 flex items-center gap-3">
                        <span class="flex size-9 items-center justify-center rounded-xl {{ $meta['bg'] }} {{ $meta['fg'] }}"><i class="fa-solid {{ $meta['icon'] }} text-sm"></i></span>
                        <h3 class="text-base font-bold text-slate-900">{{ $title }}</h3>
                    </div>
                    <dl class="grid gap-x-6 gap-y-4 sm:grid-cols-2">
                        @foreach ($items as $label => $value)
                            <div class="rounded-xl border border-slate-100 bg-slate-50/60 p-3">
                                <dt class="flex items-center gap-1.5 text-[11px] font-bold uppercase tracking-wide text-slate-400"><i class="fa-solid fa-minus text-[8px] opacity-50"></i> {{ $label }}</dt>
                                <dd class="mt-1 whitespace-pre-line text-sm font-semibold {{ filled($value) ? 'text-slate-800' : 'text-slate-400 italic' }}">{{ filled($value) ? $value : 'Belum diisi' }}</dd>
                            </div>
                        @endforeach
                    </dl>
                </section>
            @endforeach

            <section class="border-b border-slate-100 py-6 last:border-b-0">
                <div class="mb-4 flex items-center gap-3">
                    <span class="flex size-9 items-center justify-center rounded-xl bg-blue-50 text-blue-700"><i class="fa-solid fa-folder-open text-sm"></i></span>
                    <h3 class="text-base font-bold text-slate-900">Dokumen Pribadi</h3>
                </div>
                <div class="grid gap-3 sm:grid-cols-3">
                    @foreach (['Ijazah SMP' => ['Ijazah SMP', 'Ijazah', 'ijazah', 'ijazah_smp'], 'Akte' => ['Akte', 'akte'], 'Kartu Keluarga' => ['Kartu Keluarga', 'kartu_keluarga']] as $type => $aliases)
                        @php $document = $student->documents->filter(fn ($item) => in_array($item->document_type, $aliases, true))->last(); @endphp
                        <div class="rounded-xl border border-slate-100 bg-slate-50/60 p-3">
                            <p class="text-sm font-bold text-slate-900">{{ $type }}</p>
                            @if ($document)
                                <a href="{{ route('bk.students.biodata.documents.download', [$student, $document]) }}" class="mt-1 block truncate text-xs font-medium text-blue-800 hover:text-blue-900">{{ $document->original_name }}</a>
                            @else
                                <p class="mt-1 text-xs font-semibold italic text-slate-400">Belum diupload</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            </section>

            <section class="border-b border-slate-100 py-6 last:border-b-0">
                <div class="mb-4 flex items-center gap-3"><span class="flex size-9 items-center justify-center rounded-xl bg-amber-50 text-amber-700"><i class="fa-solid fa-trophy text-sm"></i></span><h3 class="text-base font-bold text-slate-900">Prestasi Akademik atau Non Akademik</h3></div>
                <div class="space-y-2">
                    @forelse ($student->achievements as $achievement)
                        <div class="rounded-xl border border-slate-100 bg-slate-50/60 p-3 text-sm font-semibold text-slate-700">{{ ucfirst(str_replace('_', ' ', $achievement->type)) }} - {{ $achievement->name }} - {{ strtoupper(str_replace('_', '/', $achievement->level)) }} - {{ $achievement->year }}</div>
                    @empty
                        <p class="rounded-xl border border-dashed border-slate-200 bg-slate-50 p-4 text-sm font-semibold text-slate-400">Tidak ada prestasi.</p>
                    @endforelse
                </div>
            </section>

            <section class="border-b border-slate-100 py-6 last:border-b-0">
                <div class="mb-4 flex items-center gap-3"><span class="flex size-9 items-center justify-center rounded-xl bg-emerald-50 text-emerald-700"><i class="fa-solid fa-people-group text-sm"></i></span><h3 class="text-base font-bold text-slate-900">Organisasi dan Ekskul di Sekolah</h3></div>
                <div class="space-y-2">
                    @forelse ($student->organizations as $organization)
                        <div class="rounded-xl border border-slate-100 bg-slate-50/60 p-3 text-sm font-semibold text-slate-700">{{ $organization->name }} - {{ $organization->position }} - {{ strtoupper(str_replace('_', '/', $organization->level)) }} - {{ $organization->year }}</div>
                    @empty
                        <p class="rounded-xl border border-dashed border-slate-200 bg-slate-50 p-4 text-sm font-semibold text-slate-400">Tidak mengikuti organisasi/ekskul.</p>
                    @endforelse
                </div>
            </section>

        </x-card>

        <aside class="space-y-6">
            <x-card>
                <div class="mb-3 flex items-center gap-3">
                    <span class="flex size-9 items-center justify-center rounded-xl bg-blue-50 text-blue-700"><i class="fa-solid fa-list-check"></i></span>
                    <div><h3 class="text-sm font-bold text-slate-900">Progress Biodata</h3><p class="text-xs text-slate-500">Ringkasan kelengkapan data siswa.</p></div>
                </div>
                <div class="flex items-end gap-2">
                    <div class="text-4xl font-extrabold tracking-tight text-blue-900">{{ $progress['percentage'] }}%</div>
                    <span class="mb-1.5 rounded-full bg-blue-50 px-2 py-0.5 text-xs font-bold text-blue-700"><i class="fa-solid fa-chart-simple mr-1"></i> lengkap</span>
                </div>
                <div class="mt-4 h-3 overflow-hidden rounded-full bg-slate-100"><div class="h-full rounded-full bg-blue-800 transition-all" style="width: {{ $progress['percentage'] }}%"></div></div>
                <div class="mt-5 space-y-2.5">
                    @php $progressIcons = ['personal'=>'fa-user','address'=>'fa-location-dot','physical'=>'fa-heart-pulse','parents'=>'fa-people-roof','campus_choice'=>'fa-graduation-cap','tka'=>'fa-list-check','school_activity'=>'fa-trophy','documents'=>'fa-folder-open']; @endphp
                    @foreach (['personal' => 'Data pribadi', 'address' => 'Alamat', 'physical' => 'Fisik & Kesehatan', 'parents' => 'Biodata Orang Tua', 'campus_choice' => 'Pilihan Kampus', 'tka' => 'TKA', 'school_activity' => 'Aktivitas & Evaluasi', 'documents' => 'Dokumen Wajib'] as $key => $label)
                        <div class="flex items-center justify-between rounded-lg bg-slate-50 px-2.5 py-2 text-sm"><span class="flex items-center gap-2 font-semibold text-slate-600"><i class="fa-solid {{ $progressIcons[$key] }} text-xs text-slate-400"></i> {{ $label }}</span><i class="fa-solid {{ $progress['sections'][$key] ? 'fa-circle-check text-emerald-500' : 'fa-circle text-slate-300' }}"></i></div>
                    @endforeach
                </div>
            </x-card>
            <x-card>
                <div class="mb-3 flex items-center gap-3">
                    <span class="flex size-9 items-center justify-center rounded-xl bg-amber-50 text-amber-700"><i class="fa-solid fa-award"></i></span>
                    <div><h3 class="text-sm font-bold text-slate-900">Sertifikat Prestasi</h3><p class="text-xs text-slate-500">Sertifikat yang diunggah siswa.</p></div>
                </div>
                @forelse ($student->documents->reject(fn ($document) => in_array($document->document_type, ['kip', 'kartu_keluarga', 'Kartu Keluarga', 'Ijazah SMP', 'Ijazah', 'Akte', 'dokumen_lainnya'], true)) as $document)
                    <div class="flex items-center justify-between gap-3 rounded-xl border border-slate-100 bg-white p-3 last:mb-0">
                        <div class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-amber-50 text-amber-700"><i class="fa-solid fa-file-lines text-sm"></i></div>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-xs font-bold leading-tight text-slate-800">{{ $document->document_type }}</p>
                            <a href="{{ route('bk.students.biodata.documents.download', [$student, $document]) }}" class="block truncate text-xs font-medium text-blue-800 hover:text-blue-900">{{ $document->original_name }}</a>
                        </div>
                        <form action="{{ route('bk.students.biodata.documents.destroy', [$student, $document]) }}" method="POST">@csrf @method('DELETE')<button class="flex size-8 items-center justify-center rounded-lg bg-rose-50 text-rose-600 transition hover:bg-rose-100" aria-label="Hapus sertifikat"><i class="fa-solid fa-trash text-xs"></i></button></form>
                    </div>
                @empty
                    <div class="rounded-xl border border-dashed border-slate-200 bg-slate-50 p-6 text-center">
                        <div class="mx-auto flex size-10 items-center justify-center rounded-xl bg-white text-slate-400 shadow-sm"><i class="fa-solid fa-file-circle-xmark"></i></div>
                        <p class="mt-2 text-sm font-semibold text-slate-500">Belum ada sertifikat</p>
                        <p class="text-xs text-slate-400">Siswa belum mengunggah sertifikat.</p>
                    </div>
                @endforelse
            </x-card>
        </aside>
    </div>
@endcomponent
