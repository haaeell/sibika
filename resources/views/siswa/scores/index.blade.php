@component('layouts.app', ['title' => 'Nilai Semester'])
    <x-page-header title="Nilai Semester" description="Isi nilai semester 1 sampai 5 sesuai mapel umum dan jurusan." />

    <x-card>
        <div class="flex flex-wrap gap-2" data-semester-tabs>
            @foreach ($semesters as $semester => $data)
                <button type="button" class="js-semester-tab rounded-xl px-4 py-2 text-sm font-extrabold transition {{ $semester === $activeSemester ? 'bg-blue-900 text-white' : 'bg-slate-100 text-slate-600 hover:bg-blue-50 hover:text-blue-900' }}" data-semester-target="student-score-semester-{{ $semester }}">
                    Semester {{ $semester }}
                </button>
            @endforeach
        </div>
    </x-card>

    @foreach ($semesters as $semester => $data)
        @php
            $settings = $data['settings'];
            $scores = $data['scores'];
            $needsMajor = $semester >= 3 && ! $student->schoolClass?->major_id;
            $locked = $data['locked'];
            $approval = $data['approval'];
            $pendingRequest = $data['pending_request'];
            $rejectedRequest = $data['rejected_request'];
        @endphp

        <div id="student-score-semester-{{ $semester }}" class="{{ $semester === $activeSemester ? '' : 'hidden' }}" data-semester-panel>
            @if ($needsMajor)
                <x-card>
                    <div class="flex items-start gap-3 text-amber-700">
                        <i class="fa-solid fa-triangle-exclamation mt-1"></i>
                        <div><p class="font-bold">Jurusan belum ditentukan.</p><p class="mt-1 text-sm text-amber-600">Semester 3 sampai 5 membutuhkan jurusan siswa agar mapel jurusan bisa tampil.</p></div>
                    </div>
                </x-card>
            @endif

            <x-card title="Semester {{ $semester }}" description="{{ $locked ? 'Nilai terkunci. Ajukan permintaan edit ke BK untuk mengubahnya.' : 'Isi nilai lalu simpan. Nilai tersimpan langsung masuk rekap rata-rata.' }}">
                @if ($settings->isEmpty())
                    <x-empty-state icon="fa-solid fa-chart-line" title="Setting nilai belum tersedia" description="Hubungi BK untuk mengatur mapel semester ini." />
                @elseif ($locked)
                    <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-semibold text-amber-800">
                        <i class="fa-solid fa-lock mr-1.5"></i>
                        Nilai semester ini sudah tersimpan dan terkunci. Ajukan permintaan edit ke BK untuk mengubahnya.
                    </div>

                    @if ($pendingRequest)
                        <div class="mt-3 rounded-xl border border-blue-200 bg-blue-50 px-4 py-3 text-sm font-semibold text-blue-900">
                            <i class="fa-solid fa-hourglass-half mr-1.5"></i>
                            Pengajuanmu sedang menunggu persetujuan BK ({{ $pendingRequest->created_at->format('d M Y H:i') }}).
                        </div>
                    @else
                        @if ($rejectedRequest && ($pendingRequest === null))
                            <div class="mt-3 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-700">
                                <i class="fa-solid fa-circle-xmark mr-1.5"></i>
                                Pengajuan terakhir ditolak: {{ $rejectedRequest->review_note }}
                            </div>
                        @endif
                        <form action="{{ route('siswa.scores.request-edit') }}" method="POST" class="mt-3 space-y-3">
                            @csrf
                            <input type="hidden" name="semester" value="{{ $semester }}">
                            <div>
                                <label for="reason-{{ $semester }}" class="mb-1.5 block text-sm font-semibold text-slate-700">Alasan pengajuan edit <span class="text-rose-500">*</span></label>
                                <textarea id="reason-{{ $semester }}" name="reason" rows="3" required minlength="10" placeholder="Contoh: nilai Matematika salah input, seharusnya 85 bukan 58" class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm outline-none transition placeholder:text-slate-400 focus:border-blue-700 focus:ring-4 focus:ring-blue-700/10"></textarea>
                                <x-form.error name="reason" />
                            </div>
                            <x-button type="submit"><i class="fa-solid fa-paper-plane"></i> Ajukan Permintaan Edit</x-button>
                        </form>
                    @endif

                    <div class="mt-4 grid gap-4 md:grid-cols-2">
                        @foreach ($settings as $setting)
                            @php $score = $scores->get($setting->subject_id); @endphp
                            <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                                <p class="text-xs font-bold uppercase tracking-wide text-slate-400">{{ $setting->subject->name }}</p>
                                <p class="mt-1 text-xl font-extrabold text-slate-900">{{ filled($score?->score) ? number_format((float) $score->score, 2) : '-' }}</p>
                            </div>
                        @endforeach
                    </div>
                @else
                    @if ($approval)
                        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">
                            <i class="fa-solid fa-lock-open mr-1.5"></i>
                            Edit diizinkan BK — formulir terkunci lagi otomatis setelah kamu menyimpan.
                        </div>
                    @endif
                    <form action="{{ route('siswa.scores.save') }}" method="POST" class="mt-4 space-y-4">
                        @csrf
                        <input type="hidden" name="semester" value="{{ $semester }}">
                        <div class="grid gap-4 md:grid-cols-2">
                            @foreach ($settings as $setting)
                                @php $score = $scores->get($setting->subject_id); @endphp
                                <x-form.input name="scores[{{ $setting->subject_id }}]" label="{{ $setting->subject->name }}{{ $setting->is_required ? ' *' : '' }}" icon="fa-solid fa-chart-simple" type="number" min="0" max="100" step="0.01" placeholder="0 - 100" :value="old('scores.'.$setting->subject_id, $score?->score)" />
                            @endforeach
                        </div>
                        <x-form.error name="scores" />
                        <x-button type="submit"><i class="fa-solid fa-save"></i> Simpan Nilai</x-button>
                    </form>
                @endif
            </x-card>

            <x-scores.semester-line-chart :id="'student-score-chart-'.$semester" :settings="$settings" :scores="$scores" title="Grafik Semester {{ $semester }}" />
        </div>
    @endforeach

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                window.$(document).on('click', '.js-semester-tab', function () {
                    const button = window.$(this);
                    const target = button.data('semester-target');

                    button.closest('[data-semester-tabs]').find('.js-semester-tab').removeClass('bg-blue-900 text-white').addClass('bg-slate-100 text-slate-600 hover:bg-blue-50 hover:text-blue-900');
                    button.removeClass('bg-slate-100 text-slate-600 hover:bg-blue-50 hover:text-blue-900').addClass('bg-blue-900 text-white');
                    window.$('[data-semester-panel]').addClass('hidden');
                    window.$('#' + target).removeClass('hidden');
                });
            });
        </script>
    @endpush
@endcomponent
