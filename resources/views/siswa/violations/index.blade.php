@component('layouts.app', ['title' => 'Riwayat Pelanggaran'])
    <x-page-header title="Riwayat Pelanggaran" description="Catatan pelanggaran dan sanksi milikmu." />
    <x-card title="Riwayat" description="{{ $violations->total() }} catatan ditemukan.">
        @if ($violations->isEmpty())
            <x-empty-state icon="fa-solid fa-clipboard-check" title="Belum ada catatan" description="Tidak ada pelanggaran tercatat." />
        @else
            <div class="space-y-3">@foreach ($violations as $violation)
                <article class="rounded-2xl border border-slate-200 p-5">
                    <div class="flex flex-wrap items-center justify-between gap-2"><h3 class="font-bold text-slate-900">{{ $violation->pasal_perdupsis }}</h3><span class="text-xs font-semibold text-slate-500">{{ $violation->occurred_at->translatedFormat('d F Y') }}</span></div>
                    <p class="mt-1 text-xs text-slate-500">Kelas saat kejadian: {{ $violation->class_at_incident }}</p>
                    <div class="mt-3 rounded-xl bg-slate-50 p-3 text-sm text-slate-700"><strong>Sanksi:</strong> <span class="whitespace-pre-line">{{ $violation->sanction }}</span></div>
                    @if ($violation->warning_letter_path)
                        <a href="{{ route('siswa.violations.warning-letter', $violation) }}" class="mt-3 inline-flex items-center gap-2 rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-sm font-semibold text-rose-800 hover:bg-rose-100"><i class="fa-solid fa-file-pdf"></i> Unduh Surat Peringatan</a>
                    @endif
                </article>
            @endforeach</div>
            <div class="mt-4">{{ $violations->links() }}</div>
        @endif
    </x-card>
@endcomponent
