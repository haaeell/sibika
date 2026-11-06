@php
    $state = match ($result?->category) {
        'eligible' => ['variant' => 'success', 'panel' => 'border-emerald-200 bg-emerald-50/70 text-emerald-700', 'icon' => 'fa-circle-check', 'label' => 'ELIGIBLE', 'title' => 'Selamat, kamu eligible.', 'message' => 'Kamu termasuk dalam daftar siswa eligible angkatan ini.'],
        'cadangan' => ['variant' => 'info', 'panel' => 'border-sky-200 bg-sky-50/70 text-sky-700', 'icon' => 'fa-clock', 'label' => 'CADANGAN', 'title' => 'Kamu masuk daftar cadangan.', 'message' => 'Hasilmu tercatat dalam daftar cadangan angkatan ini.'],
        'tidak eligible' => ['variant' => 'danger', 'panel' => 'border-rose-200 bg-rose-50/70 text-rose-700', 'icon' => 'fa-circle-xmark', 'label' => 'TIDAK ELIGIBLE', 'title' => 'Hasil seleksimu tersedia.', 'message' => 'Kamu belum termasuk dalam daftar eligible atau cadangan.'],
        'belum lengkap' => ['variant' => 'warning', 'panel' => 'border-amber-200 bg-amber-50/70 text-amber-700', 'icon' => 'fa-circle-exclamation', 'label' => 'BELUM LENGKAP', 'title' => 'Nilai belum lengkap.', 'message' => 'Nilai semester 1–5 belum lengkap, sehingga belum masuk peringkat.'],
        default => ['variant' => 'neutral', 'panel' => 'border-slate-200 bg-slate-50 text-slate-500', 'icon' => 'fa-clock', 'label' => 'BELUM TERSEDIA', 'title' => 'Menunggu pengumuman', 'message' => 'Hasil seleksi akan tampil setelah jadwal pengumuman tiba.'],
    };
