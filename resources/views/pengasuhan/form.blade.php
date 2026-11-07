@component('layouts.app', ['title' => $user ? 'Ubah Akun Pengasuhan' : 'Tambah Akun Pengasuhan'])
    <x-page-header title="{{ $user ? 'Ubah Akun Pengasuhan' : 'Tambah Akun Pengasuhan' }}" description="Atur akses menu pelanggaran dan/atau absensi.">
        <x-slot:actions>
            <a href="{{ route('admin.pengasuhan-accounts.index') }}" class="x-button variant-secondary"><i class="fa-solid fa-arrow-left"></i> Kembali</a>
        </x-slot:actions>
    </x-page-header>

    <x-card>
        <form action="{{ $user ? route('admin.pengasuhan-accounts.update', $user) : route('admin.pengasuhan-accounts.store') }}" method="POST" class="space-y-6">
            @csrf
            @if ($user)
                @method('PUT')
            @endif

            <div class="grid gap-4 md:grid-cols-2">
                <x-form.input name="name" label="Nama" icon="fa-solid fa-user" type="text" required :value="old('name', $user?->name ?? '')" />
                <x-form.input name="email" label="Email" icon="fa-solid fa-envelope" type="email" required :value="old('email', $user?->email ?? '')" />
                <x-form.input name="password" label="Password" icon="fa-solid fa-key" type="password" {{ $user ? '' : 'required' }} minlength="8" :value="old('password')" />
                <p class="mt-1.5 text-xs text-slate-500 col-span-2">{{ $user ? 'Kosongkan jika tidak ingin mengubah password.' : 'Minimal 8 karakter.' }}</p>
            </div>

            <div class="pt-2">
                <label class="block text-sm font-semibold text-slate-700 mb-2">Akses Menu</label>
                <div class="space-y-2">
                    @foreach ($permissions as $perm)
                        <div class="flex items-center gap-3 p-2 rounded-xl border border-slate-200 bg-slate-50 hover:bg-blue-50/50 transition">
                            <input type="checkbox" name="permissions[]" value="{{ $perm->name }}" {{ in_array($perm->name, $selected ?? []) || (! isset($selected) && true) ? 'checked' : '' }} class="rounded border-slate-300 text-blue-600 focus:ring-blue-500 h-4 w-4">
                            <label class="text-sm text-slate-700 cursor-pointer">{{ str_replace('pengasuhan.', '', $perm->name) }}</label>
                        </div>
                    @endforeach
                </div>
                <p class="mt-1.5 text-xs text-slate-500">Kosongkan semua = tidak bisa akses keduanya. Centang keduanya untuk akses penuh.</p>
            </div>

            <div class="flex justify-end gap-3">
                <x-button variant="secondary" :href="route('admin.pengasuhan-accounts.index')">Batal</x-button>
                <x-button type="submit">{{ $user ? 'Simpan Perubahan' : 'Buat Akun' }}</x-button>
            </div>
        </form>
    </x-card>
@endcomponent
