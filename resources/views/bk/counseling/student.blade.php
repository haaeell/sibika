@component('layouts.app', ['title' => 'Riwayat Konseling '.$student->name])
    <x-page-header title="Riwayat Konseling" description="{{ $student->name }} · {{ $student->nis }} · {{ $student->schoolClass?->name ?? 'Tanpa kelas' }} · {{ $student->cohort?->name ?? 'Tanpa angkatan' }}">
        <x-slot:actions><x-button variant="secondary" :href="route('bk.counseling.index')">Daftar sesi</x-button><x-button :href="route('bk.counseling.create', ['student_id' => $student->id])"><i class="fa-solid fa-plus"></i> Catat sesi</x-button></x-slot:actions>
    </x-page-header>

    <div class="flex flex-wrap items-center gap-3 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <span class="flex size-12 items-center justify-center rounded-xl bg-blue-50 text-lg font-bold text-blue-800">{{ mb_strtoupper(mb_substr($student->name, 0, 1)) }}</span>
        <div><p class="font-bold text-slate-900">{{ $student->name }}</p><p class="text-xs text-slate-500">NIS {{ $student->nis }} · {{ $student->schoolClass?->name ?? 'Tanpa kelas' }} · {{ $student->cohort?->name ?? 'Tanpa angkatan' }}</p></div>
        <div class="ml-auto"><x-badge variant="info">{{ $sessions->total() }} sesi</x-badge></div>
    </div>

    <x-card title="Catatan kronologis" description="Riwayat sesi terbaru ditampilkan lebih dulu; nomor pertemuan mengikuti seluruh riwayat siswa.">
        <form id="counseling-history-filter" method="GET" action="{{ route('bk.counseling.student', $student) }}" class="mb-5 flex flex-wrap items-end gap-3 rounded-xl border border-slate-200 bg-slate-50 p-4">
            <label class="min-w-52 flex-1 text-sm font-semibold text-slate-700">Cari kata
                <input type="search" name="search" value="{{ $filters['search'] ?? '' }}" maxlength="100" placeholder="Topik, masalah, hasil, tindak lanjut" class="mt-1 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm focus:border-blue-700 focus:ring-4 focus:ring-blue-700/10">
            </label>
            <label class="text-sm font-semibold text-slate-700">Dari tanggal
                <input type="date" name="from" value="{{ $filters['from'] ?? '' }}" class="mt-1 block rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm focus:border-blue-700 focus:ring-4 focus:ring-blue-700/10">
            </label>
            <label class="text-sm font-semibold text-slate-700">Sampai tanggal
                <input type="date" name="to" value="{{ $filters['to'] ?? '' }}" min="{{ $filters['from'] ?? '' }}" class="mt-1 block rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm focus:border-blue-700 focus:ring-4 focus:ring-blue-700/10">
            </label>
            <button id="counseling-history-reset" type="button" class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100">Reset</button>
            <noscript><x-button type="submit">Tampilkan</x-button></noscript>
        </form>
        <div id="counseling-history-results" aria-live="polite">@include('bk.counseling._history')</div>
    </x-card>
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const form = document.getElementById('counseling-history-filter');
                const results = document.getElementById('counseling-history-results');
                const search = form.elements.search;
                let timer, controller;

                function highlight() {
                    const term = search.value.trim();
                    if (!term) return;
                    const walker = document.createTreeWalker(results, NodeFilter.SHOW_TEXT);
                    const matches = [];
                    while (walker.nextNode()) {
                        const node = walker.currentNode;
                        if (node.parentElement.closest('a, button, mark, script') || !node.parentElement.closest('[data-search-content]')) continue;
                        if (node.textContent.toLocaleLowerCase().includes(term.toLocaleLowerCase())) matches.push(node);
                    }
                    matches.forEach(node => {
                        const text = node.textContent, fragment = document.createDocumentFragment();
                        let position = 0, found;
                        while ((found = text.toLocaleLowerCase().indexOf(term.toLocaleLowerCase(), position)) !== -1) {
                            fragment.append(document.createTextNode(text.slice(position, found)));
                            const mark = document.createElement('mark');
                            mark.className = 'rounded bg-amber-200 px-0.5 text-slate-900';
                            mark.textContent = text.slice(found, found + term.length);
                            fragment.append(mark);
                            position = found + term.length;
                        }
                        fragment.append(document.createTextNode(text.slice(position)));
                        node.replaceWith(fragment);
                    });
                }

                async function load(url) {
                    controller?.abort();
                    controller = new AbortController();
                    results.setAttribute('aria-busy', 'true');
                    try {
                        const response = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' }, signal: controller.signal });
                        if (!response.ok) throw new Error('Gagal memuat riwayat.');
                        results.innerHTML = await response.text();
                        highlight();
                        history.replaceState(null, '', url);
                    } catch (error) {
                        if (error.name !== 'AbortError') results.textContent = 'Gagal memuat riwayat. Coba lagi.';
                    } finally {
                        if (!controller.signal.aborted) results.removeAttribute('aria-busy');
                    }
                }

                function apply() {
                    const url = new URL(form.action);
                    new FormData(form).forEach((value, key) => { if (value) url.searchParams.set(key, value); });
                    load(url);
                }
                form.addEventListener('submit', event => { event.preventDefault(); clearTimeout(timer); apply(); });
                search.addEventListener('input', () => { clearTimeout(timer); timer = setTimeout(apply, 300); });
                form.querySelectorAll('[type="date"]').forEach(input => input.addEventListener('change', apply));
                document.getElementById('counseling-history-reset').addEventListener('click', () => { form.reset(); apply(); });
                results.addEventListener('click', event => {
                    const link = event.target.closest('a[href*="page="]');
                    if (link && results.contains(link)) { event.preventDefault(); load(link.href); }
                });
                highlight();
            });
        </script>
    @endpush
@endcomponent
