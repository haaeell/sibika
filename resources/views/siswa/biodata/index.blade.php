@php
    $isAdmin = $isAdmin ?? false;
    $biodataUpdateRoute = $biodataUpdateRoute ?? route('siswa.biodata.update');
    $biodataBackRoute = $biodataBackRoute ?? route('siswa.dashboard');
    $personalDocumentLabels = ['Ijazah SMP' => ['Ijazah SMP', 'Ijazah', 'ijazah', 'ijazah_smp'], 'Akte' => ['Akte', 'akte'], 'Kartu Keluarga' => ['Kartu Keluarga', 'kartu_keluarga']];
    $certificates = $student->documents->reject(fn ($document) => in_array($document->document_type, ['kip', 'kartu_keluarga', 'Kartu Keluarga', 'Ijazah SMP', 'Ijazah', 'Akte', 'dokumen_lainnya'], true));
    $achievementStatus = old('achievement_status', $profile?->achievement_status ?? ($student->achievements->isNotEmpty() ? 'ya' : ''));
@endphp

@component('layouts.app', ['title' => $isAdmin ? 'Edit Biodata Siswa' : 'Biodata'])
    <x-page-header :title="$isAdmin ? 'Edit Biodata Siswa' : 'Biodata Saya'" :description="$isAdmin ? 'Perbarui data biodata siswa.' : 'Lengkapi biodata dengan data yang benar untuk kebutuhan BK.'">
        <x-slot:actions>
            <x-button variant="secondary" :href="$biodataBackRoute"><i class="fa-solid fa-arrow-left"></i> Kembali</x-button>
        </x-slot:actions>
    </x-page-header>

    <div class="w-full space-y-6 pb-20 lg:pb-0" data-biodata-progress>
        <div class="sticky top-0 z-20 -mx-4 lg:mx-0">
            <div class="border-y border-slate-200 bg-white/95 px-4 py-3 shadow-sm backdrop-blur lg:rounded-2xl lg:border lg:px-5 lg:py-4">
                <div class="flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <span class="hidden size-10 items-center justify-center rounded-xl bg-blue-900 text-white sm:flex"><i class="fa-solid fa-list-check text-sm"></i></span>
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wide text-slate-400">Kelengkapan Biodata</p>
                            <p class="text-sm font-bold text-slate-900" data-progress-message>{{ $progress['percentage'] === 100 ? 'Semua data wajib sudah lengkap' : $progress['completed'].' dari '.$progress['total'].' data wajib terisi' }}</p>
                        </div>
                    </div>
                    <span class="shrink-0 text-2xl font-extrabold text-blue-900" data-progress-percentage>{{ $progress['percentage'] }}%</span>
                </div>
                <div class="mt-3 h-2 overflow-hidden rounded-full bg-slate-100">
                    <div class="h-full rounded-full bg-blue-800 transition-all duration-500" style="width: {{ $progress['percentage'] }}%" data-progress-bar></div>
                </div>
                <div class="-mx-1 mt-3 flex gap-2 overflow-x-auto px-1 pb-1 lg:grid lg:grid-cols-6 lg:overflow-visible lg:pb-0">
                            @foreach (['personal' => 'Pribadi', 'address' => 'Alamat', 'physical' => 'Fisik', 'parents' => 'Ortu', 'campus_choice' => 'Kampus', 'tka' => 'TKA', 'school_activity' => 'Aktivitas', 'documents' => 'Dokumen'] as $key => $label)
                        <div class="flex shrink-0 items-center gap-1.5 rounded-full px-2.5 py-1.5 text-xs font-semibold lg:min-w-0 {{ $progress['sections'][$key] ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-50 text-slate-400' }}" data-progress-section-indicator="{{ $key }}">
                            <i class="fa-solid {{ $progress['sections'][$key] ? 'fa-circle-check text-emerald-500' : 'fa-circle text-slate-300' }} text-xs"></i>
                            <span class="truncate">{{ $label }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <x-card class="p-0">
            <div class="border-b border-slate-100 px-5 py-5 sm:px-6">
                <h2 class="text-lg font-bold text-slate-900">Form Biodata Siswa</h2>
                <p class="mt-1 text-sm text-slate-500">Kolom bertanda <span class="font-bold text-rose-500">*</span> wajib diisi. Pastikan data sudah benar sebelum menyimpan.</p>
            </div>

            <section class="border-b border-slate-100 bg-slate-50/60 px-5 py-6 sm:px-6">
                    <div class="mx-auto flex max-w-md flex-col items-center text-center">
                        @if (! $isAdmin && $profile?->photo_path)
                            <img src="{{ route('siswa.biodata.photo.show', ['v' => md5($profile->photo_path)]) }}" alt="Foto profil {{ $student->name }}" class="size-20 rounded-2xl border-2 border-white object-cover shadow-sm sm:size-24">
                        @else
                            <div class="flex size-20 items-center justify-center rounded-2xl border border-slate-200 bg-white text-slate-400 shadow-sm sm:size-24">
                                <i class="fa-solid fa-user-graduate text-2xl"></i>
                            </div>
                        @endif

                        <p class="mt-3 text-base font-bold text-slate-900">{{ $student->name }}</p>
                        <div class="mt-1 flex flex-wrap justify-center gap-2 text-xs font-semibold text-slate-500">
                            <span class="rounded-full bg-white px-2.5 py-1 ring-1 ring-slate-200">NIS {{ $student->nis }}</span>
                            @if ($student->nisn)
                                <span class="rounded-full bg-white px-2.5 py-1 ring-1 ring-slate-200">NISN {{ $student->nisn }}</span>
                            @endif
                            @if ($isAdmin)
                                <span class="rounded-full bg-white px-2.5 py-1 ring-1 ring-slate-200">{{ $student->schoolClass?->name ?? 'Belum ditempatkan' }}</span>
                            @endif
                        </div>

                        @if (! $isAdmin)
                            <div class="mt-5 w-full rounded-xl border border-dashed border-slate-300 bg-white p-3 text-left">
                                <label for="profile-photo" class="mb-2 block text-sm font-semibold text-slate-700">Foto Profil <span class="font-normal text-slate-400">(maks. 2 MB)</span></label>
                                <input id="profile-photo" type="file" name="photo" form="biodata-form" accept="image/jpeg,image/png,image/webp" data-max-file-size="2097152" class="block w-full text-xs text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-blue-900 file:px-3 file:py-2 file:text-xs file:font-semibold file:text-white hover:file:bg-blue-800">
                                <p class="mt-2 text-xs text-slate-400">Foto ikut tersimpan saat klik Simpan Biodata.</p>
                            </div>
                        @endif
                    </div>
            </section>

            <form id="biodata-form" action="{{ $biodataUpdateRoute }}" method="POST" enctype="multipart/form-data" class="space-y-0" data-biodata-progress-form>
                @csrf
                @method('PUT')

                @if ($errors->any())
                    <div class="mx-5 mt-5 rounded-2xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-700 sm:mx-6">
                        <div class="flex gap-3">
                            <i class="fa-solid fa-circle-exclamation mt-0.5 text-rose-600"></i>
                            <div>
                                <p class="font-bold text-rose-800">Ada data yang belum bisa disimpan.</p>
                                <ul class="mt-2 list-disc space-y-1 pl-5">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                @endif

                <section class="px-5 py-7 sm:px-6 sm:py-8">
                    <div class="mb-5 flex items-center gap-3 border-b border-slate-100 pb-4">
                        <span class="flex size-10 items-center justify-center rounded-xl bg-blue-50 text-blue-800"><i class="fa-solid fa-user"></i></span>
                        <div><h2 class="text-base font-bold text-slate-900">Data Pribadi</h2><p class="text-sm leading-5 text-slate-500">Identitas utama dan kontak aktif.</p></div>
                    </div>
                    <div class="mb-4 flex items-start gap-2 rounded-xl border border-rose-200 bg-rose-50 px-3 py-2.5 text-xs font-medium text-rose-700">
                        <i class="fa-solid fa-lock mt-0.5 text-rose-600"></i>
                        <span>NIS, NISN, dan nama lengkap berasal dari sistem dan tidak dapat diubah.</span>
                    </div>
                    <div class="grid gap-4 grid-cols-1 sm:grid-cols-2">
                        <x-form.input name="nis" label="NIS" icon="fa-solid fa-id-card" :value="$student->nis" readonly />
                        <x-form.input name="nisn" label="NISN" icon="fa-solid fa-fingerprint" :value="$student->nisn" readonly />
                        <div class="sm:col-span-2">
                            <x-form.input name="name" label="Nama Lengkap" icon="fa-solid fa-user-graduate" :value="$student->name" readonly />
                        </div>
                        <x-form.select name="gender" label="Jenis Kelamin" icon="fa-solid fa-venus-mars" data-progress-required data-progress-section="personal">
                            <option value="">Pilih jenis kelamin</option>
                            <option value="male" @selected(old('gender', $profile?->gender) === 'male')>Laki-laki</option>
                            <option value="female" @selected(old('gender', $profile?->gender) === 'female')>Perempuan</option>
                        </x-form.select>
                        <x-form.input name="birth_place" label="Tempat Lahir" icon="fa-solid fa-location-dot" :value="old('birth_place', $profile?->birth_place)" data-progress-required data-progress-section="personal" />
                        <x-form.input name="birth_date" label="Tanggal Lahir" icon="fa-solid fa-cake-candles" type="date" :value="old('birth_date', $profile?->birth_date?->format('Y-m-d'))" data-progress-required data-progress-section="personal" />
                        <x-form.input name="phone" label="No WA Aktif" icon="fa-solid fa-phone" type="tel" inputmode="numeric" :value="old('phone', $profile?->phone)" placeholder="08xxxxxxxxxx" data-progress-required data-progress-section="personal" />
                    </div>
                </section>

                <section class="border-t border-slate-100 px-5 py-7 sm:px-6 sm:py-8">
                    <div class="mb-5 flex items-center gap-3 border-b border-slate-100 pb-4">
                        <span class="flex size-10 items-center justify-center rounded-xl bg-sky-50 text-sky-800"><i class="fa-solid fa-map-location-dot"></i></span>
                        <div><h2 class="text-base font-bold text-slate-900">Alamat</h2><p class="text-sm leading-5 text-slate-500">Alamat domisili lengkap saat ini.</p></div>
                    </div>
                    <div class="grid gap-4 grid-cols-1 sm:grid-cols-2">
                        <x-form.select name="province" label="Provinsi" icon="fa-solid fa-map" class="select2" data-region-select="province" :data-initial="old('province', $profile?->province)" data-placeholder="Pilih provinsi" data-progress-required data-progress-section="address">
                            <option value="">Memuat provinsi...</option>
                        </x-form.select>
                        <x-form.select name="city" label="Kota / Kabupaten" icon="fa-solid fa-city" class="select2" data-region-select="city" :data-initial="old('city', $profile?->city)" data-placeholder="Pilih kabupaten/kota" data-progress-required data-progress-section="address" disabled>
                            <option value="">Pilih kabupaten/kota</option>
                        </x-form.select>
                        <x-form.select name="district" label="Kecamatan" icon="fa-solid fa-map-location-dot" class="select2" data-region-select="district" :data-initial="old('district', $profile?->district)" data-placeholder="Pilih kecamatan" data-progress-required data-progress-section="address" disabled>
                            <option value="">Pilih kecamatan</option>
                        </x-form.select>
                        <x-form.select name="village" label="Kelurahan / Desa" icon="fa-solid fa-house-chimney" class="select2" data-region-select="village" :data-initial="old('village', $profile?->village)" data-placeholder="Pilih kelurahan/desa" data-progress-required data-progress-section="address" disabled>
                            <option value="">Pilih kelurahan/desa</option>
                        </x-form.select>
                        <x-form.input name="postal_code" label="Kode Pos" icon="fa-solid fa-envelopes-bulk" inputmode="numeric" :value="old('postal_code', $profile?->postal_code)" placeholder="40135" data-progress-required data-progress-section="address" />
                        <div class="sm:col-span-2">
                            <x-form.textarea name="address" label="Alamat Rumah" icon="fa-solid fa-location-crosshairs" :value="old('address', $profile?->address)" placeholder="Jalan, RT/RW, No. Rumah, detail alamat" data-progress-required data-progress-section="address" rows="3" />
                        </div>
                    </div>
                </section>

                <section class="border-t border-slate-100 px-5 py-7 sm:px-6 sm:py-8">
                    <div class="mb-5 flex items-center gap-3 border-b border-slate-100 pb-4">
                        <span class="flex size-10 items-center justify-center rounded-xl bg-rose-50 text-rose-700"><i class="fa-solid fa-heart-pulse"></i></span>
                        <div><h2 class="text-base font-bold text-slate-900">Data Fisik & Kesehatan</h2><p class="text-sm leading-5 text-slate-500">Tinggi, berat dan riwayat kesehatan.</p></div>
                    </div>
                    <div class="grid gap-4 grid-cols-2">
                        <x-form.input name="height_cm" label="Tinggi Badan (cm)" icon="fa-solid fa-ruler-vertical" type="number" inputmode="numeric" min="100" max="250" :value="old('height_cm', $profile?->height_cm)" placeholder="170" data-progress-required data-progress-section="physical" />
                        <x-form.input name="weight_kg" label="Berat Badan (kg)" icon="fa-solid fa-weight-scale" type="number" inputmode="numeric" min="20" max="200" :value="old('weight_kg', $profile?->weight_kg)" placeholder="60" data-progress-required data-progress-section="physical" />
                    </div>
                    <div class="mt-4">
                        <x-form.textarea name="medical_history" label="Apakah Ada Riwayat Kesehatan/Penyakit" icon="fa-solid fa-notes-medical" :value="old('medical_history', $profile?->medical_history)" placeholder="Jika ada silahkan isi dan jika tidak ada cukup tuliskan (-)" data-progress-required data-progress-section="physical" rows="3" />
                    </div>
                    <div class="mt-4 grid gap-4 grid-cols-1 lg:grid-cols-3">
                        <x-form.select name="mcu_status" label="Status Medical Check-Up (MCU) Mandiri" icon="fa-solid fa-file-medical" data-progress-required data-progress-section="physical">
                            <option value="">Pilih status MCU</option>
                            <option value="belum" @selected(old('mcu_status', $profile?->mcu_status) === 'belum')>Belum</option>
                            <option value="proses" @selected(old('mcu_status', $profile?->mcu_status) === 'proses')>Proses</option>
                            <option value="sudah" @selected(old('mcu_status', $profile?->mcu_status) === 'sudah')>Sudah</option>
                        </x-form.select>
                        <div data-mcu-extra-wrapper>
                            <x-form.input name="mcu_count" label="Jumlah Medical Check-Up" icon="fa-solid fa-hashtag" type="number" min="1" max="99" :value="old('mcu_count', $profile?->mcu_count)" data-mcu-extra />
                        </div>
                        <div data-mcu-extra-wrapper>
                            <x-form.input name="mcu_last_date" label="Tanggal Medical Check-Up Terakhir" icon="fa-solid fa-calendar-check" type="date" :value="old('mcu_last_date', $profile?->mcu_last_date?->format('Y-m-d'))" data-mcu-extra />
                        </div>
                    </div>
                </section>

                <section class="border-t border-slate-100 px-5 py-7 sm:px-6 sm:py-8">
                    <div class="mb-5 flex items-center gap-3 border-b border-slate-100 pb-4">
                        <span class="flex size-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-700"><i class="fa-solid fa-people-roof"></i></span>
                        <div><h2 class="text-base font-bold text-slate-900">Biodata Orang Tua</h2><p class="text-sm leading-5 text-slate-500">Data kontak dan pekerjaan orang tua.</p></div>
                    </div>
                    <div class="grid gap-4 grid-cols-1 sm:grid-cols-2">
                        <x-form.input name="parent_father_name" label="Nama Ayah" icon="fa-solid fa-person" :value="old('parent_father_name', $profile?->parent_father_name)" data-progress-required data-progress-section="parents" />
                        <x-form.input name="parent_father_occupation" label="Pekerjaan Ayah" icon="fa-solid fa-briefcase" :value="old('parent_father_occupation', $profile?->parent_father_occupation)" data-progress-required data-progress-section="parents" />
                        <x-form.input name="parent_mother_name" label="Nama Ibu" icon="fa-solid fa-person-dress" :value="old('parent_mother_name', $profile?->parent_mother_name)" data-progress-required data-progress-section="parents" />
                        <x-form.input name="parent_mother_occupation" label="Pekerjaan Ibu" icon="fa-solid fa-briefcase" :value="old('parent_mother_occupation', $profile?->parent_mother_occupation)" data-progress-required data-progress-section="parents" />
                        <x-form.input name="parent_phone" label="Nomor Orang Tua" icon="fa-solid fa-phone" type="tel" :value="old('parent_phone', $profile?->parent_phone)" data-progress-required data-progress-section="parents" />
                        <div class="sm:col-span-2">
                            <x-form.textarea name="parent_address" label="Alamat Orang Tua" icon="fa-solid fa-map-location-dot" :value="old('parent_address', $profile?->parent_address)" data-progress-required data-progress-section="parents" rows="3" />
                        </div>
                    </div>
                </section>

                <section class="border-t border-slate-100 px-5 py-7 sm:px-6 sm:py-8">
                    <div class="mb-5 flex items-center gap-3 border-b border-slate-100 pb-4">
                        <span class="flex size-10 items-center justify-center rounded-xl bg-indigo-50 text-indigo-700"><i class="fa-solid fa-graduation-cap"></i></span>
                        <div><h2 class="text-base font-bold text-slate-900">Pilihan Kampus</h2><p class="text-sm leading-5 text-slate-500">Rencana melanjutkan kuliah.</p></div>
                    </div>
                    <div class="grid gap-4 grid-cols-1 lg:grid-cols-3">
                        @foreach ([1 => 'Pilihan 1 (Kampus)', 2 => 'Pilihan 2 (Kampus)', 3 => 'Pilihan 3 (Kampus)'] as $choice => $label)
                            @php($field = 'university_choice_'.$choice.'_id')
                            @php($majorField = 'university_major_choice_'.$choice)
                            <div class="space-y-3">
                                <x-form.select :name="$field" :label="$label" icon="fa-solid fa-building-columns" class="select2" data-placeholder="Pilih kampus" data-progress-required data-progress-section="campus_choice">
                                    <option value="">Pilih kampus</option>
                                    @foreach (['negeri' => 'Perguruan Tinggi Negeri', 'swasta' => 'Perguruan Tinggi Swasta', 'kedinasan' => 'Perguruan Tinggi Kedinasan', 'lainnya' => 'Lainnya'] as $type => $typeLabel)
                                        @if (($universities[$type] ?? collect())->isNotEmpty())
                                            <optgroup label="{{ $typeLabel }}">
                                                @foreach ($universities[$type] as $university)
                                                    <option value="{{ $university->id }}" data-type="{{ $university->type }}" @selected(old($field, $profile?->{$field}) == $university->id)>{{ $university->name }}{{ $university->short_name ? ' ('.$university->short_name.')' : '' }}</option>
                                                @endforeach
                                            </optgroup>
                                        @endif
                                    @endforeach
                                </x-form.select>
                                <div data-campus-major-wrapper>
                                    <x-form.input :name="$majorField" :label="'Jurusan Pilihan '.$choice" icon="fa-solid fa-code-branch" :value="old($majorField, $profile?->{$majorField})" placeholder="Contoh: Teknik Informatika" data-campus-major />
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>

                <section class="border-t border-slate-100 px-5 py-7 sm:px-6 sm:py-8">
                    <div class="mb-5 flex items-center gap-3 border-b border-slate-100 pb-4">
                        <span class="flex size-10 items-center justify-center rounded-xl bg-violet-50 text-violet-700"><i class="fa-solid fa-list-check"></i></span>
                        <div><h2 class="text-base font-bold text-slate-900">Tes Kemampuan Akademik (TKA)</h2><p class="text-sm leading-5 text-slate-500">Pilih satu atau lebih mapel TKA.</p></div>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-white p-4 sm:p-5" data-tka-group>
                        @php($selectedTkaIds = collect(old('tka_subjects', $student->tkaSelections->pluck('tka_subject_id')->all()))->filter(fn ($value) => filled($value))->values()->all() ?: [null])
                        @php($filledTkaCount = collect($selectedTkaIds)->filter(fn ($value) => filled($value))->count())
                        <input type="hidden" data-progress-required data-progress-section="tka" data-tka-progress-flag value="{{ $filledTkaCount > 0 ? '1' : '' }}">
                        <div class="space-y-3" data-tka-list>
                            @foreach ($selectedTkaIds as $index => $selectedTkaId)
                                <div class="rounded-xl border border-slate-200 bg-slate-50/70 p-3 sm:p-4" data-repeat-item>
                                    <div class="mb-3 flex items-center justify-between gap-3">
                                        <p class="inline-flex items-center gap-2 text-xs font-bold uppercase tracking-wide text-slate-500"><span class="flex size-6 items-center justify-center rounded-full bg-white text-blue-900 ring-1 ring-slate-200" data-repeat-counter>{{ $loop->iteration }}</span> Mapel TKA</p>
                                        @if (! $isAdmin)<button type="button" class="inline-flex h-8 items-center justify-center gap-2 rounded-lg bg-rose-50 px-3 text-xs font-bold text-rose-600 ring-1 ring-rose-100 transition hover:bg-rose-100" data-remove-repeat><i class="fa-solid fa-trash-can"></i> Hapus</button>@endif
                                    </div>
                                    <label class="space-y-1.5 text-sm font-semibold text-slate-700">Mapel TKA
                                        <select name="tka_subjects[]" data-tka-select class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 outline-none transition focus:border-blue-700 focus:ring-4 focus:ring-blue-700/10"><option value="">Pilih mapel TKA</option>@foreach (($tkaSubjects ?? collect()) as $tkaSubject)<option value="{{ $tkaSubject->id }}" @selected((int) $selectedTkaId === (int) $tkaSubject->id)>{{ $tkaSubject->name }}</option>@endforeach</select>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                        <div class="mt-3">
                            @if (! $isAdmin)
                                <button type="button" class="inline-flex h-10 w-full items-center justify-center gap-2 rounded-lg bg-blue-900 px-4 text-xs font-bold text-white transition hover:bg-blue-800" data-add-tka><i class="fa-solid fa-plus"></i> Tambah TKA Lain</button>
                            @endif
                        </div>
                    </div>
                </section>

                <section class="border-t border-slate-100 px-5 py-7 sm:px-6 sm:py-8">
                    <div class="mb-5 flex items-center gap-3 border-b border-slate-100 pb-4">
                        <span class="flex size-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-700"><i class="fa-solid fa-trophy"></i></span>
                        <div><h2 class="text-base font-bold text-slate-900">Aktivitas & Evaluasi Diri</h2><p class="text-sm leading-5 text-slate-500">Prestasi, organisasi dan pengembangan diri.</p></div>
                    </div>
                    <div class="grid gap-4 grid-cols-1">
                        <div class="rounded-2xl border border-slate-200 bg-white p-4 sm:p-5" data-achievement-group>
                            <div class="mb-4 flex gap-3">
                                <span class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-amber-50 text-amber-700"><i class="fa-solid fa-medal"></i></span>
                                <div>
                                    <div class="flex flex-wrap items-center gap-2">
                                        <h3 class="text-sm font-bold text-slate-900">Prestasi Akademik atau Non Akademik</h3>
                                        <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-slate-500">Opsional</span>
                                    </div>
                                    <p class="mt-1 text-xs leading-5 text-slate-500">Pilih jawaban terlebih dahulu. Jika memilih Ya, lengkapi minimal satu prestasi bila ada.</p>
                                </div>
                            </div>
                            <x-form.select name="achievement_status" label="Apakah kamu memiliki prestasi akademik atau non akademik?" icon="fa-solid fa-circle-question" data-achievement-status>
                                <option value="">Pilih jawaban</option>
                                <option value="tidak" @selected($achievementStatus === 'tidak')>Tidak</option>
                                <option value="ya" @selected($achievementStatus === 'ya')>Ya</option>
                            </x-form.select>
                            <div class="mt-4" data-achievement-details-wrapper>
                                <div class="rounded-xl border border-blue-100 bg-blue-50/60 px-3 py-2.5 text-xs leading-5 text-blue-900">
                                    <span class="font-bold">Tips:</span> Contoh nama prestasi: Juara 1 Olimpiade Matematika, Finalis Lomba Desain Poster, atau Peserta LKS.
                                </div>
                                <div class="mt-4 space-y-3" data-achievement-list>
                                @foreach (old('achievements', $student->achievements->map(fn ($item) => $item->only(['type', 'name', 'level', 'year']))->all() ?: [[]]) as $index => $achievement)
                                    <div class="rounded-xl border border-slate-200 bg-slate-50/70 p-3 sm:p-4" data-repeat-item>
                                        <div class="mb-3 flex items-center justify-between gap-3">
                                            <p class="inline-flex items-center gap-2 text-xs font-bold uppercase tracking-wide text-slate-500"><span class="flex size-6 items-center justify-center rounded-full bg-white text-blue-900 ring-1 ring-slate-200" data-repeat-counter>{{ $loop->iteration }}</span> Data prestasi</p>
                                            @if (! $isAdmin)<button type="button" class="inline-flex h-8 items-center justify-center gap-2 rounded-lg bg-rose-50 px-3 text-xs font-bold text-rose-600 ring-1 ring-rose-100 transition hover:bg-rose-100" data-remove-repeat><i class="fa-solid fa-trash-can"></i> Hapus</button>@endif
                                        </div>
                                        <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                                            <label class="space-y-1.5 text-sm font-semibold text-slate-700">Jenis
                                                <select name="achievements[{{ $index }}][type]" class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 outline-none transition focus:border-blue-700 focus:ring-4 focus:ring-blue-700/10"><option value="">Pilih jenis</option><option value="akademik" @selected(($achievement['type'] ?? null) === 'akademik')>Akademik</option><option value="non_akademik" @selected(($achievement['type'] ?? null) === 'non_akademik')>Non akademik</option></select>
                                            </label>
                                            <label class="space-y-1.5 text-sm font-semibold text-slate-700 sm:col-span-2 xl:col-span-1">Nama prestasi
                                                <input name="achievements[{{ $index }}][name]" value="{{ $achievement['name'] ?? '' }}" class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-blue-700 focus:ring-4 focus:ring-blue-700/10" placeholder="Contoh: Juara 1 Olimpiade">
                                            </label>
                                            <label class="space-y-1.5 text-sm font-semibold text-slate-700">Tingkat
                                                <select name="achievements[{{ $index }}][level]" class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 outline-none transition focus:border-blue-700 focus:ring-4 focus:ring-blue-700/10"><option value="">Pilih tingkat</option>@foreach (['kab_kota'=>'Kab/Kota','provinsi'=>'Provinsi','nasional'=>'Nasional','internasional'=>'Internasional'] as $value => $label)<option value="{{ $value }}" @selected(($achievement['level'] ?? null) === $value)>{{ $label }}</option>@endforeach</select>
                                            </label>
                                            <label class="space-y-1.5 text-sm font-semibold text-slate-700">Tahun
                                                <input name="achievements[{{ $index }}][year]" value="{{ $achievement['year'] ?? '' }}" type="number" min="2000" max="{{ now()->year + 1 }}" class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-blue-700 focus:ring-4 focus:ring-blue-700/10" placeholder="{{ now()->year }}">
                                            </label>
                                        </div>
                                        @if (! $isAdmin)
                                            <label class="mt-3 block rounded-xl border border-dashed border-slate-300 bg-white p-3 text-xs font-semibold text-slate-600">
                                                <span class="mb-2 flex items-center gap-2"><i class="fa-solid fa-paperclip text-slate-400"></i> Sertifikat pendukung <span class="font-medium text-slate-400">(opsional, max 2MB)</span></span>
                                                <input name="achievements[{{ $index }}][certificate]" type="file" accept="application/pdf,image/jpeg,image/png" data-max-file-size="2097152" class="block w-full text-xs text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-blue-900 file:px-3 file:py-2 file:text-xs file:font-semibold file:text-white hover:file:bg-blue-800">
                                            </label>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                            <div class="mt-3">
                                @if (! $isAdmin)
                                    <button type="button" class="inline-flex h-10 w-full items-center justify-center gap-2 rounded-lg bg-blue-900 px-4 text-xs font-bold text-white transition hover:bg-blue-800" data-add-achievement><i class="fa-solid fa-plus"></i> Tambah Prestasi Lain</button>
                                @endif
                            </div>
                            </div>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-white p-4 sm:p-5" data-organization-group>
                            <div class="mb-4 flex gap-3">
                                <span class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-cyan-50 text-cyan-700"><i class="fa-solid fa-people-group"></i></span>
                                <div>
                                    <h3 class="text-sm font-bold text-slate-900">Organisasi dan Ekskul di Sekolah   <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-slate-500">Opsional</span></h3>
                                    <p class="mt-1 text-xs leading-5 text-slate-500">Pilih status terlebih dahulu. Jika memilih Ya, lengkapi minimal satu organisasi atau ekskul.</p>
                                </div>
                            </div>
                            <x-form.select name="organization_status" label="Apakah kamu mengikuti organisasi atau ekskul di sekolah?" icon="fa-solid fa-circle-question" data-progress-required data-progress-section="school_activity" data-organization-status>
                                <option value="">Pilih jawaban</option>
                                <option value="tidak" @selected(old('organization_status', $profile?->organization_status) === 'tidak')>Tidak</option>
                                <option value="ya" @selected(old('organization_status', $profile?->organization_status) === 'ya')>Ya</option>
                            </x-form.select>
                            <div class="mt-4" data-organization-name-wrapper>
                                <div class="rounded-xl border border-emerald-100 bg-emerald-50/60 px-3 py-3">
                                    <div>
                                        <p class="text-xs font-bold uppercase tracking-wide text-emerald-700">Detail kegiatan</p>
                                        <p class="mt-1 text-xs leading-5 text-emerald-900">Tuliskan nama organisasi/ekskul, peran, tingkat, dan tahun aktif.</p>
                                    </div>
                                </div>
                                <div class="mt-3 space-y-3" data-organization-list>
                                    @foreach (old('organizations', $student->organizations->map(fn ($item) => $item->only(['name', 'position', 'level', 'year']))->all() ?: [[]]) as $index => $organization)
                                        <div class="rounded-xl border border-slate-200 bg-slate-50/70 p-3 sm:p-4" data-repeat-item>
                                            <div class="mb-3 flex items-center justify-between gap-3">
                                                <p class="inline-flex items-center gap-2 text-xs font-bold uppercase tracking-wide text-slate-500"><span class="flex size-6 items-center justify-center rounded-full bg-white text-blue-900 ring-1 ring-slate-200" data-repeat-counter>{{ $loop->iteration }}</span> Data organisasi</p>
                                                @if (! $isAdmin)<button type="button" class="inline-flex h-8 items-center justify-center gap-2 rounded-lg bg-rose-50 px-3 text-xs font-bold text-rose-600 ring-1 ring-rose-100 transition hover:bg-rose-100" data-remove-repeat><i class="fa-solid fa-trash-can"></i> Hapus</button>@endif
                                            </div>
                                            <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                                                <label class="space-y-1.5 text-sm font-semibold text-slate-700">Nama organisasi/ekskul
                                                    <input name="organizations[{{ $index }}][name]" value="{{ $organization['name'] ?? '' }}" class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-blue-700 focus:ring-4 focus:ring-blue-700/10" placeholder="Contoh: OSIS" data-organization-detail>
                                                </label>
                                                <label class="space-y-1.5 text-sm font-semibold text-slate-700">Jabatan/peran
                                                    <input name="organizations[{{ $index }}][position]" value="{{ $organization['position'] ?? '' }}" class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-blue-700 focus:ring-4 focus:ring-blue-700/10" placeholder="Contoh: Ketua" data-organization-detail>
                                                </label>
                                                <label class="space-y-1.5 text-sm font-semibold text-slate-700">Tingkat
                                                    <select name="organizations[{{ $index }}][level]" class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 outline-none transition focus:border-blue-700 focus:ring-4 focus:ring-blue-700/10" data-organization-detail><option value="">Pilih tingkat</option>@foreach (['sekolah'=>'Sekolah','kab_kota'=>'Kab/Kota','provinsi'=>'Provinsi','nasional'=>'Nasional','internasional'=>'Internasional'] as $value => $label)<option value="{{ $value }}" @selected(($organization['level'] ?? null) === $value)>{{ $label }}</option>@endforeach</select>
                                                </label>
                                                <label class="space-y-1.5 text-sm font-semibold text-slate-700">Tahun
                                                    <input name="organizations[{{ $index }}][year]" value="{{ $organization['year'] ?? '' }}" type="number" min="2000" max="{{ now()->year + 1 }}" class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-blue-700 focus:ring-4 focus:ring-blue-700/10" placeholder="{{ now()->year }}" data-organization-detail>
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <div class="mt-3">
                                    @if (! $isAdmin)
                                        <button type="button" class="inline-flex h-10 w-full items-center justify-center gap-2 rounded-lg bg-blue-900 px-4 text-xs font-bold text-white transition hover:bg-blue-800" data-add-organization><i class="fa-solid fa-plus"></i> Tambah Organisasi Lain</button>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <x-form.textarea name="self_improvement_notes" label="Hal yang Perlu Ditingkatkan (Evaluasi Diri)" icon="fa-solid fa-chart-line" :value="old('self_improvement_notes', $profile?->self_improvement_notes)" placeholder="Tuliskan hal yang ingin kamu tingkatkan" data-progress-required data-progress-section="school_activity" rows="3" />
                    </div>

                    <div class="mt-8 rounded-2xl border border-slate-200 bg-white p-4 sm:p-5">
                        <div class="mb-4 flex items-center gap-3">
                            <span class="flex size-9 items-center justify-center rounded-xl bg-blue-50 text-blue-800"><i class="fa-solid fa-folder-open"></i></span>
                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <h3 class="text-sm font-bold text-slate-900">Dokumen Pribadi</h3>
                                    <span class="rounded-full bg-slate-200 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-slate-600">Opsional</span>
                                </div>
                                <p class="text-xs leading-4 text-slate-500">Upload ijazah, akte, dan kartu keluarga. PDF/JPG/PNG maksimal 2MB per file.</p>
                            </div>
                        </div>

                        <div class="grid gap-3 md:grid-cols-3">
                            @foreach (['ijazah_smp' => 'Ijazah SMP', 'akte' => 'Akte', 'kartu_keluarga' => 'Kartu Keluarga'] as $type => $label)
                                @php($document = $student->documents->filter(fn ($document) => in_array($document->document_type, $personalDocumentLabels[$label], true))->last())
                                <div class="rounded-xl border border-slate-200 bg-slate-50 p-3" data-personal-document-card="{{ $type }}">
                                    <div class="flex items-start justify-between gap-3">
                                        <div>
                                            <p class="text-sm font-bold text-slate-900">{{ $label }}</p>
                                            <p class="mt-1 text-xs text-slate-500" data-personal-document-name>{{ $document ? $document->original_name : 'Belum diupload' }}</p>
                                        </div>
                                        <i class="fa-solid {{ $document ? 'fa-circle-check text-emerald-500' : 'fa-circle text-slate-300' }} mt-1" data-personal-document-icon></i>
                                    </div>
                                    @if (! $isAdmin)
                                        <input type="file" name="{{ $type }}" accept="application/pdf,image/jpeg,image/png" data-max-file-size="2097152" data-personal-document-file data-personal-document-type="{{ $type }}" data-progress-initial="{{ $document ? '1' : '' }}" class="mt-3 block w-full text-xs text-slate-600 file:mr-2 file:rounded-lg file:border-0 file:bg-blue-900 file:px-3 file:py-2 file:text-xs file:font-semibold file:text-white hover:file:bg-blue-800 disabled:opacity-60" data-progress-required data-progress-section="documents">
                                        <p class="mt-2 text-xs text-slate-400">Dokumen otomatis tersimpan saat file dipilih.</p>
                                        <p class="mt-1 hidden text-xs font-semibold" data-personal-document-status></p>
                                    @endif
                                    @if ($document)
                                        <div class="mt-3 flex gap-2" data-personal-document-actions>
                                            <a href="{{ $isAdmin ? route('bk.students.biodata.documents.download', [$student, $document]) : route('siswa.biodata.documents.download', $document) }}" class="inline-flex h-9 flex-1 items-center justify-center gap-2 rounded-lg bg-white px-3 text-xs font-bold text-blue-800 ring-1 ring-slate-200 hover:bg-blue-50" data-personal-document-download><i class="fa-solid fa-download"></i> Download</a>
                                            <button type="submit" form="certificate-delete-{{ $document->id }}" class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-rose-50 text-rose-600 ring-1 ring-rose-100 hover:bg-rose-100" aria-label="Hapus {{ $label }}"><i class="fa-solid fa-trash text-xs"></i></button>
                                        </div>
                                    @elseif (! $isAdmin)
                                        <div class="mt-3 hidden gap-2" data-personal-document-actions>
                                            <a href="#" class="inline-flex h-9 flex-1 items-center justify-center gap-2 rounded-lg bg-white px-3 text-xs font-bold text-blue-800 ring-1 ring-slate-200 hover:bg-blue-50" data-personal-document-download><i class="fa-solid fa-download"></i> Download</a>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="mt-8 rounded-2xl border border-dashed border-amber-200 bg-amber-50/40 p-4 sm:p-5">
                        <p class="text-xs font-bold uppercase tracking-wide text-slate-400">Daftar Sertifikat Prestasi</p>
                        <div class="mt-3 space-y-2">
                            @forelse ($student->achievements as $achievement)
                                @foreach ($achievement->documents as $document)
                                <div class="flex items-center justify-between gap-3 rounded-xl border border-slate-200 bg-white p-3">
                                    <div class="min-w-0 flex-1">
                                        <p class="truncate text-sm font-bold leading-4 text-slate-800">{{ $achievement->name }}</p>
                                        <a href="{{ $isAdmin ? route('bk.students.biodata.documents.download', [$student, $document]) : route('siswa.biodata.documents.download', $document) }}" class="mt-0.5 block truncate text-xs font-medium text-blue-800 hover:text-blue-900">{{ $document->original_name }}</a>
                                        <p class="text-xs text-slate-400">{{ number_format($document->file_size/1024, 0) }} KB · {{ $document->created_at->format('d M Y') }}</p>
                                    </div>
                                    <button type="submit" form="certificate-delete-{{ $document->id }}" class="inline-flex size-10 shrink-0 items-center justify-center rounded-xl border border-rose-100 bg-rose-50 text-rose-600 transition hover:bg-rose-100" aria-label="Hapus sertifikat {{ $document->document_type }}"><i class="fa-solid fa-trash text-sm"></i></button>
                                </div>
                                @endforeach
                            @empty
                                <div class="rounded-xl border border-dashed border-slate-200 bg-white p-6 text-center">
                                    <div class="mx-auto flex size-10 items-center justify-center rounded-xl bg-slate-50 text-slate-400"><i class="fa-solid fa-file-circle-xmark"></i></div>
                                    <p class="mt-2 text-sm font-medium text-slate-500">Belum ada sertifikat</p>
                                    <p class="text-xs text-slate-400">Upload sertifikat prestasi kamu di atas.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </section>

                <div class="sticky bottom-0 z-10 border-t border-slate-200 bg-white/95 px-5 py-4 shadow-[0_-8px_20px_-16px_rgba(15,23,42,0.35)] backdrop-blur lg:static lg:flex lg:justify-end lg:bg-slate-50/70 lg:px-6 lg:py-5 lg:shadow-none">
                    <x-button type="submit" class="h-12 w-full text-base font-bold shadow-lg shadow-blue-900/10 lg:w-auto lg:px-8"><i class="fa-solid fa-save"></i> Simpan Biodata</x-button>
                    <p class="mt-2 text-center text-xs text-slate-400 lg:hidden">Pastikan semua data wajib terisi sebelum menyimpan.</p>
                </div>
            </form>
        </x-card>

        @foreach ($student->documents as $document)
            <form id="certificate-delete-{{ $document->id }}" action="{{ $isAdmin ? route('bk.students.biodata.documents.destroy', [$student, $document]) : route('siswa.biodata.documents.destroy', $document) }}" method="POST" class="hidden">
                @csrf
                @method('DELETE')
            </form>
        @endforeach
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                document.querySelectorAll('select[name^="university_choice_"]').forEach(function (select) {
                    const wrapper = select.closest('.space-y-3')?.querySelector('[data-campus-major-wrapper]');
                    const input = wrapper?.querySelector('[data-campus-major]');
                    const toggle = function () {
                        const isKedinasan = select.selectedOptions[0]?.dataset.type === 'kedinasan';
                        if (wrapper) wrapper.classList.toggle('hidden', isKedinasan);
                        if (isKedinasan && input) input.value = '';
                    };
                    select.addEventListener('change', toggle);
                    if (window.$) window.$(select).on('change', toggle);
                    toggle();
                });

                const mcuStatus = document.querySelector('[name="mcu_status"]');
                const mcuExtraWrappers = document.querySelectorAll('[data-mcu-extra-wrapper]');
                const toggleMcu = function () {
                    const showExtras = mcuStatus?.value === 'proses' || mcuStatus?.value === 'sudah';
                    mcuExtraWrappers.forEach(function (wrapper) {
                        wrapper.classList.toggle('hidden', !showExtras);
                        if (!showExtras) {
                            wrapper.querySelectorAll('input').forEach(function (input) {
                                input.value = '';
                            });
                        }
                    });
                };
                if (mcuStatus) {
                    mcuStatus.addEventListener('change', toggleMcu);
                    if (window.$) window.$(mcuStatus).on('change', toggleMcu);
                    toggleMcu();
                }

                const biodataForm = document.querySelector('[data-biodata-progress-form]');
                const maxFileSize = 2 * 1024 * 1024;
                const maxPersonalDocumentSize = 12 * 1024 * 1024;
                const validateFileSize = function (input) {
                    const oversized = Array.from(input.files || []).find(function (file) {
                        return file.size > maxFileSize;
                    });
                    const message = oversized ? 'Ukuran file "' + oversized.name + '" melebihi 2 MB.' : '';
                    input.setCustomValidity(message);
                    return !oversized;
                };
                const validatePersonalDocumentSize = function () {
                    const inputs = Array.from(document.querySelectorAll('[data-personal-document-file]'));
                    const total = inputs.reduce(function (sum, input) {
                        return sum + Array.from(input.files || []).reduce(function (fileSum, file) {
                            return fileSum + file.size;
                        }, 0);
                    }, 0);
                    const message = total > maxPersonalDocumentSize ? 'Total ukuran ijazah, akte, dan kartu keluarga maksimal 12 MB.' : '';
                    inputs.forEach(function (input) { input.setCustomValidity(message); });
                    return !message;
                };
                document.addEventListener('change', function (event) {
                    if (!event.target.matches('input[type="file"][data-max-file-size]')) return;
                    const fileInput = event.target;
                    const sizeOk = validateFileSize(fileInput);
                    let totalOk = true;
                    if (fileInput.matches('[data-personal-document-file]')) totalOk = validatePersonalDocumentSize();
                    fileInput.reportValidity();
                    if (sizeOk && totalOk && fileInput.matches('[data-personal-document-file]')) uploadPersonalDocument(fileInput);
                });
                biodataForm?.addEventListener('submit', function (event) {
                    const valid = Array.from(biodataForm.querySelectorAll('input[type="file"][data-max-file-size]')).every(validateFileSize) && validatePersonalDocumentSize();
                    if (!valid) {
                        event.preventDefault();
                        biodataForm.querySelector('input[type="file"]:invalid')?.reportValidity();
                    }
                });

                const personalDocumentUploadUrl = @json(route('siswa.biodata.personal-documents.store'));
                const personalDocumentDestroyTemplate = @json(route('siswa.biodata.documents.destroy', ['document' => '__DOCUMENT_ID__']));
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                const setPersonalDocumentStatus = function (card, message, state) {
                    const statusEl = card?.querySelector('[data-personal-document-status]');
                    if (!statusEl) return;
                    statusEl.textContent = message || '';
                    statusEl.classList.remove('hidden', 'text-emerald-600', 'text-rose-600', 'text-slate-500');
                    if (!message) {
                        statusEl.classList.add('hidden');
                        return;
                    }
                    statusEl.classList.add(state === 'ok' ? 'text-emerald-600' : (state === 'error' ? 'text-rose-600' : 'text-slate-500'));
                };
                const refreshPersonalDocumentCard = function (card, doc) {
                    const nameEl = card.querySelector('[data-personal-document-name]');
                    if (nameEl) nameEl.textContent = doc.original_name;
                    const iconEl = card.querySelector('[data-personal-document-icon]');
                    if (iconEl) iconEl.className = 'fa-solid fa-circle-check text-emerald-500 mt-1';
                    const actions = card.querySelector('[data-personal-document-actions]');
                    if (!actions) return;
                    actions.classList.remove('hidden');
                    actions.classList.add('flex');
                    const download = actions.querySelector('[data-personal-document-download]');
                    if (download) download.href = doc.download_url;
                    let deleteForm = window.document.getElementById('certificate-delete-' + doc.id);
                    if (!deleteForm) {
                        deleteForm = window.document.createElement('form');
                        deleteForm.id = 'certificate-delete-' + doc.id;
                        deleteForm.method = 'POST';
                        deleteForm.action = personalDocumentDestroyTemplate.replace('__DOCUMENT_ID__', doc.id);
                        deleteForm.className = 'hidden';
                        deleteForm.innerHTML = '<input type="hidden" name="_token" value="' + csrfToken + '"><input type="hidden" name="_method" value="DELETE">';
                        window.document.body.appendChild(deleteForm);
                    }
                    let deleteButton = actions.querySelector('button[type="submit"]');
                    if (!deleteButton) {
                        deleteButton = window.document.createElement('button');
                        deleteButton.type = 'submit';
                        deleteButton.className = 'inline-flex h-9 w-9 items-center justify-center rounded-lg bg-rose-50 text-rose-600 ring-1 ring-rose-100 hover:bg-rose-100';
                        deleteButton.setAttribute('aria-label', 'Hapus ' + doc.document_type);
                        deleteButton.innerHTML = '<i class="fa-solid fa-trash text-xs"></i>';
                        actions.appendChild(deleteButton);
                    }
                    deleteButton.setAttribute('form', deleteForm.id);
                };
                const uploadPersonalDocument = function (input) {
                    const file = input.files && input.files[0];
                    if (!file) return;
                    const card = input.closest('[data-personal-document-card]');
                    input.disabled = true;
                    setPersonalDocumentStatus(card, 'Mengunggah "' + file.name + '"...', 'progress');
                    const formData = new FormData();
                    formData.append('type', input.getAttribute('data-personal-document-type'));
                    formData.append('file', file);
                    fetch(personalDocumentUploadUrl, {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                        body: formData,
                    }).then(function (response) {
                        return response.json().then(function (payload) {
                            return { ok: response.ok, payload: payload };
                        }).catch(function () {
                            return { ok: false, payload: null };
                        });
                    }).then(function (result) {
                        input.disabled = false;
                        if (!result.ok || !result.payload || !result.payload.ok) {
                            const errors = (result.payload && result.payload.errors) || {};
                            const message = errors.file ? errors.file[0] : (errors.type ? errors.type[0] : ((result.payload && result.payload.message) || 'Upload gagal. Coba lagi.'));
                            input.value = '';
                            setPersonalDocumentStatus(card, message, 'error');
                            return;
                        }
                        input.value = '';
                        input.setAttribute('data-progress-initial', '1');
                        input.setCustomValidity('');
                        refreshPersonalDocumentCard(card, result.payload.document);
                        setPersonalDocumentStatus(card, 'Tersimpan otomatis.', 'ok');
                        input.dispatchEvent(new Event('input', { bubbles: true }));
                    }).catch(function () {
                        input.disabled = false;
                        input.value = '';
                        setPersonalDocumentStatus(card, 'Upload gagal karena koneksi. Coba lagi.', 'error');
                    });
                };

                const organizationStatusEl = document.querySelector('[data-organization-status]');
                const organizationWrapper = document.querySelector('[data-organization-name-wrapper]');
                const achievementStatusEl = document.querySelector('[data-achievement-status]');
                const achievementWrapper = document.querySelector('[data-achievement-details-wrapper]');
                const clearWrapperFields = function (wrapper) {
                    wrapper.querySelectorAll('input, select, textarea').forEach(function (field) {
                        field.value = '';
                        if (typeof field.setCustomValidity === 'function') field.setCustomValidity('');
                    });
                };
                const toggleOrganizations = function () {
                    if (!organizationStatusEl || !organizationWrapper) return;
                    const isYa = organizationStatusEl.value === 'ya';
                    organizationWrapper.classList.toggle('hidden', !isYa);
                    if (!isYa) clearWrapperFields(organizationWrapper);
                };
                const toggleAchievements = function () {
                    if (!achievementStatusEl || !achievementWrapper) return;
                    const isYa = achievementStatusEl.value === 'ya';
                    achievementWrapper.classList.toggle('hidden', !isYa);
                    if (!isYa) clearWrapperFields(achievementWrapper);
                };
                const refreshRepeatCounters = function (list) {
                    list.querySelectorAll('[data-repeat-item]').forEach(function (item, index) {
                        const counter = item.querySelector('[data-repeat-counter]');
                        if (counter) counter.textContent = index + 1;
                    });
                };
                if (organizationStatusEl) {
                    organizationStatusEl.addEventListener('change', toggleOrganizations);
                    if (window.$) window.$(organizationStatusEl).on('change', toggleOrganizations);
                }
                if (achievementStatusEl) {
                    achievementStatusEl.addEventListener('change', toggleAchievements);
                    if (window.$) window.$(achievementStatusEl).on('change', toggleAchievements);
                }
                toggleOrganizations();
                toggleAchievements();
                const refreshTkaProgress = function () {
                    const hasTka = Array.from(document.querySelectorAll('[data-tka-select]')).some(function (select) {
                        return String(select.value ?? '').trim() !== '';
                    });
                    document.querySelectorAll('[data-tka-progress-flag]').forEach(function (flag) {
                        flag.value = hasTka ? '1' : '';
                    });
                };
                document.addEventListener('change', function (event) {
                    if (event.target.matches && event.target.matches('[data-tka-select]')) refreshTkaProgress();
                });
                refreshTkaProgress();

                const cloneItem = function (list) {
                    const item = list.querySelector('[data-repeat-item]');
                    if (!item) return;
                    const clone = item.cloneNode(true);
                    const index = list.querySelectorAll('[data-repeat-item]').length;
                    clone.querySelectorAll('input, select').forEach(function (input) {
                        input.name = input.name.replace(/\[\d+\]/, '[' + index + ']');
                        input.value = '';
                        input.required = false;
                    });
                    list.appendChild(clone);
                    refreshRepeatCounters(list);
                };
                document.querySelector('[data-add-achievement]')?.addEventListener('click', function () { cloneItem(document.querySelector('[data-achievement-list]')); });
                document.querySelector('[data-add-organization]')?.addEventListener('click', function () { cloneItem(document.querySelector('[data-organization-list]')); toggleOrganizations(); });
                document.querySelector('[data-add-tka]')?.addEventListener('click', function () { cloneItem(document.querySelector('[data-tka-list]')); refreshTkaProgress(); });
                document.addEventListener('click', function (event) {
                    const removeButton = event.target.closest('[data-remove-repeat]');
                    if (!removeButton) return;
                    const item = removeButton.closest('[data-repeat-item]');
                    const list = item?.parentElement;
                    if (list && list.querySelectorAll('[data-repeat-item]').length > 1) {
                        item.remove();
                        refreshRepeatCounters(list);
                        if (list.hasAttribute('data-tka-list')) refreshTkaProgress();
                    }
                });
            });
        </script>
    @endpush
@endcomponent
