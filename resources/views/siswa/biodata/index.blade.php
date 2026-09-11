@php
    $isAdmin = $isAdmin ?? false;
    $biodataUpdateRoute = $biodataUpdateRoute ?? route('siswa.biodata.update');
    $biodataBackRoute = $biodataBackRoute ?? route('siswa.dashboard');
    $certificates = $student->documents->reject(fn ($document) => in_array($document->document_type, ['kip', 'kartu_keluarga', 'dokumen_lainnya'], true));
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
                    @foreach (['personal' => 'Pribadi', 'address' => 'Alamat', 'physical' => 'Fisik', 'campus_choice' => 'Kampus', 'career_preparation' => 'Karir', 'school_activity' => 'Aktivitas'] as $key => $label)
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

            <form id="biodata-form" action="{{ $biodataUpdateRoute }}" method="POST" class="space-y-0" data-biodata-progress-form>
                @csrf
                @method('PUT')

                <section class="border-b border-slate-100 bg-slate-50/60 px-5 py-6 sm:px-6">
                    <div class="mx-auto flex max-w-md flex-col items-center text-center">
                        @if (! $isAdmin && $profile?->photo_path)
                            <img src="{{ route('siswa.biodata.photo.show') }}" alt="Foto profil {{ $student->name }}" class="size-20 rounded-2xl border-2 border-white object-cover shadow-sm sm:size-24">
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
                                <input id="profile-photo" type="file" name="photo" form="photo-upload-form" accept="image/jpeg,image/png,image/webp" class="block w-full text-xs text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-blue-900 file:px-3 file:py-2 file:text-xs file:font-semibold file:text-white hover:file:bg-blue-800">
                                <x-button type="submit" form="photo-upload-form" class="mt-3 h-10 w-full text-sm"><i class="fa-solid fa-upload"></i> Upload Foto</x-button>
                            </div>
                        @endif
                    </div>
                </section>

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
                        <x-form.select name="gender" label="Jenis Kelamin" icon="fa-solid fa-venus-mars" data-progress-required data-progress-section="personal" required>
                            <option value="">Pilih jenis kelamin</option>
                            <option value="male" @selected(old('gender', $profile?->gender) === 'male')>Laki-laki</option>
                            <option value="female" @selected(old('gender', $profile?->gender) === 'female')>Perempuan</option>
                        </x-form.select>
                        <x-form.input name="birth_place" label="Tempat Lahir" icon="fa-solid fa-location-dot" :value="old('birth_place', $profile?->birth_place)" data-progress-required data-progress-section="personal" required />
                        <x-form.input name="birth_date" label="Tanggal Lahir" icon="fa-solid fa-cake-candles" type="date" :value="old('birth_date', $profile?->birth_date?->format('Y-m-d'))" data-progress-required data-progress-section="personal" required />
                        <x-form.input name="phone" label="No WA Aktif" icon="fa-solid fa-phone" type="tel" inputmode="numeric" :value="old('phone', $profile?->phone)" placeholder="08xxxxxxxxxx" data-progress-required data-progress-section="personal" required />
                    </div>
                </section>

                <section class="border-t border-slate-100 px-5 py-7 sm:px-6 sm:py-8">
                    <div class="mb-5 flex items-center gap-3 border-b border-slate-100 pb-4">
                        <span class="flex size-10 items-center justify-center rounded-xl bg-sky-50 text-sky-800"><i class="fa-solid fa-map-location-dot"></i></span>
                        <div><h2 class="text-base font-bold text-slate-900">Alamat</h2><p class="text-sm leading-5 text-slate-500">Alamat domisili lengkap saat ini.</p></div>
                    </div>
                    <div class="grid gap-4 grid-cols-1 sm:grid-cols-2">
                        <x-form.select name="province" label="Provinsi" icon="fa-solid fa-map" class="select2" data-region-select="province" :data-initial="old('province', $profile?->province)" data-placeholder="Pilih provinsi" data-progress-required data-progress-section="address" required>
                            <option value="">Memuat provinsi...</option>
                        </x-form.select>
                        <x-form.select name="city" label="Kota / Kabupaten" icon="fa-solid fa-city" class="select2" data-region-select="city" :data-initial="old('city', $profile?->city)" data-placeholder="Pilih kabupaten/kota" data-progress-required data-progress-section="address" disabled required>
                            <option value="">Pilih kabupaten/kota</option>
                        </x-form.select>
                        <x-form.select name="district" label="Kecamatan" icon="fa-solid fa-map-location-dot" class="select2" data-region-select="district" :data-initial="old('district', $profile?->district)" data-placeholder="Pilih kecamatan" data-progress-required data-progress-section="address" disabled required>
                            <option value="">Pilih kecamatan</option>
                        </x-form.select>
                        <x-form.select name="village" label="Kelurahan / Desa" icon="fa-solid fa-house-chimney" class="select2" data-region-select="village" :data-initial="old('village', $profile?->village)" data-placeholder="Pilih kelurahan/desa" data-progress-required data-progress-section="address" disabled required>
                            <option value="">Pilih kelurahan/desa</option>
                        </x-form.select>
                        <x-form.input name="postal_code" label="Kode Pos" icon="fa-solid fa-envelopes-bulk" inputmode="numeric" :value="old('postal_code', $profile?->postal_code)" placeholder="40135" data-progress-required data-progress-section="address" required />
                        <div class="sm:col-span-2">
                            <x-form.textarea name="address" label="Alamat Rumah" icon="fa-solid fa-location-crosshairs" :value="old('address', $profile?->address)" placeholder="Jalan, RT/RW, No. Rumah, detail alamat" data-progress-required data-progress-section="address" required rows="3" />
                        </div>
                    </div>
                </section>

                <section class="border-t border-slate-100 px-5 py-7 sm:px-6 sm:py-8">
                    <div class="mb-5 flex items-center gap-3 border-b border-slate-100 pb-4">
                        <span class="flex size-10 items-center justify-center rounded-xl bg-rose-50 text-rose-700"><i class="fa-solid fa-heart-pulse"></i></span>
                        <div><h2 class="text-base font-bold text-slate-900">Data Fisik & Kesehatan</h2><p class="text-sm leading-5 text-slate-500">Tinggi, berat dan riwayat kesehatan.</p></div>
                    </div>
                    <div class="grid gap-4 grid-cols-2">
                        <x-form.input name="height_cm" label="Tinggi Badan (cm)" icon="fa-solid fa-ruler-vertical" type="number" inputmode="numeric" min="100" max="250" :value="old('height_cm', $profile?->height_cm)" placeholder="170" data-progress-required data-progress-section="physical" required />
                        <x-form.input name="weight_kg" label="Berat Badan (kg)" icon="fa-solid fa-weight-scale" type="number" inputmode="numeric" min="20" max="200" :value="old('weight_kg', $profile?->weight_kg)" placeholder="60" data-progress-required data-progress-section="physical" required />
                    </div>
                    <div class="mt-4 grid gap-4 grid-cols-1">
                        <x-form.textarea name="medical_history" label="Apakah Ada Riwayat Kesehatan/Penyakit" icon="fa-solid fa-notes-medical" :value="old('medical_history', $profile?->medical_history)" placeholder="Jika ada silahkan isi dan jika tidak ada cukup tuliskan (-)" data-progress-required data-progress-section="physical" required rows="3" />
                        <x-form.select name="mcu_status" label="Status Medical Check-Up (MCU) Mandiri" icon="fa-solid fa-file-medical" data-progress-required data-progress-section="physical" required>
                            <option value="">Pilih status MCU</option>
                            <option value="belum" @selected(old('mcu_status', $profile?->mcu_status) === 'belum')>Belum</option>
                            <option value="proses" @selected(old('mcu_status', $profile?->mcu_status) === 'proses')>Proses</option>
                            <option value="sudah" @selected(old('mcu_status', $profile?->mcu_status) === 'sudah')>Sudah</option>
                        </x-form.select>
                    </div>
                </section>

                <section class="border-t border-slate-100 px-5 py-7 sm:px-6 sm:py-8">
                    <div class="mb-5 flex items-center gap-3 border-b border-slate-100 pb-4">
                        <span class="flex size-10 items-center justify-center rounded-xl bg-indigo-50 text-indigo-700"><i class="fa-solid fa-graduation-cap"></i></span>
                        <div><h2 class="text-base font-bold text-slate-900">Pilihan Kampus</h2><p class="text-sm leading-5 text-slate-500">Rencana melanjutkan kuliah.</p></div>
                    </div>
                    <div class="grid gap-4 grid-cols-1 sm:grid-cols-2">
                        <x-form.input name="university_choice_1" label="Pilihan 1 (Kampus)" icon="fa-solid fa-building-columns" :value="old('university_choice_1', $profile?->university_choice_1)" placeholder="Contoh: UI - Kedokteran" data-progress-required data-progress-section="campus_choice" required />
                        <x-form.input name="university_choice_2" label="Pilihan 2 (Kampus)" icon="fa-solid fa-building-columns" :value="old('university_choice_2', $profile?->university_choice_2)" placeholder="Contoh: ITB - Teknik" data-progress-required data-progress-section="campus_choice" required />
                    </div>
                </section>

                <section class="border-t border-slate-100 px-5 py-7 sm:px-6 sm:py-8">
                    <div class="mb-5 flex items-center gap-3 border-b border-slate-100 pb-4">
                        <span class="flex size-10 items-center justify-center rounded-xl bg-amber-50 text-amber-700"><i class="fa-solid fa-bullseye"></i></span>
                        <div><h2 class="text-base font-bold text-slate-900">Persiapan & Karir</h2><p class="text-sm leading-5 text-slate-500">Refleksi persiapan dan kekhawatiran karir.</p></div>
                    </div>
                    <div class="grid gap-4 grid-cols-1">
                        <x-form.textarea name="grade_11_preparation" label="Sudah sejauh mana persiapanmu di kelas 11 ini?" icon="fa-solid fa-book-open-reader" :value="old('grade_11_preparation', $profile?->grade_11_preparation)" placeholder="Ceritakan persiapan belajar, bimbel, usaha yang sudah dilakukan" data-progress-required data-progress-section="career_preparation" required rows="4" />
                        <x-form.textarea name="career_concern" label="Apa yang paling kamu khawatirkan dalam mencapai karir tersebut?" icon="fa-solid fa-triangle-exclamation" :value="old('career_concern', $profile?->career_concern)" placeholder="Tuliskan kekhawatiranmu" data-progress-required data-progress-section="career_preparation" required rows="4" />
                    </div>
                </section>

                <section class="border-t border-slate-100 px-5 py-7 sm:px-6 sm:py-8">
                    <div class="mb-5 flex items-center gap-3 border-b border-slate-100 pb-4">
                        <span class="flex size-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-700"><i class="fa-solid fa-trophy"></i></span>
                        <div><h2 class="text-base font-bold text-slate-900">Aktivitas & Evaluasi Diri</h2><p class="text-sm leading-5 text-slate-500">Prestasi, organisasi dan pengembangan diri.</p></div>
                    </div>
                    <div class="grid gap-4 grid-cols-1">
                        <x-form.textarea name="school_achievements" label="Apakah kamu memiliki prestasi selama sekolah di SMA Plus Astha Hannas?" icon="fa-solid fa-trophy" :value="old('school_achievements', $profile?->school_achievements)" placeholder="Tuliskan prestasi atau (-) jika tidak ada" data-progress-required data-progress-section="school_activity" required rows="3" />
                        <x-form.textarea name="organization_participation" label="Apakah kamu mengikuti organisasi di SMA Plus Astha Hannas?" icon="fa-solid fa-people-group" :value="old('organization_participation', $profile?->organization_participation)" placeholder="Tuliskan organisasi atau (-) jika tidak ada" data-progress-required data-progress-section="school_activity" required rows="3" />
                        <x-form.textarea name="self_improvement_notes" label="Hal yang Perlu Ditingkatkan (Evaluasi Diri)" icon="fa-solid fa-chart-line" :value="old('self_improvement_notes', $profile?->self_improvement_notes)" placeholder="Tuliskan hal yang ingin kamu tingkatkan" data-progress-required data-progress-section="school_activity" required rows="3" />
                    </div>

                    <div class="mt-8 rounded-2xl border border-dashed border-slate-200 bg-slate-50/70 p-4 sm:p-5">
                        <div class="mb-4 flex items-center gap-3">
                            <span class="flex size-9 items-center justify-center rounded-xl bg-amber-50 text-amber-700"><i class="fa-solid fa-file-arrow-up"></i></span>
                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <h3 class="text-sm font-bold text-slate-900">Sertifikat Prestasi</h3>
                                    <span class="rounded-full bg-slate-200 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-slate-600">Opsional</span>
                                </div>
                                <p class="text-xs leading-4 text-slate-500">Upload hanya jika kamu memiliki sertifikat. PDF/JPG/PNG max 5MB.</p>
                            </div>
                        </div>

                        @if (! $isAdmin)
                        <div class="space-y-3">
                            <x-form.input name="document_type" label="Nama/Jenis Sertifikat" icon="fa-solid fa-award" placeholder="Contoh: Juara 1 OSN Kabupaten 2024" maxlength="100" form="certificate-upload-form" />
                            <div>
                                <label class="mb-1.5 block text-sm font-semibold text-slate-700">File Sertifikat</label>
                                <input type="file" name="documents[]" form="certificate-upload-form" accept="application/pdf,image/jpeg,image/png" class="block w-full rounded-xl border border-dashed border-slate-300 bg-white p-3 text-sm text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-blue-900 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-blue-800" multiple>
                                <p class="mt-1.5 text-xs text-slate-400">Bisa pilih lebih dari satu file. PDF, JPG atau PNG, maksimal 5 MB per file.</p>
                            </div>
                            <x-button type="submit" form="certificate-upload-form" class="h-11 w-full"><i class="fa-solid fa-upload"></i> Upload Sertifikat</x-button>
                        </div>
                        @endif

                        <div class="mt-5 space-y-2 border-t border-slate-200 pt-4">
                            <p class="text-xs font-bold uppercase tracking-wide text-slate-400">Daftar Sertifikat</p>
                            @forelse ($certificates as $document)
                                <div class="flex items-center justify-between gap-3 rounded-xl border border-slate-200 bg-white p-3">
                                    <div class="min-w-0 flex-1">
                                        <p class="truncate text-sm font-bold leading-4 text-slate-800">{{ $document->document_type }}</p>
                                        <a href="{{ $isAdmin ? route('bk.students.biodata.documents.download', [$student, $document]) : route('siswa.biodata.documents.download', $document) }}" class="mt-0.5 block truncate text-xs font-medium text-blue-800 hover:text-blue-900">{{ $document->original_name }}</a>
                                        <p class="text-xs text-slate-400">{{ number_format($document->file_size/1024, 0) }} KB · {{ $document->created_at->format('d M Y') }}</p>
                                    </div>
                                    <button type="submit" form="certificate-delete-{{ $document->id }}" class="inline-flex size-10 shrink-0 items-center justify-center rounded-xl border border-rose-100 bg-rose-50 text-rose-600 transition hover:bg-rose-100" aria-label="Hapus sertifikat {{ $document->document_type }}"><i class="fa-solid fa-trash text-sm"></i></button>
                                </div>
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

        @if (! $isAdmin)
            <form id="photo-upload-form" action="{{ route('siswa.biodata.photo.store') }}" method="POST" enctype="multipart/form-data" class="hidden">
                @csrf
            </form>

            <form id="certificate-upload-form" action="{{ route('siswa.biodata.documents.store') }}" method="POST" enctype="multipart/form-data" class="hidden">
                @csrf
            </form>
        @endif

        @foreach ($certificates as $document)
            <form id="certificate-delete-{{ $document->id }}" action="{{ $isAdmin ? route('bk.students.biodata.documents.destroy', [$student, $document]) : route('siswa.biodata.documents.destroy', $document) }}" method="POST" class="hidden">
                @csrf
                @method('DELETE')
            </form>
        @endforeach
    </div>
@endcomponent
