@component('layouts.app', ['title' => 'Riwayat Pelanggaran '.$student->name])
    <x-page-header title="Riwayat Pelanggaran" description="{{ $student->name }} · {{ $student->nis }} · {{ $student->schoolClass?->name ?? 'Tanpa kelas' }}">
        <x-slot:actions><x-button variant="secondary" :href="route('bk.violations.index')"><i class="fa-solid fa-arrow-left"></i> Daftar pelanggaran</x-button></x-slot:actions>
    </x-page-header>
    <x-card title="{{ $violations->total() }} pelanggaran tercatat" description="Semua kejadian siswa ini, termasuk dari kelas sebelumnya; terbaru lebih dulu.">
        @if ($violations->isEmpty())
            <x-empty-state icon="fa-solid fa-clipboard-check" title="Belum ada pelanggaran" description="Belum ada catatan untuk siswa ini." />
        @else
            <div class="space-y-3">
                @foreach ($violations as $violation)
                    <article class="rounded-2xl border border-slate-200 p-5">
                        <div class="flex flex-wrap items-start justify-between gap-3"><div><p class="text-xs font-semibold text-slate-500">{{ $violation->occurred_at->translatedFormat('d F Y') }} · {{ $violation->class_at_incident }}</p><h3 class="mt-1 font-bold text-slate-900">{{ $violation->pasal_perdupsis }}</h3></div><x-button variant="secondary" :href="route('bk.violations.edit', $violation)"><i class="fa-solid fa-pen"></i> Ubah data</x-button></div>
                        <p class="mt-3 whitespace-pre-line rounded-xl bg-slate-50 p-3 text-sm text-slate-700"><strong>Sanksi:</strong> {{ $violation->sanction }}</p>
                        <div class="mt-3 flex flex-wrap items-center justify-between gap-2 text-xs text-slate-500"><span>Dicatat oleh {{ $violation->creator?->name ?? 'Akun dihapus' }}</span>@if ($violation->warning_letter_path)<a class="font-bold text-blue-800 hover:underline" href="{{ route('bk.violations.warning-letter', $violation) }}"><i class="fa-solid fa-file-pdf"></i> Unduh surat peringatan</a>@endif</div>
                    </article>
                @endforeach
            </div>
            <div class="mt-5">{{ $violations->links() }}</div>
        @endif
    </x-card>
@endcomponent
