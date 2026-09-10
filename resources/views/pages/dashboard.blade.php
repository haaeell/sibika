@component('layouts.app', ['title' => 'Dashboard'])
    <x-page-header
        title="Dashboard"
        description="Kerangka awal dashboard lintas role."
    />

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
        @foreach ([
            ['label' => 'Siswa', 'value' => '0', 'icon' => 'fa-solid fa-users', 'class' => 'bg-blue-50 text-blue-900'],
            ['label' => 'Kelas', 'value' => '0', 'icon' => 'fa-solid fa-school', 'class' => 'bg-sky-50 text-sky-600'],
            ['label' => 'Tugas Aktif', 'value' => '0', 'icon' => 'fa-solid fa-file-pen', 'class' => 'bg-amber-50 text-amber-600'],
            ['label' => 'Alumni', 'value' => '0', 'icon' => 'fa-solid fa-user-graduate', 'class' => 'bg-emerald-50 text-emerald-600'],
        ] as $stat)
            <x-card>
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-medium text-slate-500">{{ $stat['label'] }}</p>
                        <p class="mt-2 text-3xl font-bold tracking-tight text-slate-900">{{ $stat['value'] }}</p>
                    </div>
                    <div class="flex size-11 items-center justify-center rounded-xl {{ $stat['class'] }}">
                        <i class="{{ $stat['icon'] }}"></i>
                    </div>
                </div>
            </x-card>
        @endforeach
    </div>

    <x-card title="Template Siap" description="Area ini nanti menjadi ringkasan sesuai role setelah data dan modul dibuat.">
        <x-empty-state
            icon="fa-solid fa-layer-group"
            title="Belum ada modul aktif"
            description="Setup awal berhenti di template, package, route, dan komponen dasar."
        />
    </x-card>
@endcomponent
