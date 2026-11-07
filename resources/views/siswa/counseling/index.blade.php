@component('layouts.app', ['title' => 'Riwayat Konseling'])
    <x-page-header title="Riwayat Konseling" description="Lihat catatan dan tindak lanjut sesi konselingmu." />

    <x-card title="Catatan pertemuan" description="{{ $sessions->total() }} sesi ditemukan. Pertemuan terbaru ditampilkan lebih dulu.">
        <form method="GET" action="{{ route('siswa.counseling.index') }}" class="mb-5 flex flex-wrap items-end gap-3 rounded-xl border border-slate-200 bg-slate-50 p-4">
            <label class="min-w-52 flex-1 text-sm font-semibold text-slate-700">Cari kata
                <input type="search" name="search" value="{{ $filters['search'] ?? '' }}" maxlength="100" placeholder="Topik, masalah, hasil, tindak lanjut" class="mt-1 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm focus:border-blue-700 focus:ring-4 focus:ring-blue-700/10">
            </label>
            <label class="text-sm font-semibold text-slate-700">Dari tanggal
                <input type="date" name="from" value="{{ $filters['from'] ?? '' }}" class="mt-1 block rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm focus:border-blue-700 focus:ring-4 focus:ring-blue-700/10">
            </label>
            <label class="text-sm font-semibold text-slate-700">Sampai tanggal
                <input type="date" name="to" value="{{ $filters['to'] ?? '' }}" min="{{ $filters['from'] ?? '' }}" class="mt-1 block rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm focus:border-blue-700 focus:ring-4 focus:ring-blue-700/10">
            </label>
            <x-button type="submit">Tampilkan</x-button>
            @if (array_filter($filters))
                <x-button variant="secondary" :href="route('siswa.counseling.index')">Reset</x-button>
            @endif
        </form>
        @include('bk.counseling._history', ['readonly' => true])
    </x-card>
@endcomponent
