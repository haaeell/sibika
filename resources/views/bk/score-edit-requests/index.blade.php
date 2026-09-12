@component('layouts.app', ['title' => 'Persetujuan Edit Nilai'])
    <x-page-header title="Persetujuan Edit Nilai" description="Tinjau pengajuan edit nilai dari siswa. Izin yang disetujui berlaku sekali simpan." />

    <x-card title="Menunggu Persetujuan" description="{{ $pending->count() }} pengajuan perlu ditinjau.">
        @if ($pending->isEmpty())
            <x-empty-state icon="fa-solid fa-inbox" title="Tidak ada pengajuan" description="Semua pengajuan edit nilai sudah diproses." />
        @else
            <div class="space-y-3">
                @foreach ($pending as $item)
                    <div class="rounded-xl border border-slate-200 p-4">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div>
                                <p class="font-extrabold text-slate-900">{{ $item->student->name }} <span class="ml-1 text-xs font-semibold text-slate-400">NIS {{ $item->student->nis }}</span></p>
                                <p class="mt-0.5 text-sm font-semibold text-slate-500">{{ $item->student->schoolClass?->name ?? 'Belum ditempatkan' }} &bull; Semester {{ $item->semester_number }} &bull; {{ $item->created_at->format('d M Y H:i') }}</p>
                                <p class="mt-2 rounded-lg bg-slate-50 px-3 py-2 text-sm leading-6 text-slate-600"><span class="font-bold text-slate-700">Alasan:</span> {{ $item->reason }}</p>
                            </div>
                            <div class="flex flex-wrap items-center gap-2">
                                <form action="{{ route('bk.score-edit-requests.approve', $item) }}" method="POST" class="js-confirm-form" data-confirm-title="Setujui edit nilai?" data-confirm-text="Siswa dapat mengubah nilai semester {{ $item->semester_number }} dan menyimpannya satu kali." data-confirm-button="Ya, setujui" data-confirm-icon="question">
                                    @csrf
                                    <x-button type="submit"><i class="fa-solid fa-check"></i> Setujui</x-button>
                                </form>
                                <button type="button" class="inline-flex items-center justify-center gap-2 rounded-xl border border-rose-200 bg-white px-4 py-2.5 text-sm font-semibold text-rose-600 transition hover:bg-rose-50" data-reject-toggle aria-expanded="false" aria-controls="reject-form-{{ $item->id }}"><i class="fa-solid fa-xmark"></i> Tolak</button>
                            </div>
                        </div>
                        <form id="reject-form-{{ $item->id }}" action="{{ route('bk.score-edit-requests.reject', $item) }}" method="POST" class="mt-3 hidden space-y-2 rounded-xl border border-rose-100 bg-rose-50/50 p-3" data-reject-form>
                            @csrf
                            <label for="review-note-{{ $item->id }}" class="block text-sm font-semibold text-slate-700">Alasan penolakan <span class="text-rose-500">*</span></label>
                            <textarea id="review-note-{{ $item->id }}" name="review_note" rows="2" required minlength="5" placeholder="Jelaskan mengapa pengajuan ditolak agar siswa memahami" class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm outline-none focus:border-blue-700 focus:ring-4 focus:ring-blue-700/10"></textarea>
                            <x-button type="submit" variant="danger"><i class="fa-solid fa-paper-plane"></i> Kirim Penolakan</x-button>
                        </form>
                    </div>
                @endforeach
            </div>
        @endif
    </x-card>

    <x-card title="Riwayat 50 Terakhir" description="Pengajuan yang sudah disetujui atau ditolak.">
        @if ($history->isEmpty())
            <x-empty-state icon="fa-solid fa-clock-rotate-left" title="Belum ada riwayat" description="Riwayat persetujuan akan muncul di sini." />
        @else
            <div class="overflow-x-auto">
                <table class="w-full min-w-[760px] text-left text-sm">
                    <thead class="border-b border-slate-200 text-xs uppercase text-slate-500"><tr><th class="px-4 py-3 font-bold">Siswa</th><th class="px-4 py-3 font-bold">Semester</th><th class="px-4 py-3 font-bold">Status</th><th class="px-4 py-3 font-bold">Oleh</th><th class="px-4 py-3 font-bold">Catatan</th></tr></thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($history as $item)
                            <tr>
                                <td class="px-4 py-3 font-bold text-slate-900">{{ $item->student->name }}<span class="block text-xs font-semibold text-slate-400">{{ $item->student->nis }}</span></td>
                                <td class="px-4 py-3">{{ $item->semester_number }}</td>
                                <td class="px-4 py-3">
                                    @if ($item->status === 'approved')
                                        <span class="inline-flex rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-700">Disetujui{{ $item->consumed_at ? ' • dipakai' : ' • aktif' }}</span>
                                    @else
                                        <span class="inline-flex rounded-full bg-rose-50 px-2.5 py-1 text-xs font-bold text-rose-600">Ditolak</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-slate-600">{{ $item->reviewer?->name ?? '-' }}<span class="block text-xs text-slate-400">{{ $item->reviewed_at?->format('d M Y H:i') }}</span></td>
                                <td class="px-4 py-3 text-sm text-slate-600">{{ $item->review_note ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-card>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                window.$('[data-reject-toggle]').on('click', function () {
                    var $btn = window.$(this);
                    var $form = window.$('#' + $btn.attr('aria-controls'));
                    var expanded = $btn.attr('aria-expanded') === 'true';
                    $btn.attr('aria-expanded', String(!expanded));
                    $form.toggleClass('hidden', expanded);
                });
            });
        </script>
    @endpush
@endcomponent
