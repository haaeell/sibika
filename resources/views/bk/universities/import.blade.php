@component('layouts.app', ['title' => 'Import Kampus'])
    <x-page-header title="Import Kampus" description="Tambah atau perbarui banyak kampus sekaligus.">
        <x-slot:actions><x-button variant="secondary" :href="route('bk.universities.template')"><i class="fa-solid fa-file-arrow-down"></i> Template</x-button><x-button variant="secondary" :href="route('bk.universities.index')">Kembali</x-button></x-slot:actions>
    </x-page-header>
    <x-card>
        <form action="{{ route('bk.universities.import.prepare') }}" method="POST" enctype="multipart/form-data" class="space-y-4" data-import-form>
            @csrf
            <input name="file" type="file" accept=".xlsx,.xls" required class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm">
            <p class="text-sm text-slate-500">Kolom: Nama Kampus, Singkatan, Jenis (`negeri`/`swasta`/`kedinasan`/`lainnya`), Status (`aktif`/`nonaktif`). Nama sama akan diperbarui.</p>
            <x-button type="submit" data-import-submit><i class="fa-solid fa-upload"></i> Upload & Proses</x-button>
        </form>
        <div class="mt-6 hidden rounded-xl border border-blue-200 bg-blue-50 p-4 text-sm font-bold text-blue-900" data-import-progress></div>
        <div class="mt-4 hidden rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-700" data-import-failures></div>
    </x-card>
    @push('scripts')<script>document.addEventListener('DOMContentLoaded',function(){const f=document.querySelector('[data-import-form]'),p=document.querySelector('[data-import-progress]'),e=document.querySelector('[data-import-failures]'),b=f.querySelector('[data-import-submit]'),u=@json(route('bk.universities.import.batch'));const r=async(u,d)=>{const x=await fetch(u,{method:'POST',headers:{Accept:'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content},body:d}),j=await x.json();if(!x.ok)throw Error(j.message);return j};f.addEventListener('submit',async x=>{x.preventDefault();b.disabled=true;p.classList.remove('hidden');e.classList.add('hidden');try{let q=await r(f.action,new FormData(f)),o=0,z;do{z=await r(u,new URLSearchParams({token:q.token,offset:o}));o=z.next_offset;p.textContent=`Memproses ${z.processed}/${z.total}: ${z.success} baru, ${z.updated} diperbarui.`}while(!z.done);if(z.failures.length){e.classList.remove('hidden');e.textContent=z.failures.map(v=>`Baris ${v.row} (${v.name||'-'}): ${v.message}`).join('\n')}p.textContent=`Import selesai: ${z.success} baru, ${z.updated} diperbarui.`}catch(x){p.textContent=x.message}finally{b.disabled=false}})})</script>@endpush
@endcomponent
