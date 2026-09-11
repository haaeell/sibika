@php
    $isAdmin = $isAdmin ?? false;
    $biodataUpdateRoute = $biodataUpdateRoute ?? route('siswa.biodata.update');
    $biodataBackRoute = $biodataBackRoute ?? route('siswa.dashboard');
@endphp

@component('layouts.app', ['title' => $isAdmin ? 'Edit Biodata Siswa' : 'Biodata'])
    <x-page-header :title="$isAdmin ? 'Edit Biodata Siswa' : 'Biodata Saya'" :description="$isAdmin ? 'Perbarui data biodata siswa.' : 'Lengkapi data pribadi dan keluarga untuk kebutuhan BK.'">
        <x-slot:actions>
            <x-button variant="secondary" :href="$biodataBackRoute"><i class="fa-solid fa-arrow-left"></i> Kembali</x-button>
        </x-slot:actions>
    </x-page-header>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_20rem]">
        <x-card>
            <form action="{{ $biodataUpdateRoute }}" method="POST" class="space-y-8">
                @csrf
                @method('PUT')

                <section>
                    <div class="mb-5 flex items-center gap-3 border-b border-slate-100 pb-4">
                        <span class="flex size-10 items-center justify-center rounded-xl bg-blue-50 text-blue-800"><i class="fa-solid fa-user"></i></span>
                        <div><h2 class="font-bold text-slate-900">Data Pribadi</h2><p class="text-sm text-slate-500">Data utama siswa dan informasi kontak.</p></div>
                    </div>
                    <div class="grid gap-4 md:grid-cols-2">
                        <x-form.input name="nis" label="NIS" icon="fa-solid fa-id-card" :value="$student->nis" readonly class="bg-slate-50 text-slate-500" />
                        <x-form.input name="nisn" label="NISN" icon="fa-solid fa-fingerprint" :value="$student->nisn" readonly class="bg-slate-50 text-slate-500" />
                        <x-form.input name="name" label="Nama Lengkap" icon="fa-solid fa-user-graduate" :value="$student->name" readonly class="bg-slate-50 text-slate-500" />
                        <x-form.select name="gender" label="Jenis Kelamin" icon="fa-solid fa-venus-mars" required>
                            <option value="">Pilih jenis kelamin</option>
                            <option value="male" @selected(old('gender', $profile?->gender) === 'male')>Laki-laki</option>
                            <option value="female" @selected(old('gender', $profile?->gender) === 'female')>Perempuan</option>
                        </x-form.select>
                        <x-form.input name="birth_place" label="Tempat Lahir" icon="fa-solid fa-location-dot" :value="old('birth_place', $profile?->birth_place)" required />
                        <x-form.input name="birth_date" label="Tanggal Lahir" icon="fa-solid fa-cake-candles" type="date" :value="old('birth_date', $profile?->birth_date?->format('Y-m-d'))" required />
                        <x-form.input name="phone" label="No WA Aktif" icon="fa-solid fa-phone" :value="old('phone', $profile?->phone)" required />
                    </div>
                </section>

                <section>
                    <div class="mb-5 flex items-center gap-3 border-b border-slate-100 pb-4">
                        <span class="flex size-10 items-center justify-center rounded-xl bg-sky-50 text-sky-800"><i class="fa-solid fa-map-location-dot"></i></span>
                        <div><h2 class="font-bold text-slate-900">Alamat</h2><p class="text-sm text-slate-500">Alamat domisili saat ini.</p></div>
                    </div>
                    <div class="grid gap-4 md:grid-cols-2">
                        <x-form.select name="province" label="Provinsi" class="select2" data-region-select="province" :data-initial="old('province', $profile?->province)" data-placeholder="Pilih provinsi" required>
                            <option value="">Memuat provinsi...</option>
                        </x-form.select>
                        <x-form.select name="city" label="Kota / Kabupaten" class="select2" data-region-select="city" :data-initial="old('city', $profile?->city)" data-placeholder="Pilih kabupaten/kota" disabled required>
                            <option value="">Pilih kabupaten/kota</option>
                        </x-form.select>
                        <x-form.select name="district" label="Kecamatan" class="select2" data-region-select="district" :data-initial="old('district', $profile?->district)" data-placeholder="Pilih kecamatan" disabled required>
                            <option value="">Pilih kecamatan</option>
                        </x-form.select>
                        <x-form.select name="village" label="Kelurahan / Desa" class="select2" data-region-select="village" :data-initial="old('village', $profile?->village)" data-placeholder="Pilih kelurahan/desa" disabled required>
                            <option value="">Pilih kelurahan/desa</option>
                        </x-form.select>
                        <x-form.input name="postal_code" label="Kode Pos" :value="old('postal_code', $profile?->postal_code)" required />
                        <x-form.textarea name="address" label="Alamat Rumah" class="md:col-span-2" :value="old('address', $profile?->address)" required />
                    </div>
                </section>

                <section>
                    <div class="mb-5 flex items-center gap-3 border-b border-slate-100 pb-4">
                        <span class="flex size-10 items-center justify-center rounded-xl bg-rose-50 text-rose-700"><i class="fa-solid fa-heart-pulse"></i></span>
                        <div><h2 class="font-bold text-slate-900">Data Fisik & Kesehatan</h2><p class="text-sm text-slate-500">Tinggi, berat badan dan riwayat kesehatan.</p></div>
                    </div>
                    <div class="grid gap-4 md:grid-cols-2">
                        <x-form.input name="height_cm" label="Tinggi Badan (cm)" type="number" min="100" max="250" :value="old('height_cm', $profile?->height_cm)" placeholder="Contoh: 170" />
                        <x-form.input name="weight_kg" label="Berat Badan (kg)" type="number" min="20" max="200" :value="old('weight_kg', $profile?->weight_kg)" placeholder="Contoh: 60" />
                        <x-form.textarea name="medical_history" label="Apakah Ada Riwayat Kesehatan/Penyakit" class="md:col-span-2" :value="old('medical_history', $profile?->medical_history)" placeholder="Jika ada silahkan isi dan jika tidak ada cukup tuliskan (-)" />
                        <x-form.select name="mcu_status" label="Status Medical Check-Up (MCU) Mandiri">
                            <option value="">Pilih status MCU</option>
                            <option value="belum" @selected(old('mcu_status', $profile?->mcu_status) === 'belum')>Belum</option>
                            <option value="proses" @selected(old('mcu_status', $profile?->mcu_status) === 'proses')>Proses</option>
                            <option value="sudah" @selected(old('mcu_status', $profile?->mcu_status) === 'sudah')>Sudah</option>
                        </x-form.select>
                    </div>
                </section>

                <section>
                    <div class="mb-5 flex items-center gap-3 border-b border-slate-100 pb-4">
                        <span class="flex size-10 items-center justify-center rounded-xl bg-indigo-50 text-indigo-700"><i class="fa-solid fa-graduation-cap"></i></span>
                        <div><h2 class="font-bold text-slate-900">Pilihan Kampus</h2><p class="text-sm text-slate-500">Rencana melanjutkan pendidikan.</p></div>
                    </div>
                    <div class="grid gap-4 md:grid-cols-2">
                        <x-form.input name="university_choice_1" label="Pilihan 1 (Kampus)" :value="old('university_choice_1', $profile?->university_choice_1)" placeholder="Contoh: UI - Kedokteran" />
                        <x-form.input name="university_choice_2" label="Pilihan 2 (Kampus)" :value="old('university_choice_2', $profile?->university_choice_2)" placeholder="Contoh: ITB - Teknik" />
                    </div>
                </section>

                <section>
                    <div class="mb-5 flex items-center gap-3 border-b border-slate-100 pb-4">
                        <span class="flex size-10 items-center justify-center rounded-xl bg-amber-50 text-amber-700"><i class="fa-solid fa-bullseye"></i></span>
                        <div><h2 class="font-bold text-slate-900">Persiapan & Karir</h2><p class="text-sm text-slate-500">Refleksi persiapan dan kekhawatiran karir.</p></div>
                    </div>
                    <div class="grid gap-4">
                        <x-form.textarea name="grade_11_preparation" label="Sudah sejauh mana persiapanmu di kelas 11 ini?" :value="old('grade_11_preparation', $profile?->grade_11_preparation)" placeholder="Ceritakan persiapan belajar, bimbel, dll" />
                        <x-form.textarea name="career_concern" label="Apa yang paling kamu khawatirkan dalam mencapai karir tersebut?" :value="old('career_concern', $profile?->career_concern)" placeholder="Tuliskan kekhawatiranmu" />
                    </div>
                </section>

                <section>
                    <div class="mb-5 flex items-center gap-3 border-b border-slate-100 pb-4">
                        <span class="flex size-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-700"><i class="fa-solid fa-trophy"></i></span>
                        <div><h2 class="font-bold text-slate-900">Aktivitas & Evaluasi Diri</h2><p class="text-sm text-slate-500">Prestasi, organisasi dan hal yang perlu ditingkatkan.</p></div>
                    </div>
                    <div class="grid gap-4">
                        <x-form.textarea name="school_achievements" label="Apakah kamu memiliki prestasi selama sekolah di SMA Plus Astha Hannas?" :value="old('school_achievements', $profile?->school_achievements)" placeholder="Tuliskan prestasi atau (-) jika tidak ada" />
                        <x-form.textarea name="organization_participation" label="Apakah kamu mengikuti organisasi di SMA Plus Astha Hannas?" :value="old('organization_participation', $profile?->organization_participation)" placeholder="Tuliskan organisasi atau (-) jika tidak ada" />
                        <x-form.textarea name="self_improvement_notes" label="Hal yang Perlu Ditingkatkan (Evaluasi Diri)" :value="old('self_improvement_notes', $profile?->self_improvement_notes)" placeholder="Tuliskan hal yang ingin kamu tingkatkan" />
                    </div>
                </section>

                <div class="flex justify-end border-t border-slate-100 pt-6">
                    <x-button type="submit"><i class="fa-solid fa-save"></i> Simpan Biodata</x-button>
                </div>
            </form>
        </x-card>

        <aside class="space-y-6">
            <x-card title="Progress Biodata" description="Lengkapi setiap bagian agar data siap digunakan BK.">
                <div class="flex items-end justify-between gap-4">
                    <span class="text-4xl font-extrabold text-blue-900">{{ $progress['percentage'] }}%</span>
                    <span class="text-xs font-bold uppercase tracking-wide text-slate-400">Lengkap</span>
                </div>
                <div class="mt-4 h-3 overflow-hidden rounded-full bg-slate-100"><div class="h-full rounded-full bg-blue-800 transition-all" style="width: {{ $progress['percentage'] }}%"></div></div>
                <div class="mt-5 space-y-3">
                    @foreach (['personal' => 'Data pribadi', 'address' => 'Alamat', 'physical' => 'Fisik & Kesehatan', 'campus_choice' => 'Pilihan Kampus', 'career_preparation' => 'Persiapan Karir', 'school_activity' => 'Aktivitas & Evaluasi', 'documents' => 'Dokumen'] as $key => $label)
                        <div class="flex items-center justify-between gap-3 text-sm"><span class="font-semibold text-slate-600">{{ $label }}</span><i class="fa-solid {{ $progress['sections'][$key] ? 'fa-circle-check text-emerald-500' : 'fa-circle text-slate-300' }}"></i></div>
                    @endforeach
                </div>
            </x-card>

            @if (! $isAdmin)
            <x-card title="Foto Profil" description="Gunakan foto JPG, PNG, atau WEBP maksimal 2 MB.">
                @if ($profile?->photo_path)
                    <img src="{{ route('siswa.biodata.photo.show') }}" alt="Foto profil" class="mx-auto mb-4 size-28 rounded-2xl border border-slate-200 object-cover">
                @endif
                <form action="{{ route('siswa.biodata.photo.store') }}" method="POST" enctype="multipart/form-data" class="space-y-3">
                    @csrf
                    <input type="file" name="photo" accept="image/jpeg,image/png,image/webp" class="block w-full rounded-xl border border-slate-200 bg-slate-50 p-2 text-xs text-slate-600">
                    <x-button type="submit" class="w-full"><i class="fa-solid fa-upload"></i> Upload Foto</x-button>
                </form>
            </x-card>

            <x-card title="Sertifikat Prestasi" description="Upload sertifikat/piagam prestasi. Ketik nama sertifikat (max 100 karakter). PDF/JPG/PNG max 5MB.">
                <form action="{{ route('siswa.biodata.documents.store') }}" method="POST" enctype="multipart/form-data" class="space-y-3">
                    @csrf
                    <x-form.input name="document_type" label="Nama/Jenis Sertifikat" placeholder="Contoh: Juara 1 OSN Kabupaten 2024" required maxlength="100" />
                    <input type="file" name="document" accept="application/pdf,image/jpeg,image/png" class="block w-full rounded-xl border border-slate-200 bg-slate-50 p-2 text-xs text-slate-600" required>
                    <x-button type="submit" class="w-full"><i class="fa-solid fa-file-arrow-up"></i> Upload Sertifikat</x-button>
                </form>
                <div class="mt-5 space-y-2 border-t border-slate-100 pt-4">
                    @forelse ($student->documents as $document)
                        <div class="flex items-center justify-between gap-2 text-xs">
                            <div class="min-w-0 flex-1">
                                <p class="truncate font-semibold text-slate-700">{{ $document->document_type }}</p>
                                <a href="{{ route('siswa.biodata.documents.download', $document) }}" class="truncate font-medium text-blue-800 hover:text-blue-900">{{ $document->original_name }}</a>
                            </div>
                            <form action="{{ route('siswa.biodata.documents.destroy', $document) }}" method="POST">@csrf @method('DELETE')<button class="text-rose-600" aria-label="Hapus sertifikat"><i class="fa-solid fa-trash"></i></button></form>
                        </div>
                    @empty
                        <p class="text-xs font-medium text-slate-400">Belum ada sertifikat.</p>
                    @endforelse
                </div>
            </x-card>
            @endif
        </aside>
    </div>
@endcomponent
