@if ($sessions->isEmpty())
    <x-empty-state icon="fa-solid fa-comments" title="Tidak ada sesi" description="Belum ada sesi yang cocok dengan pencarian atau rentang tanggal." />
@else
    <ol class="space-y-4">
        @foreach ($sessions as $session)
            <li class="rounded-2xl border border-slate-200 p-5 transition hover:border-blue-200 hover:bg-slate-50/50">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div><div class="flex flex-wrap items-center gap-2"><x-badge variant="neutral">Pertemuan ke-{{ $session->meeting_number }}</x-badge><span class="text-xs font-semibold text-slate-500">{{ $session->held_at->locale('id')->translatedFormat('j M Y, H.i') }}</span><x-badge variant="info">{{ ucfirst($session->type) }}</x-badge></div><h3 data-search-content class="mt-2 text-lg font-bold text-slate-900">{{ $session->topic }}</h3><p class="mt-1 text-xs text-slate-500">Dicatat {{ $session->creator?->name ?? 'Akun dihapus' }} · terakhir diubah {{ $session->updated_at->format('d M Y, H:i') }} oleh {{ $session->editor?->name ?? 'Akun dihapus' }}</p></div>
                    @unless ($readonly ?? false)
                        <div class="flex flex-wrap gap-2"><x-button variant="secondary" :href="route('bk.counseling.edit', $session)"><i class="fa-solid fa-pen" aria-hidden="true"></i> Edit</x-button>
                            <form method="POST" action="{{ route('bk.counseling.destroy', $session) }}" class="js-delete-form" data-confirm-title="Hapus sesi konseling ini?" data-confirm-text="Catatan pertemuan ini akan dihapus permanen.">@csrf @method('DELETE')<x-button variant="danger" type="submit"><i class="fa-solid fa-trash" aria-hidden="true"></i> Hapus</x-button></form>
                        </div>
                    @endunless
                </div>
                <dl class="mt-4 space-y-3 text-sm"><div><dt class="font-bold text-slate-700">Ringkasan</dt><dd data-search-content class="mt-1 whitespace-pre-line text-slate-600">{{ $session->summary }}</dd></div>@if ($session->outcome)<div><dt class="font-bold text-slate-700">Hasil pembahasan</dt><dd data-search-content class="mt-1 whitespace-pre-line text-slate-600">{{ $session->outcome }}</dd></div>@endif</dl>
                @if ($session->follow_up)
                    <div class="mt-4 rounded-xl p-4 {{ $session->completed_at ? 'bg-emerald-50' : ($session->due_at?->isBefore(today()) ? 'bg-rose-50' : 'bg-amber-50') }}">
                        <div class="flex flex-wrap items-center justify-between gap-2"><div><span class="text-xs font-bold uppercase text-slate-700">{{ $session->completed_at ? 'Selesai' : ($session->due_at?->isBefore(today()) ? 'Lewat tenggat' : 'Perlu tindak lanjut') }}</span>@if ($session->due_at)<span class="ml-2 text-xs text-slate-600">Tenggat {{ $session->due_at->format('d M Y') }}</span>@endif</div>
                            @unless ($readonly ?? false)
                                <form method="POST" action="{{ route('bk.counseling.complete', $session) }}">@csrf @method('PATCH')<button type="submit" class="rounded-lg bg-white px-3 py-1.5 text-xs font-bold text-blue-800 shadow-sm">{{ $session->completed_at ? 'Buka kembali' : 'Tandai selesai' }}</button></form>
                            @endunless
                        </div>
                        <p data-search-content class="mt-2 whitespace-pre-line text-sm text-slate-700">{{ $session->follow_up }}</p>
                        @if ($session->completed_at)<p class="mt-1 text-xs text-slate-500">Ditutup {{ $session->completed_at->format('d M Y, H:i') }}</p>@endif
                    </div>
                @endif
            </li>
        @endforeach
    </ol>
    <div class="mt-5">{{ $sessions->links() }}</div>
@endif
