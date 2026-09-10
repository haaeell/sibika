@component('layouts.app', ['title' => 'Setting Nilai'])
    <x-page-header title="Setting Nilai" description="Atur mapel umum dan mapel jurusan per semester." />

    <x-card>
        <form action="{{ route('bk.score-subject-settings.average-subjects.update') }}" method="POST">
            @csrf
            @method('PUT')
            <div class="mb-4 flex items-start gap-3">
                <span class="flex size-11 shrink-0 items-center justify-center rounded-2xl bg-blue-50 text-blue-900"><i class="fa-solid fa-calculator"></i></span>
                <div>
                    <h2 class="font-extrabold text-slate-900">Perhitungan Rata-rata</h2>
                    <p class="mt-1 text-sm font-semibold text-slate-500">Centang mapel sekali saja. Jika Bahasa Indonesia dihitung, semua semester yang punya mapel itu ikut dihitung.</p>
                </div>
            </div>

            <div class="mb-4 flex flex-wrap gap-2" data-average-tabs>
                <button type="button" class="js-average-tab rounded-xl bg-blue-900 px-3 py-2 text-sm font-extrabold text-white" data-average-target="average-general">Umum</button>
                @foreach ($majors as $major)
                    <button type="button" class="js-average-tab rounded-xl bg-slate-100 px-3 py-2 text-sm font-extrabold text-slate-600 transition hover:bg-blue-50 hover:text-blue-900" data-average-target="average-major-{{ $major->id }}">{{ $major->name }}</button>
                @endforeach
            </div>

            <div id="average-general" data-average-panel>
                <div class="grid gap-2 md:grid-cols-2 xl:grid-cols-3">
                    @foreach ($subjects as $subject)
                        @php $setting = $averageSettings->get('general-'.$subject->id); @endphp
                        <label class="flex items-center gap-3 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm font-semibold text-slate-700">
                            <input type="checkbox" name="average_subjects[general][]" value="{{ $subject->id }}" class="size-4 rounded border-slate-300 text-blue-800 focus:ring-blue-700" @checked($setting?->include_in_average ?? true)>
                            <span>{{ $subject->name }} <span class="text-xs text-slate-400">({{ $subject->code }})</span></span>
                        </label>
                    @endforeach
                </div>
            </div>

            @foreach ($majors as $major)
                <div id="average-major-{{ $major->id }}" class="hidden" data-average-panel>
                    <div class="grid gap-2 md:grid-cols-2 xl:grid-cols-3">
                        @foreach ($subjects as $subject)
                            @php $setting = $averageSettings->get($major->id.'-'.$subject->id); @endphp
                            <label class="flex items-center gap-3 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm font-semibold text-slate-700">
                                <input type="checkbox" name="average_subjects[{{ $major->id }}][]" value="{{ $subject->id }}" class="size-4 rounded border-slate-300 text-blue-800 focus:ring-blue-700" @checked($setting?->include_in_average ?? true)>
                                <span>{{ $subject->name }} <span class="text-xs text-slate-400">({{ $subject->code }})</span></span>
                            </label>
                        @endforeach
                    </div>
                </div>
            @endforeach

            <div class="mt-5"><x-button type="submit"><i class="fa-solid fa-save"></i> Simpan Perhitungan Rata-rata</x-button></div>
        </form>
    </x-card>

    <div class="grid items-start gap-5 xl:grid-cols-2">
        @for ($semester = 1; $semester <= 5; $semester++)
            @php
                $settings = $settingsBySemester->get($semester, collect());
                $groups = $settings->groupBy(fn ($setting) => $setting->major?->name ?? 'Umum');
            @endphp

            <x-card class="self-start p-0">
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
                            @if ($semester >= 3 && $groups->count() > 1)
                                <div class="flex flex-wrap gap-2" data-score-tabs>
                                    @foreach ($groups as $group => $items)
                                        <button type="button" class="js-score-tab rounded-xl px-3 py-2 text-sm font-extrabold transition {{ $loop->first ? 'bg-blue-900 text-white' : 'bg-slate-100 text-slate-600 hover:bg-blue-50 hover:text-blue-900' }}" data-score-tab-target="score-tab-{{ $semester }}-{{ $loop->index }}">
                                            {{ $group }}
                                            <span class="ml-1 text-xs opacity-75">{{ $items->count() }}</span>
                                        </button>
                                    @endforeach
                                </div>
                            @endif

                            @foreach ($groups as $group => $items)
                                <div id="score-tab-{{ $semester }}-{{ $loop->index }}" class="{{ $semester >= 3 && $groups->count() > 1 && ! $loop->first ? 'hidden' : '' }}" data-score-tab-panel>
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
                                                    @php $avg = $averageSettings->get(($setting->major_id ?? 'general').'-'.$setting->subject_id)?->include_in_average ?? true; @endphp
                                                    <span class="hidden rounded-full px-2.5 py-1 text-xs font-bold lg:inline-flex {{ $avg ? 'bg-blue-50 text-blue-700' : 'bg-amber-50 text-amber-700' }}">{{ $avg ? 'Dihitung' : 'Tidak dihitung' }}</span>
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

                window.$(document).on('click', '.js-score-tab', function () {
                    const button = window.$(this);
                    const card = button.closest('[data-score-semester-panel]');
                    const target = button.data('score-tab-target');

                    card.find('.js-score-tab')
                        .removeClass('bg-blue-900 text-white')
                        .addClass('bg-slate-100 text-slate-600 hover:bg-blue-50 hover:text-blue-900');
                    button
                        .removeClass('bg-slate-100 text-slate-600 hover:bg-blue-50 hover:text-blue-900')
                        .addClass('bg-blue-900 text-white');

                    card.find('[data-score-tab-panel]').addClass('hidden');
                    card.find('#' + target).removeClass('hidden');
                });

                window.$(document).on('click', '.js-average-tab', function () {
                    const button = window.$(this);
                    const target = button.data('average-target');

                    button.closest('form').find('.js-average-tab').removeClass('bg-blue-900 text-white').addClass('bg-slate-100 text-slate-600 hover:bg-blue-50 hover:text-blue-900');
                    button.removeClass('bg-slate-100 text-slate-600 hover:bg-blue-50 hover:text-blue-900').addClass('bg-blue-900 text-white');
                    button.closest('form').find('[data-average-panel]').addClass('hidden');
                    button.closest('form').find('#' + target).removeClass('hidden');
                });
            });
        </script>
    @endpush
@endcomponent
