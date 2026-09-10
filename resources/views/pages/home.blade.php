@component('layouts.app', ['title' => 'Setup Awal'])
    <x-page-header
        title="SIBIKA"
        description="Fondasi template E-Learning BK SMA Plus Astha Hannas."
    >
        <x-slot:actions>
            <x-button href="{{ route('dashboard') }}">
                <i class="fa-solid fa-house"></i>
                Buka Dashboard
            </x-button>
        </x-slot:actions>
    </x-page-header>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
        <x-card>
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-sm font-medium text-slate-500">Stack</p>
                    <p class="mt-2 text-2xl font-bold text-slate-900">Laravel + Blade</p>
                </div>
                <div class="flex size-11 items-center justify-center rounded-xl bg-blue-50 text-blue-900">
                    <i class="fa-brands fa-laravel"></i>
                </div>
            </div>
        </x-card>

        <x-card>
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-sm font-medium text-slate-500">UI</p>
                    <p class="mt-2 text-2xl font-bold text-slate-900">Tailwind CSS</p>
                </div>
                <div class="flex size-11 items-center justify-center rounded-xl bg-sky-50 text-sky-600">
                    <i class="fa-solid fa-palette"></i>
                </div>
            </div>
        </x-card>

        <x-card>
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-sm font-medium text-slate-500">Interaksi</p>
                    <p class="mt-2 text-2xl font-bold text-slate-900">jQuery Ready</p>
                </div>
                <div class="flex size-11 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">
                    <i class="fa-solid fa-code"></i>
                </div>
            </div>
        </x-card>
    </div>

    <x-card title="Status Setup" description="Template dasar dan package utama sudah disiapkan sebagai landasan pengembangan.">
        <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
            <x-badge variant="success">Role route siap</x-badge>
            <x-badge variant="success">Blade component siap</x-badge>
            <x-badge variant="success">Vendor UI siap</x-badge>
            <x-badge variant="warning">Modul fitur belum dibuat</x-badge>
        </div>
    </x-card>
@endcomponent
