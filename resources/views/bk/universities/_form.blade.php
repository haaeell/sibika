@csrf
<div class="grid gap-4 md:grid-cols-2">
    <x-form.input name="name" label="Nama Kampus" icon="fa-solid fa-building-columns" placeholder="Contoh: Universitas Indonesia" :value="old('name', $university->name)" />
    <x-form.input name="short_name" label="Singkatan" icon="fa-solid fa-hashtag" placeholder="Contoh: UI" :value="old('short_name', $university->short_name)" />
    <x-form.select name="type" label="Jenis Kampus" icon="fa-solid fa-layer-group">
        @foreach (['negeri' => 'Perguruan Tinggi Negeri', 'swasta' => 'Perguruan Tinggi Swasta', 'kedinasan' => 'Perguruan Tinggi Kedinasan', 'lainnya' => 'Lainnya'] as $value => $label)
            <option value="{{ $value }}" @selected(old('type', $university->type) === $value)>{{ $label }}</option>
        @endforeach
    </x-form.select>
</div>
<label class="mt-4 flex items-start gap-3 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-700"><input type="checkbox" name="is_active" value="1" class="mt-0.5 size-5 rounded border-slate-300 text-blue-800 focus:ring-blue-700" @checked(old('is_active', $university->is_active))><span>Kampus aktif<span class="block text-xs font-medium text-slate-500">Kampus nonaktif tidak muncul pada pilihan biodata siswa.</span></span></label>
<div class="mt-6 flex flex-wrap items-center gap-2"><x-button type="submit"><i class="fa-solid fa-save"></i> Simpan</x-button><x-button variant="secondary" :href="route('bk.universities.index')">Batal</x-button></div>
