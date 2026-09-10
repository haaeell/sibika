@props([
    'title' => 'Dashboard',
])

@php
    $navigation = [
        [
            'label' => null,
            'items' => [
                [
                    'label' => 'Dashboard',
                    'icon' => 'fa-solid fa-house',
                    'url' => route('dashboard'),
                    'active' => request()->routeIs('dashboard') || request()->routeIs('*.dashboard'),
                ],
            ],
        ],
        [
            'label' => 'Master Data',
            'items' => [
                ['label' => 'Tahun Ajaran', 'icon' => 'fa-solid fa-calendar-days', 'url' => route('bk.academic-years.index'), 'active' => request()->routeIs('bk.academic-years.*')],
                ['label' => 'Angkatan', 'icon' => 'fa-solid fa-layer-group', 'url' => route('bk.cohorts.index'), 'active' => request()->routeIs('bk.cohorts.*')],
                ['label' => 'Kelas', 'icon' => 'fa-solid fa-school', 'url' => route('bk.school-classes.index'), 'active' => request()->routeIs('bk.school-classes.*')],
                ['label' => 'Guru', 'icon' => 'fa-solid fa-chalkboard-user', 'url' => route('bk.teachers.index'), 'active' => request()->routeIs('bk.teachers.*')],
                ['label' => 'Siswa', 'icon' => 'fa-solid fa-users', 'url' => route('bk.students.index'), 'active' => request()->routeIs('bk.students.*')],
            ],
        ],
        [
            'label' => 'Akademik',
            'items' => [
                ['label' => 'Absensi', 'icon' => 'fa-solid fa-calendar-check', 'url' => '#', 'active' => false],
                ['label' => 'Nilai', 'icon' => 'fa-solid fa-chart-line', 'url' => '#', 'active' => false],
                ['label' => 'TKA', 'icon' => 'fa-solid fa-book-open', 'url' => '#', 'active' => false],
            ],
        ],
        [
            'label' => 'BK & Karir',
            'items' => [
                ['label' => 'Biodata', 'icon' => 'fa-solid fa-id-card', 'url' => '#', 'active' => false],
                ['label' => 'Karir Siswa', 'icon' => 'fa-solid fa-compass', 'url' => '#', 'active' => false],
                ['label' => 'Kampus', 'icon' => 'fa-solid fa-building-columns', 'url' => '#', 'active' => false],
                ['label' => 'Prestasi', 'icon' => 'fa-solid fa-trophy', 'url' => '#', 'active' => false],
                ['label' => 'Alumni', 'icon' => 'fa-solid fa-user-graduate', 'url' => '#', 'active' => false],
            ],
        ],
        [
            'label' => 'Pembelajaran',
            'items' => [
                ['label' => 'Tugas', 'icon' => 'fa-solid fa-file-pen', 'url' => '#', 'active' => false],
                ['label' => 'CBT', 'icon' => 'fa-solid fa-laptop-file', 'url' => '#', 'active' => false],
            ],
        ],
        [
            'label' => 'Kelulusan',
            'items' => [
                ['label' => 'Eligible', 'icon' => 'fa-solid fa-star', 'url' => '#', 'active' => false],
                ['label' => 'Kelulusan', 'icon' => 'fa-solid fa-graduation-cap', 'url' => '#', 'active' => false],
                ['label' => 'Pengumuman', 'icon' => 'fa-solid fa-bullhorn', 'url' => '#', 'active' => false],
            ],
        ],
        [
            'label' => 'Sistem',
            'items' => [
                ['label' => 'Monitoring', 'icon' => 'fa-solid fa-chart-pie', 'url' => '#', 'active' => false],
                ['label' => 'Laporan', 'icon' => 'fa-solid fa-file-export', 'url' => '#', 'active' => false],
                ['label' => 'Pengaturan', 'icon' => 'fa-solid fa-gear', 'url' => '#', 'active' => false],
            ],
        ],
    ];
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title }} - {{ config('app.name', 'SIBIKA') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 text-slate-800 antialiased">
    <div class="min-h-screen lg:flex">
        <div class="hidden lg:fixed lg:inset-y-0 lg:flex">
            <x-layout.sidebar :navigation="$navigation" />
        </div>

        <div class="fixed inset-0 z-40 hidden bg-slate-900/40 lg:hidden" data-sidebar-overlay></div>
        <div class="fixed inset-y-0 left-0 z-50 hidden lg:hidden" data-sidebar-drawer>
            <x-layout.sidebar :navigation="$navigation" />
        </div>

        <div class="min-w-0 flex-1 lg:pl-64">
            <x-layout.header :title="$title" />

            <main class="p-4 lg:p-6">
                <div class="space-y-6">
                    {{ $slot }}
                </div>
            </main>
        </div>
    </div>

    @if (session('success'))
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                window.showToast({ title: @json(session('success')) });
            });
        </script>
    @endif

    @if (session('error'))
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                window.handleAjaxError({ responseJSON: { message: @json(session('error')) } });
            });
        </script>
    @endif

    @stack('scripts')
</body>
</html>
