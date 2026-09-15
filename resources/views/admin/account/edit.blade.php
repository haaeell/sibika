@component('layouts.app', ['title' => 'Akun Superadmin'])
    <x-page-header title="Akun Superadmin" description="Ubah email dan password akun superadmin." />

    <x-card>
        <form action="{{ route('admin.account.update') }}" method="POST" class="max-w-2xl space-y-5">
            @csrf
            @method('PUT')

            <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                <i class="fa-solid fa-shield-halved mr-2"></i> Password saat ini wajib diisi untuk menyimpan perubahan akun.
            </div>

            <x-form.input name="email" label="Email Superadmin" icon="fa-solid fa-envelope" type="email" :value="old('email', auth()->user()->email)" required />

            <div class="grid gap-4 md:grid-cols-2">
                <x-form.input name="password" label="Password Baru" icon="fa-solid fa-lock" type="password" help="Kosongkan jika tidak ingin mengganti password." />
                <x-form.input name="password_confirmation" label="Konfirmasi Password Baru" icon="fa-solid fa-lock" type="password" />
            </div>

            <x-form.input name="current_password" label="Password Saat Ini" icon="fa-solid fa-key" type="password" required />

            <x-button type="submit"><i class="fa-solid fa-save"></i> Simpan Perubahan</x-button>
        </form>
    </x-card>
@endcomponent
