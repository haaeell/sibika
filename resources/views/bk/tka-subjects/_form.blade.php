@csrf
<div class="grid gap-4 grid-cols-1">
    <x-form.input name="name" label="Nama Mapel TKA" icon="fa-solid fa-book" placeholder="Contoh: Matematika" :value="old('name', $tkaSubject->name)" />
</div>
<label class="mt-4 flex items-start gap-3 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-700"><input type="checkbox" name="is_active" value="1" class="mt-0.5 size-5 rounded border-slate-300 text-blue-800 focus:ring-blue-700" @checked(old('is_active', $tkaSubject->is_active))><span>Mapel TKA aktif<span class="block text-xs font-medium text-slate-500">Mapel nonaktif tidak muncul pada pilihan biodata siswa.</span></span></label>
<div class="mt-6 flex flex-wrap items-center gap-2"><x-button type="submit"><i class="fa-solid fa-save"></i> Simpan</x-button><x-button variant="secondary" :href="route('bk.tka-subjects.index')">Batal</x-button></div>
