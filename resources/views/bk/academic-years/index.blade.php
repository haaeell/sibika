@component('layouts.app', ['title' => 'Tahun Ajaran'])
    <x-page-header title="Tahun Ajaran" description="Kelola periode akademik dan semester aktif.">
        <x-slot:actions>
            <x-button :href="route('bk.academic-years.create')">
                <i class="fa-solid fa-plus"></i>
                Tambah Tahun Ajaran
            </x-button>
        </x-slot:actions>
    </x-page-header>

    <x-card>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[760px] text-left text-sm">
                <thead class="border-b border-slate-200 text-xs uppercase text-slate-500">
                    <tr>
                        <th class="px-4 py-3 font-bold">Nama</th>
                        <th class="px-4 py-3 font-bold">Periode</th>
                        <th class="px-4 py-3 font-bold">Semester</th>
                        <th class="px-4 py-3 font-bold">Status</th>
                        <th class="px-4 py-3 text-right font-bold">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($academicYears as $academicYear)
                        <tr class="text-slate-700">
                            <td class="px-4 py-4 font-semibold text-slate-900">{{ $academicYear->name }}</td>
                            <td class="px-4 py-4">{{ $academicYear->start_year }} - {{ $academicYear->end_year }}</td>
                            <td class="px-4 py-4">{{ str($academicYear->semester)->headline() }}</td>
                            <td class="px-4 py-4">
                                @if ($academicYear->is_active)
                                    <span class="inline-flex rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-700">Aktif</span>
                                @else
                                    <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-600">Nonaktif</span>
                                @endif
                            </td>
                            <td class="px-4 py-4">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('bk.academic-years.edit', $academicYear) }}" class="btn-icon" title="Edit">
                                        <i class="fa-solid fa-pen"></i>
                                    </a>
                                    <form action="{{ route('bk.academic-years.destroy', $academicYear) }}" method="POST" class="js-delete-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-icon text-rose-600" title="Hapus">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-10 text-center text-sm font-semibold text-slate-500">Belum ada tahun ajaran.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-5">
            {{ $academicYears->links() }}
        </div>
    </x-card>

    @if (session('success'))
        @push('scripts')
            <script>
                showToast({ title: @json(session('success')) });
            </script>
        @endpush
    @endif

    @push('scripts')
        <script>
            $('.js-delete-form').on('submit', function (event) {
                event.preventDefault();

                confirmAction({
                    title: 'Hapus tahun ajaran?',
                    text: 'Data yang dihapus tidak bisa dikembalikan.',
                    confirmText: 'Ya, hapus',
                }).then((result) => {
                    if (result.isConfirmed) {
                        this.submit();
                    }
                });
            });
        </script>
    @endpush
@endcomponent
