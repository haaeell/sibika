@component('layouts.app', ['title' => $user ? 'Ubah Kepala Sekolah' : 'Tambah Kepala Sekolah'])
    <x-page-header :title="$user ? 'Ubah Kepala Sekolah' : 'Tambah Kepala Sekolah'" description="Akses baca-saja ke menu BK dan admin, kecuali konseling."></x-page-header>
    <x-card><form method="POST" action="{{ $user ? route('admin.principals.update', $user) : route('admin.principals.store') }}" class="space-y-4">@csrf @if ($user) @method('PUT') @endif
        <x-form.input name="name" label="Nama" required :value="old('name', $user?->name)" />
        <x-form.input name="email" label="Email" type="email" required :value="old('email', $user?->email)" />
        <x-form.input name="password" label="Password" type="password" :required="! $user" minlength="8" />
        <p class="text-xs text-slate-500">{{ $user ? 'Kosongkan jika tidak diubah.' : 'Minimal 8 karakter.' }}</p>
        <x-button type="submit">Simpan</x-button><x-button variant="secondary" :href="route('admin.principals.index')">Batal</x-button>
    </form></x-card>
@endcomponent
