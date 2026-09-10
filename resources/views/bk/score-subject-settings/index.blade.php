@component('layouts.app', ['title' => 'Setting Nilai'])
    <x-page-header title="Setting Nilai" description="Atur mapel umum dan mapel jurusan per semester." />

    <div class="grid gap-5 xl:grid-cols-2">
        @for ($semester = 1; $semester <= 5; $semester++)
            @php
                $settings = $settingsBySemester->get($semester, collect());
                $groups = $settings->groupBy(fn ($setting) => $setting->major?->name ?? 'Umum');
            @endphp

            <x-card class="p-0">
                <div class="flex items-start justify-between gap-4 p-5">
                    <button type="button" class="js-score-semester-toggle flex min-w-0 flex-1 items-center gap-3 text-left" aria-expanded="false" aria-controls="score-semester-{{ $semester }}">
                        <span class="flex size-12 items-center justify-center rounded-2xl bg-blue-900 text-lg font-extrabold text-white">{{ $semester }}</span>
                        <span>
                            <span class="block text-lg font-extrabold text-slate-900">Semester {{ $semester }}</span>
                            <span class="block text-sm font-semibold text-slate-500">{{ $semester <= 2 ? 'Kelas X tanpa jurusan' : 'Umum + mapel jurusan' }}</span>
                        </span>
                        <i class="fa-solid fa-chevron-down ml-auto text-sm text-slate-400 transition" data-score-semester-chevron></i>
                    </button>
                    <div class="flex items-center gap-3">
                        <span class="hidden rounded-full bg-slate-100 px-3 py-1 text-xs font-extrabold text-slate-600 sm:inline-flex">{{ $settings->count() }} mapel</span>
                        <a href="{{ route('bk.score-subject-settings.create', ['semester' => $semester]) }}" class="inline-flex size-10 items-center justify-center rounded-xl bg-blue-900 text-white transition hover:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-700 focus:ring-offset-2 has-tooltip" data-tooltip="Tambah" aria-label="Tambah setting semester {{ $semester }}">
                            <i class="fa-solid fa-plus"></i>
                        </a>
                    </div>
                </div>

                <div id="score-semester-{{ $semester }}" class="hidden border-t border-slate-100 p-5 pt-0" data-score-semester-panel>
                    @if ($settings->isEmpty())
                        <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-4 py-8 text-center">
                            <p class="text-sm font-bold text-slate-500">Belum ada setting.</p>
                            <a href="{{ route('bk.score-subject-settings.create', ['semester' => $semester]) }}" class="mt-3 inline-flex text-sm font-bold text-blue-900 hover:text-blue-700">Tambah mapel semester ini</a>
                        </div>
                    @else
                        <div class="space-y-4 pt-5">
                            @foreach ($groups as $group => $items)
                                <div>
                                    <div class="mb-2 flex items-center justify-between gap-2">
                                        <span class="inline-flex rounded-full {{ $group === 'Umum' ? 'bg-sky-50 text-sky-700' : 'bg-indigo-50 text-indigo-700' }} px-2.5 py-1 text-xs font-extrabold">{{ $group }}</span>
                                        <span class="text-xs font-bold text-slate-400">{{ $items->count() }} mapel</span>
                                    </div>
                                    <div class="space-y-2">
                                        @foreach ($items->sortBy(fn ($setting) => $setting->subject?->name) as $setting)
                                            <div class="flex items-center justify-between gap-3 rounded-2xl border border-slate-200 bg-white px-4 py-3 transition hover:border-blue-100 hover:bg-blue-50/40">
                                                <div class="min-w-0">
                                                    <p class="truncate text-sm font-extrabold text-slate-900">{{ $setting->subject?->name ?? '-' }}</p>
                                                    <p class="mt-0.5 text-xs font-semibold text-slate-400">{{ $setting->subject?->code ?? '-' }}</p>
                                                </div>
                                                <div class="flex shrink-0 items-center gap-2">
                                                    <span class="hidden rounded-full px-2.5 py-1 text-xs font-bold sm:inline-flex {{ $setting->is_required ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">{{ $setting->is_required ? 'Wajib' : 'Pilihan' }}</span>
                                                    <span class="hidden rounded-full px-2.5 py-1 text-xs font-bold sm:inline-flex {{ $setting->is_active ? 'bg-blue-50 text-blue-700' : 'bg-slate-100 text-slate-600' }}">{{ $setting->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                                                    @include('bk.score-subject-settings._actions', ['setting' => $setting])
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </x-card>
        @endfor
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                window.$('.js-score-semester-toggle').on('click', function () {
                    const button = window.$(this);
                    const panel = window.$('#' + button.attr('aria-controls'));
                    const isOpen = button.attr('aria-expanded') === 'true';

                    button.attr('aria-expanded', String(!isOpen));
                    panel.toggleClass('hidden', isOpen);
                    button.find('[data-score-semester-chevron]').toggleClass('rotate-180', !isOpen);
                });
            });
        </script>
    @endpush
@endcomponent
