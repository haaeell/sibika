@csrf
<div class="grid gap-4 md:grid-cols-2">
    <input type="hidden" name="category" value="general">
    <x-form.input name="code" label="Kode Mata Pelajaran" icon="fa-solid fa-hashtag" placeholder="Contoh: MAT" :value="old('code', $subject->code)" />
    <x-form.input name="name" label="Nama Mata Pelajaran" icon="fa-solid fa-book" placeholder="Contoh: Matematika" :value="old('name', $subject->name)" />
</div>
<label class="mt-4 flex items-start gap-3 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-700"><input type="checkbox" name="is_active" value="1" class="mt-0.5 size-5 rounded border-slate-300 text-blue-800 focus:ring-blue-700" @checked(old('is_active', $subject->is_active))><span>Mata pelajaran aktif<span class="block text-xs font-medium text-slate-500">Mapel nonaktif tidak muncul saat mengatur guru.</span></span></label>
<div class="mt-6 flex flex-wrap items-center gap-2"><x-button type="submit"><i class="fa-solid fa-save"></i> Simpan</x-button><x-button variant="secondary" :href="route('bk.subjects.index')">Batal</x-button></div>
