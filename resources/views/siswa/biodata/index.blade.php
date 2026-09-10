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
                        <x-form.input name="nickname" label="Nama Panggilan" icon="fa-solid fa-signature" :value="old('nickname', $profile?->nickname)" />
                        <x-form.select name="gender" label="Jenis Kelamin" icon="fa-solid fa-venus-mars">
                            <option value="">Pilih jenis kelamin</option>
                            <option value="male" @selected(old('gender', $profile?->gender) === 'male')>Laki-laki</option>
                            <option value="female" @selected(old('gender', $profile?->gender) === 'female')>Perempuan</option>
                        </x-form.select>
                        <x-form.input name="birth_place" label="Tempat Lahir" icon="fa-solid fa-location-dot" :value="old('birth_place', $profile?->birth_place)" />
                        <x-form.input name="birth_date" label="Tanggal Lahir" icon="fa-solid fa-cake-candles" type="date" :value="old('birth_date', $profile?->birth_date?->format('Y-m-d'))" />
                        <x-form.input name="phone" label="Nomor HP" icon="fa-solid fa-phone" :value="old('phone', $profile?->phone)" />
                        <x-form.input name="email" label="Email" icon="fa-solid fa-envelope" type="email" :value="old('email', $profile?->email ?? $student->user?->email)" />
                    </div>
                </section>

                <section>
                    <div class="mb-5 flex items-center gap-3 border-b border-slate-100 pb-4">
                        <span class="flex size-10 items-center justify-center rounded-xl bg-sky-50 text-sky-800"><i class="fa-solid fa-map-location-dot"></i></span>
                        <div><h2 class="font-bold text-slate-900">Alamat</h2><p class="text-sm text-slate-500">Alamat domisili saat ini.</p></div>
                    </div>
                    <div class="grid gap-4 md:grid-cols-2">
                        <x-form.select name="province" label="Provinsi" class="select2" data-region-select="province" :data-initial="old('province', $profile?->province)" data-placeholder="Pilih provinsi">
                            <option value="">Memuat provinsi...</option>
                        </x-form.select>
                        <x-form.select name="city" label="Kota / Kabupaten" class="select2" data-region-select="city" :data-initial="old('city', $profile?->city)" data-placeholder="Pilih kabupaten/kota" disabled>
                            <option value="">Pilih kabupaten/kota</option>
                        </x-form.select>
                        <x-form.select name="district" label="Kecamatan" class="select2" data-region-select="district" :data-initial="old('district', $profile?->district)" data-placeholder="Pilih kecamatan" disabled>
                            <option value="">Pilih kecamatan</option>
                        </x-form.select>
                        <x-form.select name="village" label="Kelurahan / Desa" class="select2" data-region-select="village" :data-initial="old('village', $profile?->village)" data-placeholder="Pilih kelurahan/desa" disabled>
                            <option value="">Pilih kelurahan/desa</option>
                        </x-form.select>
                        <x-form.input name="postal_code" label="Kode Pos" :value="old('postal_code', $profile?->postal_code)" />
                        <x-form.textarea name="address" label="Alamat Lengkap" class="md:col-span-2" :value="old('address', $profile?->address)" />
                    </div>
                </section>

                @foreach (['father' => 'Data Ayah', 'mother' => 'Data Ibu', 'guardian' => 'Data Wali'] as $type => $label)
                    @php($parent = $parents->get($type))
                    <section>
                        <div class="mb-5 flex items-center gap-3 border-b border-slate-100 pb-4">
                            <span class="flex size-10 items-center justify-center rounded-xl bg-amber-50 text-amber-700"><i class="fa-solid fa-people-roof"></i></span>
                            <div><h2 class="font-bold text-slate-900">{{ $label }}</h2><p class="text-sm text-slate-500">Informasi kontak dan pekerjaan.</p></div>
                        </div>
                        <div class="grid gap-4 md:grid-cols-2">
                            <x-form.input name="{{ $type }}[name]" label="Nama" icon="fa-solid fa-user" :value="old($type.'.name', $parent?->name)" />
                            <x-form.input name="{{ $type }}[phone]" label="Nomor HP" icon="fa-solid fa-phone" :value="old($type.'.phone', $parent?->phone)" />
                            @if ($type !== 'guardian')
                                <x-form.input name="{{ $type }}[occupation]" label="Pekerjaan" :value="old($type.'.occupation', $parent?->occupation)" />
                                <x-form.input name="{{ $type }}[education]" label="Pendidikan Terakhir" :value="old($type.'.education', $parent?->education)" />
                                <x-form.select name="{{ $type }}[income_range]" label="Rentang Penghasilan">
                                    <option value="">Pilih rentang penghasilan</option>
                                    @foreach (['< Rp1 juta', 'Rp1 juta - Rp3 juta', 'Rp3 juta - Rp5 juta', 'Rp5 juta - Rp10 juta', '> Rp10 juta', 'Tidak berpenghasilan'] as $incomeRange)
                                        <option value="{{ $incomeRange }}" @selected(old($type.'.income_range', $parent?->income_range) === $incomeRange)>{{ $incomeRange }}</option>
                                    @endforeach
                                </x-form.select>
                            @else
                                <x-form.input name="{{ $type }}[relation]" label="Hubungan dengan Siswa" :value="old($type.'.relation', $parent?->relation)" />
                            @endif
                        </div>
                    </section>
                @endforeach

                <section>
                    <div class="mb-5 flex items-center gap-3 border-b border-slate-100 pb-4">
                        <span class="flex size-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-700"><i class="fa-solid fa-school"></i></span>
                        <div><h2 class="font-bold text-slate-900">Data Pendidikan</h2><p class="text-sm text-slate-500">Riwayat pendidikan sebelumnya.</p></div>
                    </div>
                    <div class="grid gap-4 md:grid-cols-2">
                        <x-form.input name="previous_school" label="Asal Sekolah" :value="old('previous_school', $profile?->previous_school)" />
                        <x-form.input name="graduation_year" label="Tahun Lulus" type="number" min="2000" max="2105" :value="old('graduation_year', $profile?->graduation_year)" />
                        <x-form.textarea name="previous_school_address" label="Alamat Asal Sekolah" class="md:col-span-2" :value="old('previous_school_address', $profile?->previous_school_address)" />
                        <x-form.textarea name="academic_notes" label="Catatan Akademik" class="md:col-span-2" :value="old('academic_notes', $profile?->academic_notes)" />
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
                    @foreach (['personal' => 'Data pribadi', 'address' => 'Alamat', 'parents' => 'Data orang tua', 'education' => 'Pendidikan', 'documents' => 'Dokumen'] as $key => $label)
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

            <x-card title="Dokumen" description="KIP, kartu keluarga, atau dokumen pendukung lainnya.">
                <form action="{{ route('siswa.biodata.documents.store') }}" method="POST" enctype="multipart/form-data" class="space-y-3">
                    @csrf
                    <x-form.select name="document_type" label="Jenis Dokumen">
                        <option value="">Pilih jenis dokumen</option>
                        <option value="kip">KIP</option>
                        <option value="kartu_keluarga">Kartu Keluarga</option>
                        <option value="dokumen_lainnya">Dokumen Lainnya</option>
                    </x-form.select>
                    <input type="file" name="document" accept="application/pdf,image/jpeg,image/png" class="block w-full rounded-xl border border-slate-200 bg-slate-50 p-2 text-xs text-slate-600">
                    <x-button type="submit" class="w-full"><i class="fa-solid fa-file-arrow-up"></i> Upload Dokumen</x-button>
                </form>
                <div class="mt-5 space-y-2 border-t border-slate-100 pt-4">
                    @forelse ($student->documents as $document)
                        <div class="flex items-center justify-between gap-2 text-xs"><a href="{{ route('siswa.biodata.documents.download', $document) }}" class="truncate font-semibold text-blue-800 hover:text-blue-900">{{ $document->original_name }}</a><form action="{{ route('siswa.biodata.documents.destroy', $document) }}" method="POST">@csrf @method('DELETE')<button class="text-rose-600" aria-label="Hapus dokumen"><i class="fa-solid fa-trash"></i></button></form></div>
                    @empty
                        <p class="text-xs font-medium text-slate-400">Belum ada dokumen.</p>
                    @endforelse
                </div>
            </x-card>
            @endif
        </aside>
    </div>
@endcomponent
