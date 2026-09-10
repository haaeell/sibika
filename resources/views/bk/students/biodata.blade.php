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
                <div>
                    <p class="text-xs font-bold uppercase tracking-widest text-blue-800">{{ $student->nis }}</p>
                    <h2 class="mt-2 text-2xl font-extrabold text-slate-900">{{ $student->name }}</h2>
                    <p class="mt-1 text-sm font-medium text-slate-500">{{ $student->schoolClass?->name ?? 'Belum ditempatkan' }} · {{ $student->cohort?->name ?? 'Tanpa angkatan' }}</p>
                </div>
                <span class="rounded-full bg-blue-50 px-3 py-1.5 text-xs font-bold text-blue-800">{{ $progress['percentage'] }}% biodata</span>
            </div>

            @php
                $sections = [
                    'Data Pribadi' => [
                        'Nama panggilan' => $profile?->nickname,
                        'Jenis kelamin' => ['male' => 'Laki-laki', 'female' => 'Perempuan'][$profile?->gender] ?? null,
                        'Tempat, tanggal lahir' => trim(($profile?->birth_place ?? '').($profile?->birth_date ? ', '.$profile->birth_date->format('d M Y') : '')),
                        'Nomor HP' => $profile?->phone,
                        'Email' => $profile?->email ?? $student->user?->email,
                    ],
                    'Alamat' => [
                        'Provinsi' => $profile?->province,
                        'Kota / Kabupaten' => $profile?->city,
                        'Kecamatan' => $profile?->district,
                        'Kelurahan / Desa' => $profile?->village,
                        'Kode Pos' => $profile?->postal_code,
                        'Alamat lengkap' => $profile?->address,
                    ],
                    'Pendidikan' => [
                        'Asal sekolah' => $profile?->previous_school,
                        'Alamat asal sekolah' => $profile?->previous_school_address,
                        'Tahun lulus' => $profile?->graduation_year,
                        'Catatan akademik' => $profile?->academic_notes,
                    ],
                ];
            @endphp

            @foreach ($sections as $title => $items)
                <section class="border-b border-slate-100 py-6 last:border-b-0">
                    <h3 class="mb-4 text-base font-bold text-slate-900">{{ $title }}</h3>
                    <dl class="grid gap-x-6 gap-y-4 sm:grid-cols-2">
                        @foreach ($items as $label => $value)
                            <div><dt class="text-xs font-bold uppercase tracking-wide text-slate-400">{{ $label }}</dt><dd class="mt-1 whitespace-pre-line text-sm font-semibold text-slate-700">{{ filled($value) ? $value : 'Belum diisi' }}</dd></div>
                        @endforeach
                    </dl>
                </section>
            @endforeach

            <section class="border-t border-slate-100 py-6">
                <h3 class="mb-4 text-base font-bold text-slate-900">Data Keluarga</h3>
                <div class="grid gap-4 md:grid-cols-3">
                    @foreach (['father' => 'Ayah', 'mother' => 'Ibu', 'guardian' => 'Wali'] as $type => $label)
                        @php($parent = $parents->get($type))
                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-4"><p class="text-xs font-bold uppercase tracking-wide text-slate-400">{{ $label }}</p><p class="mt-2 font-bold text-slate-800">{{ $parent?->name ?: 'Belum diisi' }}</p><p class="mt-1 text-sm text-slate-500">{{ $parent?->phone ?: 'Nomor belum diisi' }}</p><p class="mt-2 text-xs text-slate-500">{{ $parent?->occupation ?: ($parent?->relation ?: 'Detail belum diisi') }}</p></div>
                    @endforeach
                </div>
            </section>
        </x-card>

        <aside class="space-y-6">
            <x-card title="Progress Biodata" description="Ringkasan kelengkapan data siswa.">
                <div class="text-4xl font-extrabold text-blue-900">{{ $progress['percentage'] }}%</div>
                <div class="mt-4 h-3 overflow-hidden rounded-full bg-slate-100"><div class="h-full rounded-full bg-blue-800" style="width: {{ $progress['percentage'] }}%"></div></div>
                <div class="mt-5 space-y-3">
                    @foreach (['personal' => 'Data pribadi', 'address' => 'Alamat', 'parents' => 'Data orang tua', 'education' => 'Pendidikan', 'documents' => 'Dokumen'] as $key => $label)
                        <div class="flex items-center justify-between text-sm"><span class="font-semibold text-slate-600">{{ $label }}</span><i class="fa-solid {{ $progress['sections'][$key] ? 'fa-circle-check text-emerald-500' : 'fa-circle text-slate-300' }}"></i></div>
                    @endforeach
                </div>
            </x-card>
            <x-card title="Dokumen" description="Dokumen yang diunggah siswa.">
                @forelse ($student->documents as $document)
                    <div class="flex items-center gap-2 border-b border-slate-100 py-2 last:border-0"><i class="fa-solid fa-file text-blue-800"></i><span class="truncate text-xs font-semibold text-slate-600">{{ $document->original_name }}</span></div>
                @empty
                    <p class="text-sm font-medium text-slate-400">Belum ada dokumen.</p>
                @endforelse
            </x-card>
        </aside>
    </div>
@endcomponent
