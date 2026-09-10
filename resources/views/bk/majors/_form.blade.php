@csrf
<div class="grid gap-4 md:grid-cols-2">
    <x-form.input name="code" label="Kode Jurusan" icon="fa-solid fa-hashtag" placeholder="Contoh: IPA" :value="old('code', $major->code)" />
    <x-form.input name="name" label="Nama Jurusan" icon="fa-solid fa-book-open" placeholder="Contoh: Ilmu Pengetahuan Alam" :value="old('name', $major->name)" />
</div>
<label class="mt-4 flex items-start gap-3 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-700"><input type="checkbox" name="is_active" value="1" class="mt-0.5 size-5 rounded border-slate-300 text-blue-800 focus:ring-blue-700" @checked(old('is_active', $major->is_active))><span>Jurusan aktif<span class="block text-xs font-medium text-slate-500">Jurusan nonaktif tidak muncul saat membuat kelas baru.</span></span></label>
<div class="mt-6 flex flex-wrap items-center gap-2"><x-button type="submit"><i class="fa-solid fa-save"></i> Simpan</x-button><x-button variant="secondary" :href="route('bk.majors.index')">Batal</x-button></div>