@endphp
@component('layouts.app', ['title' => 'Pengumuman Eligible'])
    <div class="mx-auto max-w-4xl space-y-4">
        <div class="hidden sm:block"><x-page-header title="Pengumuman Eligible" /></div>

        <x-card class="overflow-hidden !p-0">
            <div class="flex items-center justify-between gap-3 border-b border-slate-100 px-4 py-3 sm:px-5">
                <div class="flex min-w-0 items-center gap-3">
                    <span class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-800"><i class="fa-solid fa-star" aria-hidden="true"></i></span>
                    <div class="min-w-0">
                        <p class="text-[10px] font-bold uppercase tracking-wide text-slate-400">Hasil seleksi</p>
                        <p class="truncate text-sm font-bold text-slate-700">{{ $student->cohort?->name ?? 'Angkatan siswa' }}</p>
                    </div>
                </div>
                <x-badge :variant="$state['variant']" class="shrink-0">{{ $state['label'] }}</x-badge>
            </div>

            <div class="px-4 py-3 sm:px-5 sm:py-5">
                <div class="flex items-start gap-3 rounded-xl border px-3 py-2.5 sm:py-3 {{ $state['panel'] }}">
                    <i class="fa-solid {{ $state['icon'] }} mt-0.5 text-lg" aria-hidden="true"></i>
                    <div class="min-w-0">
                        <h2 class="text-base font-extrabold tracking-tight text-slate-900 sm:text-lg">{{ $state['title'] }}</h2>
                        <p class="mt-0.5 text-xs leading-5 text-slate-600 sm:text-sm">{{ $state['message'] }}</p>
                    </div>
                </div>

                @if ($result)
                    <div class="mt-3 grid gap-3 sm:grid-cols-[minmax(0,1fr)_minmax(0,1.2fr)]">
                        <div class="rounded-xl border border-slate-100 bg-slate-50 px-3 py-2 sm:py-2.5">
                            <p class="text-[10px] font-bold uppercase tracking-wide text-slate-400">Siswa</p>
                            <p class="mt-0.5 break-words text-sm font-extrabold text-slate-900">{{ $student->name }}</p>
                            <p class="text-xs text-slate-500">{{ $student->nis }} <span aria-hidden="true">·</span> {{ $student->schoolClass?->name ?? 'Tanpa kelas' }}</p>
                        </div>
                        <dl class="grid grid-cols-3 gap-2">
                            <div class="min-w-0 rounded-xl border border-slate-100 bg-slate-50 px-2.5 py-2 sm:py-2.5"><dt class="text-[10px] leading-tight text-slate-500">Total Nilai</dt><dd class="mt-1 text-sm font-extrabold tabular-nums text-slate-900">{{ $result->total_score === null ? '—' : number_format((float) $result->total_score, 2, ',', '.') }}</dd></div>
                            <div class="min-w-0 rounded-xl border border-slate-100 bg-slate-50 px-2.5 py-2 sm:py-2.5"><dt class="text-[10px] leading-tight text-slate-500">Nilai Rata-rata</dt><dd class="mt-1 text-sm font-extrabold tabular-nums text-slate-900">{{ $result->average === null ? '—' : number_format((float) $result->average, 2, ',', '.') }}</dd></div>
                            <div class="min-w-0 rounded-xl border border-slate-100 bg-slate-50 px-2.5 py-2 sm:py-2.5"><dt class="text-[10px] leading-tight text-slate-500">Peringkat</dt><dd class="mt-1 text-sm font-extrabold tabular-nums text-slate-900">{{ $result->rank ? '#'.$result->rank : '—' }}</dd></div>
                        </dl>
                    </div>

                    @if (in_array($result->category, ['eligible', 'cadangan'], true))
                        <section class="mt-3 border-t border-slate-100 pt-3 sm:mt-4 sm:pt-4" aria-labelledby="eligible-action-title">
                            @if ($result->response === 'diterima')
                                <h3 id="eligible-action-title" class="text-sm font-bold text-slate-900">Pilihan tersimpan: Diterima</h3>
                                <p class="mt-1 text-xs text-slate-500">Langkah berikutnya, masuk ke grup WA {{ ucfirst($result->category) }} untuk informasi selanjutnya.</p>
                                @php($waUrl = $result->category === 'eligible' ? $announcement->eligible_wa_url : $announcement->reserve_wa_url)
                                @if ($waUrl)
                                    <x-button :href="$waUrl" target="_blank" rel="noopener noreferrer" class="mt-3 w-full sm:w-auto"><i class="fa-brands fa-whatsapp" aria-hidden="true"></i> Masuk grup WA {{ ucfirst($result->category) }}</x-button>
                                @endif
                            @elseif ($result->response)
                                <h3 id="eligible-action-title" class="text-sm font-bold text-slate-900">Pilihan tersimpan: {{ ucfirst($result->response) }}</h3>
                                <p class="mt-1 text-xs text-slate-500">Pilihan ini tidak dapat diubah.</p>
                            @else
                                <h3 id="eligible-action-title" class="text-sm font-bold text-slate-900">Konfirmasi hasilmu</h3>
                                <p class="mt-1 text-xs text-slate-500">Pilihan tidak dapat diubah. Tautan grup WA muncul setelah memilih Terima.</p>
                                <form method="POST" action="{{ route('siswa.eligible.respond') }}" class="mt-3 grid grid-cols-2 gap-2">
                                    @csrf
                                    <x-button type="submit" name="response" value="diterima" class="w-full">Terima</x-button>
                                    <x-button type="submit" variant="danger" name="response" value="ditolak" class="w-full">Tolak</x-button>
                                </form>
                                @error('response') <p role="alert" class="mt-2 text-xs font-semibold text-rose-700">{{ $message }}</p> @enderror
                            @endif
                        </section>
                    @endif

                    <p class="mt-3 hidden text-right text-[11px] text-slate-400 sm:block">Diumumkan {{ $announcement->published_at->locale('id')->translatedFormat('d F Y, H.i') }} WIB</p>
                @else
                    <p class="mt-4 text-sm text-slate-500" role="status">Hasil belum dipublikasikan. Silakan kembali lagi nanti.</p>
                @endif
            </div>
        </x-card>
    </div>
@endcomponent
