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
                $mcuLabels = ['belum' => 'Belum', 'proses' => 'Proses', 'sudah' => 'Sudah'];
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
                        'Status MCU Mandiri' => $mcuLabels[$profile?->mcu_status] ?? $profile?->mcu_status,
                    ],
                    'Pilihan Kampus' => [
                        'Pilihan 1 (Kampus)' => $profile?->universityChoice1?->name,
                        'Pilihan 2 (Kampus)' => $profile?->universityChoice2?->name,
                    ],
                    'Persiapan & Karir' => [
                        'Persiapan di Kelas 11' => $profile?->grade_11_preparation,
                        'Kekhawatiran Karir' => $profile?->career_concern,
                    ],
                    'Aktivitas & Evaluasi Diri' => [
                        'Prestasi di SMA Plus Astha Hannas' => $profile?->school_achievements,
                        'Mengikuti Organisasi' => $profile?->organization_status ? ucfirst($profile->organization_status) : null,
                        'Nama Organisasi' => $profile?->organization_status === 'ya' ? ($profile?->organization_name ?? '-') : '-',
                        'Hal yang Perlu Ditingkatkan' => $profile?->self_improvement_notes,
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

        </x-card>

        <aside class="space-y-6">
            <x-card title="Progress Biodata" description="Ringkasan kelengkapan data siswa.">
                <div class="text-4xl font-extrabold text-blue-900">{{ $progress['percentage'] }}%</div>
                <div class="mt-4 h-3 overflow-hidden rounded-full bg-slate-100"><div class="h-full rounded-full bg-blue-800" style="width: {{ $progress['percentage'] }}%"></div></div>
                <div class="mt-5 space-y-3">
                    @foreach (['personal' => 'Data pribadi', 'address' => 'Alamat', 'physical' => 'Fisik & Kesehatan', 'campus_choice' => 'Pilihan Kampus', 'career_preparation' => 'Persiapan Karir', 'school_activity' => 'Aktivitas & Evaluasi', 'documents' => 'Sertifikat Prestasi'] as $key => $label)
                        <div class="flex items-center justify-between text-sm"><span class="font-semibold text-slate-600">{{ $label }}</span><i class="fa-solid {{ $progress['sections'][$key] ? 'fa-circle-check text-emerald-500' : 'fa-circle text-slate-300' }}"></i></div>
                    @endforeach
                </div>
            </x-card>
            <x-card title="Sertifikat Prestasi" description="Sertifikat yang diunggah siswa.">
                @forelse ($student->documents as $document)
                    <div class="flex items-center justify-between gap-2 border-b border-slate-100 py-2 last:border-0">
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-xs font-bold text-slate-700">{{ $document->document_type }}</p>
                            <a href="{{ route('bk.students.biodata.documents.download', [$student, $document]) }}" class="truncate text-xs font-semibold text-blue-800 hover:text-blue-900">{{ $document->original_name }}</a>
                        </div>
                        <form action="{{ route('bk.students.biodata.documents.destroy', [$student, $document]) }}" method="POST">@csrf @method('DELETE')<button class="text-rose-600" aria-label="Hapus sertifikat"><i class="fa-solid fa-trash"></i></button></form>
                    </div>
                @empty
                    <p class="text-sm font-medium text-slate-400">Belum ada sertifikat.</p>
                @endforelse
            </x-card>
        </aside>
    </div>
@endcomponent
