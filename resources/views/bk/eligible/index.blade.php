@component('layouts.app', ['title' => 'Pengumuman Eligible'])
    <x-page-header title="Pengumuman Eligible" description="Kelola peringkat, kuota, dan pengumuman per angkatan." />

    <div class="eligible-page space-y-5">
        <div class="flex flex-wrap items-end justify-between gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <form method="GET" action="{{ route('bk.eligible.index') }}" class="flex flex-wrap items-end gap-3">
                <label class="block text-sm font-semibold text-slate-700">Angkatan
                    <select name="cohort_id" class="eligible-field mt-1 block min-w-56">
                        @foreach ($cohorts as $option)
                            <option value="{{ $option->id }}" @selected($cohort?->id === $option->id)>{{ $option->name }}</option>
                        @endforeach
                    </select>
                </label>
                <button class="eligible-primary" type="submit">Tampilkan</button>
            </form>
            @if ($announcement)
                <div class="flex flex-wrap gap-2">
                    <a class="eligible-secondary" href="{{ route('bk.eligible.export', ['cohort_id' => $cohort->id, 'format' => 'pdf']) }}"><i class="fa-regular fa-file-pdf" aria-hidden="true"></i> PDF</a>
                    <a class="eligible-secondary" href="{{ route('bk.eligible.export', ['cohort_id' => $cohort->id, 'format' => 'xlsx']) }}"><i class="fa-regular fa-file-excel" aria-hidden="true"></i> Excel</a>
                </div>
            @endif
        </div>

        @if ($cohort)
            @php
                $counts = $announcement ? $rows->countBy('category') : collect();
                $rankedCount = $rows->whereNotNull('rank')->count();
                $hasManualOrder = $announcement && $rows->contains(fn ($row) => $row->rank !== $row->initial_rank);
            @endphp
            <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                @foreach ([['Eligible', 'eligible', 'fa-circle-check'], ['Cadangan', 'cadangan', 'fa-clock'], ['Tidak Eligible', 'tidak eligible', 'fa-circle-xmark'], ['Belum Lengkap', 'belum lengkap', 'fa-circle-minus']] as [$label, $key, $icon])
                    <div class="eligible-summary eligible-{{ str_replace(' ', '-', $key) }}" data-summary="{{ $label }}">
                        <span class="eligible-summary-icon"><i class="fa-solid {{ $icon }}" aria-hidden="true"></i></span>
                        <div><p class="text-xs font-bold uppercase tracking-wide">{{ $label }}</p><p class="mt-1 text-2xl font-extrabold tabular-nums" data-summary-count>{{ $counts[$key] ?? ($key === 'tidak eligible' ? $rankedCount : ($key === 'belum lengkap' ? $rows->count() - $rankedCount : 0)) }}</p></div>
                    </div>
                @endforeach
            </div>

            <form method="POST" action="{{ route('bk.eligible.store') }}" class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                @csrf
                <input type="hidden" name="cohort_id" value="{{ $cohort->id }}">
                <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-100 px-5 py-4">
                    <div><h2 class="font-bold text-slate-900">Pengaturan pengumuman</h2><p class="text-xs text-slate-500">{{ $announcement ? 'Ubah kuota dan jadwal tanpa menghapus peringkat manual.' : 'Simpan untuk menetapkan hasil dari nilai terkini.' }}</p></div>
                    @if ($announcement)
                        <span class="eligible-publish {{ $announcement->published_at->isPast() ? 'eligible-live' : '' }}">{{ $announcement->published_at->isPast() ? 'Sudah tayang' : 'Terjadwal' }}</span>
                    @endif
                </div>
                <div class="grid gap-4 p-5 sm:grid-cols-2 xl:grid-cols-5">
                    <label class="eligible-label">Kuota Eligible
                        <input class="eligible-field" type="number" min="0" name="eligible_quota" required value="{{ old('eligible_quota', $announcement?->eligible_quota ?? 0) }}">
                    </label>
                    <label class="eligible-label">Kuota Cadangan
                        <input class="eligible-field" type="number" min="0" name="reserve_quota" required value="{{ old('reserve_quota', $announcement?->reserve_quota ?? 0) }}">
                    </label>
                    <label class="eligible-label">Jadwal tayang
                        <input class="eligible-field" type="datetime-local" name="published_at" required value="{{ old('published_at', $announcement?->published_at?->format('Y-m-d\TH:i')) }}">
                    </label>
                    <label class="eligible-label">Grup WA Eligible
                        <input class="eligible-field" type="url" name="eligible_wa_url" placeholder="https://chat.whatsapp.com/..." value="{{ old('eligible_wa_url', $announcement?->eligible_wa_url) }}">
                    </label>
                    <label class="eligible-label">Grup WA Cadangan
                        <input class="eligible-field" type="url" name="reserve_wa_url" placeholder="https://chat.whatsapp.com/..." value="{{ old('reserve_wa_url', $announcement?->reserve_wa_url) }}">
                    </label>
                </div>
                <div class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 px-5 py-4">
                    <div class="text-xs text-slate-500">@if ($announcement) Jika jadwal baru di masa depan, hasil siswa tersembunyi sampai tayang lagi. Perubahan kategori mengosongkan respons siswa. @else Siswa hanya melihat hasil setelah jadwal tayang. @endif</div>
                    <button type="submit" class="eligible-primary"><i class="fa-solid fa-floppy-disk" aria-hidden="true"></i> Simpan pengaturan</button>
                </div>
                @if ($errors->any()) <p role="alert" class="px-5 pb-4 text-sm text-red-700">{{ $errors->first() }}</p> @endif
            </form>

            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-5 py-4">
                    <div><h2 class="font-bold text-slate-900">{{ $announcement ? 'Daftar hasil' : 'Pratinjau peringkat' }}</h2><p class="text-xs text-slate-500">{{ $rankedCount }} dari {{ $rows->count() }} siswa memiliki nilai lengkap · peringkat awal untuk nilai seri diurutkan menurut nama dan NIS.</p></div>
                    <div class="flex flex-wrap items-center gap-2">
                        @if ($announcement)
                            <form method="POST" action="{{ route('bk.eligible.reset') }}" id="eligible-reset-form">
                                @csrf
                                <input type="hidden" name="cohort_id" value="{{ $cohort->id }}">
                                <button type="submit" class="eligible-secondary" @disabled(! $hasManualOrder)><i class="fa-solid fa-rotate-left" aria-hidden="true"></i> Reset peringkat</button>
                            </form>
                        @endif
                        <label class="relative block"><span class="sr-only">Cari siswa</span><i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-xs text-slate-400" aria-hidden="true"></i><input id="eligible-search" class="eligible-field w-60 pl-9" type="search" placeholder="Cari nama, NIS, kelas..." aria-controls="eligible-table"></label>
                    </div>
                </div>
                @if ($announcement)
                    <p class="border-b border-slate-100 px-5 py-2 text-xs text-slate-500">Tarik ikon <i class="fa-solid fa-grip-vertical" aria-hidden="true"></i> ke baris tujuan, atau klik Pindah untuk memilih nomor peringkat. Reset mengembalikan urutan awal hasil pengumuman.</p>
                @endif
                <div class="overflow-x-auto">
                    <table id="eligible-table" class="eligible-table w-full min-w-[760px] text-left text-sm">
                        <thead><tr><th scope="col">#</th><th scope="col">Siswa</th><th scope="col" class="text-right">Nilai</th><th scope="col">Status</th><th scope="col">Respons</th>@if ($announcement) <th scope="col" class="text-right">Pindah peringkat</th> @endif</tr></thead>
                        <tbody>
                            @foreach ($rows as $row)
                                @php
                                    $student = $announcement ? $row->student : $row['student'];
                                    $rank = $announcement ? $row->rank : $row['rank'];
                                    $average = $announcement ? $row->average : $row['average'];
                                    $total = $announcement ? $row->total_score : $row['total_score'];
                                    $category = $announcement ? $row->category : \App\Services\EligibleRankingService::category($rank, (int) old('eligible_quota', 0), (int) old('reserve_quota', 0));
                                @endphp
                                <tr data-student-row @if ($announcement && $rank) data-rank="{{ $rank }}" @endif>
                                    <td><span class="eligible-rank">{{ $rank ?? '—' }}</span></td>
                                    <td><div class="font-bold text-slate-900">{{ $student->name }}</div><div class="mt-0.5 text-xs text-slate-500">{{ $student->nis }} <span aria-hidden="true">·</span> {{ $student->schoolClass?->name ?? 'Tanpa kelas' }}</div></td>
                                    <td class="text-right tabular-nums"><div class="font-bold text-slate-900">{{ $average === null ? '—' : number_format((float) $average, 2, ',', '.') }}</div><div class="mt-0.5 text-xs text-slate-500">Total {{ $total === null ? '—' : number_format((float) $total, 2, ',', '.') }}</div></td>
                                    <td><span data-category-rank="{{ $rank }}" class="eligible-badge eligible-{{ str_replace(' ', '-', $category) }}">{{ ucfirst($category) }}</span></td>
                                    <td class="text-slate-600">{{ $announcement && $row->response ? ucfirst($row->response) : '—' }}</td>
                                    @if ($announcement)
                                        <td class="text-right">
                                            @if ($rank)
                                                <div class="inline-flex items-center gap-2">
                                                    <button type="button" class="eligible-drag-handle" draggable="true" data-drag-student="{{ $student->id }}" aria-label="Tarik {{ $student->name }} ke peringkat lain" title="Tarik untuk pindah peringkat"><i class="fa-solid fa-grip-vertical" aria-hidden="true"></i></button>
                                                    <button type="button" class="eligible-move" data-move-student="{{ $student->id }}" data-move-name="{{ $student->name }}" data-move-rank="{{ $rank }}" aria-label="Pindahkan {{ $student->name }} ke peringkat pilihan">Pindah</button>
                                                </div>
                                            @else <span class="text-xs text-slate-400">—</span> @endif
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <p id="eligible-empty" class="hidden p-8 text-center text-sm text-slate-500">Siswa tidak ditemukan.</p>
                </div>
            </div>
            @if ($announcement)
                <dialog id="eligible-move-dialog" class="eligible-dialog" aria-labelledby="eligible-move-title">
                    <form method="POST" action="{{ route('bk.eligible.move') }}" id="eligible-move-form" class="p-6">
                        @csrf
                        <input type="hidden" name="cohort_id" value="{{ $cohort->id }}">
                        <input type="hidden" name="student_id" id="eligible-move-student">
                        <h3 id="eligible-move-title" class="text-lg font-bold text-slate-900">Pindah peringkat</h3>
                        <p id="eligible-move-description" class="mt-1 text-sm text-slate-600"></p>
                        <label for="eligible-move-rank" class="eligible-label mt-5">Pindah ke peringkat
                            <select id="eligible-move-rank" name="rank" class="eligible-field" required>
                                @for ($position = 1; $position <= $rankedCount; $position++)
                                    <option value="{{ $position }}">Peringkat {{ $position }}</option>
                                @endfor
                            </select>
                        </label>
                        <div class="mt-6 flex justify-end gap-2">
                            <button type="button" class="eligible-secondary" id="eligible-move-cancel">Batal</button>
                            <button type="submit" class="eligible-primary">Simpan peringkat</button>
                        </div>
                    </form>
                </dialog>
            @endif
            <script>
                const search = document.getElementById('eligible-search');
                search.addEventListener('input', () => {
                    let shown = 0;
                    document.querySelectorAll('[data-student-row]').forEach(row => {
                        row.hidden = !row.textContent.toLocaleLowerCase().includes(search.value.toLocaleLowerCase().trim());
                        if (!row.hidden) shown++;
                    });
                    document.getElementById('eligible-empty').classList.toggle('hidden', shown > 0);
                });
                @if ($announcement)
                    const moveDialog = document.getElementById('eligible-move-dialog');
                    const moveForm = document.getElementById('eligible-move-form');
                    const moveStudent = document.getElementById('eligible-move-student');
                    const moveRank = document.getElementById('eligible-move-rank');
                    document.querySelectorAll('[data-move-student]').forEach(button => {
                        button.addEventListener('click', () => {
                            moveStudent.value = button.dataset.moveStudent;
                            moveRank.value = button.dataset.moveRank;
                            document.getElementById('eligible-move-description').textContent = button.dataset.moveName + ' saat ini berada di peringkat ' + button.dataset.moveRank + '.';
                            moveDialog.showModal();
                            moveRank.focus();
                        });
                    });
                    document.getElementById('eligible-move-cancel').addEventListener('click', () => moveDialog.close());
                    moveDialog.addEventListener('click', event => {
                        if (event.target === moveDialog) moveDialog.close();
                    });

                    let draggedStudent = null;
                    document.querySelectorAll('[data-drag-student]').forEach(handle => {
                        handle.addEventListener('dragstart', event => {
                            draggedStudent = handle.dataset.dragStudent;
                            event.dataTransfer.effectAllowed = 'move';
                            event.dataTransfer.setData('text/plain', draggedStudent);
                        });
                        handle.addEventListener('dragend', () => {
                            draggedStudent = null;
                            document.querySelectorAll('.eligible-drop-target').forEach(row => row.classList.remove('eligible-drop-target'));
                        });
                    });
                    document.querySelectorAll('[data-student-row][data-rank]').forEach(row => {
                        row.addEventListener('dragover', event => {
                            if (!draggedStudent) return;
                            event.preventDefault();
                            event.dataTransfer.dropEffect = 'move';
                            document.querySelectorAll('.eligible-drop-target').forEach(target => target.classList.remove('eligible-drop-target'));
                            row.classList.add('eligible-drop-target');
                        });
                        row.addEventListener('dragleave', event => {
                            if (!row.contains(event.relatedTarget)) row.classList.remove('eligible-drop-target');
                        });
                        row.addEventListener('drop', event => {
                            event.preventDefault();
                            row.classList.remove('eligible-drop-target');
                            if (!draggedStudent || draggedStudent === row.querySelector('[data-drag-student]')?.dataset.dragStudent) return;
                            moveStudent.value = draggedStudent;
                            moveRank.value = row.dataset.rank;
                            moveForm.submit();
                        });
                    });
                    document.getElementById('eligible-reset-form').addEventListener('submit', event => {
                        if (!window.confirm('Kembalikan semua peringkat ke urutan awal sebelum dipindah admin?')) event.preventDefault();
                    });
                @endif
                @unless ($announcement)
                    const quotaEligible = document.querySelector('[name="eligible_quota"]');
                    const quotaReserve = document.querySelector('[name="reserve_quota"]');
                    function preview() {
                        const counts = { 'Eligible': 0, 'Cadangan': 0, 'Tidak Eligible': 0, 'Belum Lengkap': 0 };
                        document.querySelectorAll('[data-category-rank]').forEach(badge => {
                            const rank = Number(badge.dataset.categoryRank);
                            const eligible = Number(quotaEligible.value), reserve = Number(quotaReserve.value);
                            const category = !rank ? 'Belum Lengkap' : rank <= eligible ? 'Eligible' : rank <= eligible + reserve ? 'Cadangan' : 'Tidak Eligible';
                            badge.textContent = category;
                            badge.className = 'eligible-badge eligible-' + category.toLocaleLowerCase().replace(' ', '-');
                            counts[category]++;
                        });
                        document.querySelectorAll('[data-summary]').forEach(card => card.querySelector('[data-summary-count]').textContent = counts[card.dataset.summary]);
                    }
                    quotaEligible.addEventListener('input', preview);
                    quotaReserve.addEventListener('input', preview);
                    preview();
                @endunless
            </script>
        @endif
    </div>
@endcomponent
