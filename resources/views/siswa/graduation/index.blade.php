@component('layouts.app', ['title' => 'Pengumuman Kelulusan'])
    <x-page-header title="Pengumuman Kelulusan" description="Informasi resmi hasil kelulusan angkatanmu." />
    <div class="mx-auto max-w-3xl space-y-5">
        @if (! $visible)
            <div class="rounded-2xl border border-blue-100 bg-white p-8 text-center shadow-sm" role="status">
                <div class="mx-auto mb-4 flex size-14 items-center justify-center rounded-2xl bg-blue-50 text-2xl text-blue-700"><i class="fa-regular fa-clock" aria-hidden="true"></i></div>
                <h2 class="text-xl font-bold text-slate-900">Menunggu pengumuman</h2>
                <p class="mt-2 text-sm text-slate-600">Hasil belum dipublikasikan. Silakan kembali lagi nanti.</p>
                @if ($announcement?->is_active && $announcement->published_at->isFuture())
                    <p class="mt-4 text-sm font-semibold text-blue-700">Dibuka {{ $announcement->published_at->locale('id')->translatedFormat('j F Y, H.i') }} WIB</p>
                    <p class="mt-1 text-sm tabular-nums text-slate-500" id="graduation-countdown" data-target="{{ $announcement->published_at->toIso8601String() }}"></p>
                    <script>
                        const countdown = document.getElementById('graduation-countdown');
                        function updateGraduationCountdown() {
                            const seconds = Math.max(0, Math.floor((new Date(countdown.dataset.target) - Date.now()) / 1000));
                            if (seconds === 0) { location.reload(); return; }
                            countdown.textContent = `${Math.floor(seconds / 86400)} hari ${Math.floor(seconds % 86400 / 3600)} jam ${Math.floor(seconds % 3600 / 60)} menit ${seconds % 60} detik`;
                        }
                        updateGraduationCountdown();
                        setInterval(updateGraduationCountdown, 1000);
                    </script>
                @endif
            </div>
        @else
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-7 text-center">
                <div class="mx-auto mb-3 flex size-14 items-center justify-center rounded-full bg-emerald-100 text-2xl text-emerald-700"><i class="fa-solid fa-graduation-cap" aria-hidden="true"></i></div>
                <span class="rounded-full bg-emerald-200 px-4 py-1 text-xs font-bold tracking-widest text-emerald-900">LULUS</span>
                <h2 class="mt-3 text-xl font-bold text-emerald-950">Selamat, {{ $student->name }}!</h2>
                <p class="mt-1 text-sm text-emerald-800">Kamu dinyatakan lulus dari {{ $student->cohort?->name }}.</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h3 class="mb-4 font-bold text-slate-900">Data kelulusan</h3>
                <div class="grid gap-4 text-sm sm:grid-cols-2">
                    @foreach (['NIS' => $student->nis, 'NISN' => $student->nisn, 'Kelas' => $student->schoolClass?->name, 'Tempat, tanggal lahir' => trim(($student->profile?->birth_place ?? '').', '.($student->profile?->birth_date?->locale('id')->translatedFormat('j F Y') ?? ''), ', '), 'Orang tua' => $row['parent'], 'Total Nilai' => $row['total'] === null ? null : number_format((float) $row['total'], 2, ',', '.'), 'Nilai Rata-rata' => $row['average'] === null ? null : number_format((float) $row['average'], 2, ',', '.'), 'Peringkat Angkatan' => $row['rank']] as $label => $value)
                        <div><dt class="text-xs font-semibold text-slate-500">{{ $label }}</dt><dd class="mt-1 font-semibold text-slate-900">{{ $value ?: '—' }}</dd></div>
                    @endforeach
                </div>
                <div class="mt-6 border-t border-slate-100 pt-5">
                    @if ($certificateReady)
                        <a href="{{ route('siswa.graduation.certificate') }}" class="inline-flex items-center gap-2 rounded-xl bg-blue-800 px-5 py-2.5 text-sm font-bold text-white"><i class="fa-solid fa-file-pdf" aria-hidden="true"></i> Unduh Surat Keterangan Lulus</a>
                    @else
                        <p class="rounded-xl bg-amber-50 p-4 text-sm font-medium text-amber-900">Surat belum tersedia. Lengkapi biodata dan nilai semester 1–5, atau hubungi BK jika data pengesahan sekolah belum tersedia.</p>
                    @endif
                </div>
            </div>
        @endif
    </div>
@endcomponent
