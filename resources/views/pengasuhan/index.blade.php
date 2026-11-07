@component('layouts.app', ['title' => 'Akun Pengasuhan'])
    <x-page-header title="Akun Pengasuhan" description="Kelola akun petugas pengasuhan (pelanggaran dan absensi).">
        <x-slot:actions>
            <x-button :href="route('admin.pengasuhan-accounts.create')"><i class="fa-solid fa-plus"></i> Tambah Akun</x-button>
        </x-slot:actions>
    </x-page-header>

    <x-card>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-slate-200 text-xs uppercase text-slate-500">
                    <tr>
                        <th class="px-4 py-3 font-bold">Nama</th>
                        <th class="px-4 py-3 font-bold">Email</th>
                        <th class="px-4 py-3 font-bold">Akses</th>
                        <th class="px-4 py-3 text-right font-bold">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @if ($users->isNotEmpty())
                        @foreach ($users as $user)
                            <tr>
                                <td class="px-4 py-3 font-medium text-slate-900">{{ $user->name }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ $user->email }}</td>
                                <td class="px-4 py-3">
                                    @if ($user->permissions->isNotEmpty())
                                        @foreach ($user->permissions as $perm)
                                            <x-badge class="mr-2 mb-1">{{ str_replace('pengasuhan.', '', $perm->name) }}</x-badge>
                                        @endforeach
                                    @else
                                        <span class="text-xs text-slate-400">Tanpa akses</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <x-button variant="secondary" :href="route('admin.pengasuhan-accounts.edit', $user)">Ubah</x-button>
                                    <form action="{{ route('admin.pengasuhan-accounts.destroy', $user) }}" method="POST" class="js-delete-form inline" data-confirm-title="Hapus akun pengasuhan?">
                                        @csrf @method('DELETE')
                                        <x-button variant="danger" type="submit">Hapus</x-button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="4" class="px-4 py-8 text-center text-slate-500">Belum ada akun pengasuhan.</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </x-card>
@endcomponent
