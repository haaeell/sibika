@component('layouts.app', ['title' => 'Dashboard Siswa'])
	<x-page-header title="Dashboard Siswa" description="Pantau informasi akademik dan kelengkapan data pribadi kamu." />

	<div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
		<x-card>
			<p class="text-sm font-semibold text-slate-500">Nama</p>
			<p class="mt-2 truncate text-lg font-extrabold text-slate-900">{{ $student->name }}</p>
		</x-card>
		<x-card>
			<p class="text-sm font-semibold text-slate-500">Kelas</p>
			<p class="mt-2 text-lg font-extrabold text-slate-900">{{ $student->schoolClass?->name ?? 'Belum ditempatkan' }}</p>
		</x-card>
		<x-card>
			<p class="text-sm font-semibold text-slate-500">Angkatan</p>
			<p class="mt-2 text-lg font-extrabold text-slate-900">{{ $student->cohort?->name ?? 'Belum ditentukan' }}</p>
		</x-card>
		<x-card>
			<p class="text-sm font-semibold text-slate-500">Status</p>
			<p class="mt-2 text-lg font-extrabold text-emerald-700">{{ $student->status === 'active' ? 'Aktif' : ucfirst($student->status) }}</p>
		</x-card>
	</div>

	<div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_20rem]">
		<x-card title="Kelengkapan Biodata" description="Lengkapi biodata agar BK memiliki informasi yang akurat.">
			<div class="flex flex-wrap items-end justify-between gap-4">
				<div><p class="text-5xl font-extrabold text-blue-900">{{ $progress['percentage'] }}%</p><p class="mt-2 text-sm font-medium text-slate-500">Progress kelengkapan data</p></div>
				<x-button :href="route('siswa.biodata.index')"><i class="fa-solid fa-pen"></i> Lengkapi Biodata</x-button>
			</div>
			<div class="mt-6 h-3 overflow-hidden rounded-full bg-slate-100"><div class="h-full rounded-full bg-blue-800" style="width: {{ $progress['percentage'] }}%"></div></div>
		</x-card>

		<x-card title="Segera Hadir" description="Modul siswa berikutnya sedang disiapkan.">
			<div class="space-y-3 text-sm font-semibold text-slate-600">
				<div class="flex items-center gap-3"><i class="fa-solid fa-chart-line text-blue-800"></i> Nilai Semester <span class="ml-auto rounded-md bg-slate-100 px-1.5 py-0.5 text-[10px] uppercase text-slate-400">Soon</span></div>
				<div class="flex items-center gap-3"><i class="fa-solid fa-calendar-check text-blue-800"></i> Absensi <span class="ml-auto rounded-md bg-slate-100 px-1.5 py-0.5 text-[10px] uppercase text-slate-400">Soon</span></div>
				<div class="flex items-center gap-3"><i class="fa-solid fa-compass text-blue-800"></i> Karir <span class="ml-auto rounded-md bg-slate-100 px-1.5 py-0.5 text-[10px] uppercase text-slate-400">Soon</span></div>
			</div>
		</x-card>
	</div>
@endcomponent
