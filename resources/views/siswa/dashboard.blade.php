@component('layouts.app', ['title' => 'Dashboard Siswa'])
    <x-page-header title="Dashboard Siswa" description="Pantau biodata, nilai, dan informasi akademik kamu." />

    <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_22rem]">
        <div class="space-y-6">
            <x-card>
                <div class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-center gap-4">
                        @if ($student->profile?->photo_path && \Illuminate\Support\Facades\Storage::disk('local')->exists($student->profile->photo_path))
                            <img src="{{ route('siswa.biodata.photo.show', ['v' => md5($student->profile->photo_path)]) }}" alt="Foto profil {{ $student->name }}" class="size-16 rounded-2xl object-cover shadow-sm">
                        @else
                            <span class="flex size-16 items-center justify-center rounded-2xl bg-blue-50 text-blue-800 shadow-sm"><i class="fa-solid fa-user-graduate text-2xl"></i></span>
                        @endif
                        <div>
                            <p class="text-xs font-bold uppercase tracking-widest text-blue-800">NIS {{ $student->nis }}</p>
                            <h2 class="mt-1 text-2xl font-extrabold tracking-tight text-slate-900">{{ $student->name }}</h2>
                            <p class="mt-1 flex flex-wrap items-center gap-2 text-sm font-semibold text-slate-500"><span><i class="fa-solid fa-school mr-1 text-slate-400"></i>{{ $student->schoolClass?->name ?? 'Belum ditempatkan' }}</span><span class="text-slate-300">|</span><span><i class="fa-solid fa-layer-group mr-1 text-slate-400"></i>{{ $student->cohort?->name ?? 'Belum ditentukan' }}</span></p>
                        </div>
                    </div>
                    <span class="inline-flex w-fit items-center gap-2 rounded-full bg-emerald-50 px-3 py-1.5 text-xs font-bold text-emerald-700"><i class="fa-solid fa-circle-check"></i>{{ $student->status === 'active' ? 'Aktif' : ucfirst($student->status) }}</span>
                </div>
            </x-card>

            <x-card>
                <div class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <div class="flex items-center gap-3">
                            <span class="flex size-10 items-center justify-center rounded-xl bg-blue-50 text-blue-800"><i class="fa-solid fa-list-check"></i></span>
                            <div><h3 class="font-bold text-slate-900">Kelengkapan Biodata</h3><p class="text-sm text-slate-500">{{ $progress['completed'] }} dari {{ $progress['total'] }} data wajib terisi.</p></div>
                        </div>
                        <div class="mt-5 flex items-end gap-3"><span class="text-5xl font-extrabold tracking-tight text-blue-900">{{ $progress['percentage'] }}%</span><span class="mb-1.5 text-sm font-semibold text-slate-500">lengkap</span></div>
                    </div>
                    <x-button :href="route('siswa.biodata.index')"><i class="fa-solid fa-pen"></i> Lengkapi Biodata</x-button>
                </div>
                <div class="mt-6 h-3 overflow-hidden rounded-full bg-slate-100"><div class="h-full rounded-full bg-blue-800" style="width: {{ $progress['percentage'] }}%"></div></div>
            </x-card>

            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                @foreach ([
                    ['label' => 'Status MCU', 'value' => ['belum' => 'Belum', 'proses' => 'Proses', 'sudah' => 'Sudah'][$student->profile?->mcu_status] ?? 'Belum diisi', 'icon' => 'fa-file-medical', 'class' => 'bg-sky-50 text-sky-700'],
                    ['label' => 'Pilihan Kampus', 'value' => collect([$student->profile?->universityChoice1?->short_name ?? $student->profile?->universityChoice1?->name, $student->profile?->universityChoice2?->short_name ?? $student->profile?->universityChoice2?->name, $student->profile?->universityChoice3?->short_name ?? $student->profile?->universityChoice3?->name])->filter()->join(' / ') ?: 'Belum diisi', 'icon' => 'fa-building-columns', 'class' => 'bg-indigo-50 text-indigo-700'],
                    ['label' => 'Organisasi', 'value' => $student->profile?->organization_status === 'ya' ? 'Mengikuti' : 'Tidak mengikuti', 'icon' => 'fa-people-group', 'class' => 'bg-emerald-50 text-emerald-700'],
                ] as $item)
                    <x-card class="p-4">
                        <div class="flex items-start gap-3">
                            <span class="flex size-10 shrink-0 items-center justify-center rounded-xl {{ $item['class'] }}"><i class="fa-solid {{ $item['icon'] }}"></i></span>
                            <div class="min-w-0"><p class="text-xs font-bold uppercase tracking-wide text-slate-400">{{ $item['label'] }}</p><p class="mt-1 truncate text-sm font-extrabold text-slate-900" title="{{ $item['value'] }}">{{ $item['value'] }}</p></div>
                        </div>
                    </x-card>
                @endforeach
            </div>
        </div>

        <aside class="space-y-6">
            <x-card>
                <div class="mb-4 flex items-center gap-3"><span class="flex size-10 items-center justify-center rounded-xl bg-amber-50 text-amber-700"><i class="fa-solid fa-bolt"></i></span><div><h3 class="font-bold text-slate-900">Akses Cepat</h3><p class="text-sm text-slate-500">Menu yang sering dipakai.</p></div></div>
                <div class="space-y-2">
                    <a href="{{ route('siswa.biodata.index') }}" class="flex items-center gap-3 rounded-xl border border-slate-100 bg-slate-50 px-3 py-3 text-sm font-bold text-slate-700 transition hover:bg-blue-50 hover:text-blue-900"><i class="fa-solid fa-id-card w-5 text-center text-blue-800"></i> Biodata Saya <i class="fa-solid fa-chevron-right ml-auto text-xs text-slate-400"></i></a>
                    <a href="{{ route('siswa.scores.index') }}" class="flex items-center gap-3 rounded-xl border border-slate-100 bg-slate-50 px-3 py-3 text-sm font-bold text-slate-700 transition hover:bg-blue-50 hover:text-blue-900"><i class="fa-solid fa-chart-line w-5 text-center text-blue-800"></i> Nilai Semester <i class="fa-solid fa-chevron-right ml-auto text-xs text-slate-400"></i></a>
                    <a href="{{ route('siswa.articles.index') }}" class="flex items-center gap-3 rounded-xl border border-slate-100 bg-slate-50 px-3 py-3 text-sm font-bold text-slate-700 transition hover:bg-blue-50 hover:text-blue-900"><i class="fa-regular fa-newspaper w-5 text-center text-blue-800"></i> Artikel & Informasi <i class="fa-solid fa-chevron-right ml-auto text-xs text-slate-400"></i></a>
                </div>
            </x-card>

            <x-card>
                <div class="mb-4 flex items-center justify-between gap-3"><div class="flex items-center gap-3"><span class="flex size-10 items-center justify-center rounded-xl bg-blue-50 text-blue-800"><i class="fa-regular fa-newspaper"></i></span><div><h3 class="font-bold text-slate-900">Informasi Terbaru</h3><p class="text-sm text-slate-500">Universitas, beasiswa, dan karir.</p></div></div><a href="{{ route('siswa.articles.index') }}" class="text-xs font-extrabold text-blue-800">Lihat semua</a></div>
                <div class="space-y-3">
                    @forelse ($articles as $article)
                        <a href="{{ route('siswa.articles.show', $article) }}" class="block rounded-xl border border-slate-100 bg-slate-50 p-3 transition hover:bg-blue-50"><p class="text-xs font-bold uppercase tracking-wide text-blue-800">{{ $article->categoryLabel() }} · {{ $article->published_at?->translatedFormat('d M Y') }}</p><p class="mt-1 line-clamp-2 text-sm font-extrabold text-slate-900">{{ $article->title }}</p></a>
                    @empty
                        <p class="rounded-xl bg-slate-50 p-3 text-sm font-semibold text-slate-500">Belum ada artikel terbaru.</p>
                    @endforelse
                </div>
            </x-card>

            <x-card>
                <div class="mb-4 flex items-center gap-3"><span class="flex size-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-700"><i class="fa-solid fa-circle-check"></i></span><div><h3 class="font-bold text-slate-900">Status Bagian</h3><p class="text-sm text-slate-500">Ringkasan progress biodata.</p></div></div>
                <div class="space-y-2.5">
                    @foreach (['personal' => 'Data pribadi', 'address' => 'Alamat', 'physical' => 'Fisik & Kesehatan', 'parents' => 'Biodata Orang Tua', 'campus_choice' => 'Pilihan Kampus', 'school_activity' => 'Aktivitas', 'documents' => 'Dokumen'] as $key => $label)
                        <div class="flex items-center justify-between rounded-lg bg-slate-50 px-3 py-2 text-sm"><span class="font-semibold text-slate-600">{{ $label }}</span><i class="fa-solid {{ $progress['sections'][$key] ? 'fa-circle-check text-emerald-500' : 'fa-circle text-slate-300' }}"></i></div>
                    @endforeach
                </div>
            </x-card>
        </aside>
    </div>
@endcomponent
