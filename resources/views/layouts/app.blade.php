@props([
    'title' => 'Dashboard',
])

@php
    $isStudent = auth()->check() && auth()->user()->hasRole('siswa');
    $canManageBiodata = auth()->check() && auth()->user()->hasAnyRole(['bk', 'super_admin']);
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
                ['label' => 'Jurusan', 'icon' => 'fa-solid fa-code-branch', 'url' => route('bk.majors.index'), 'active' => request()->routeIs('bk.majors.*')],
                ['label' => 'Mata Pelajaran', 'icon' => 'fa-solid fa-book-open', 'url' => route('bk.subjects.index'), 'active' => request()->routeIs('bk.subjects.*')],
                ['label' => 'Kampus', 'icon' => 'fa-solid fa-building-columns', 'url' => route('bk.universities.index'), 'active' => request()->routeIs('bk.universities.*')],
                ['label' => 'Kelas', 'icon' => 'fa-solid fa-school', 'url' => route('bk.school-classes.index'), 'active' => request()->routeIs('bk.school-classes.*')],
                ['label' => 'Guru', 'icon' => 'fa-solid fa-chalkboard-user', 'url' => route('bk.teachers.index'), 'active' => request()->routeIs('bk.teachers.*')],
                ['label' => 'Siswa', 'icon' => 'fa-solid fa-users', 'url' => route('bk.students.index'), 'active' => request()->routeIs('bk.students.*')],
            ],
        ],
        [
            'label' => 'Akademik',
            'items' => [
                ['label' => 'Absensi', 'icon' => 'fa-solid fa-calendar-check', 'url' => null, 'active' => false, 'disabled' => true, 'badge' => 'Soon'],
                ['label' => 'Setting Nilai', 'icon' => 'fa-solid fa-sliders', 'url' => route('bk.score-subject-settings.index'), 'active' => request()->routeIs('bk.score-subject-settings.*')],
                ['label' => 'Data Nilai', 'icon' => 'fa-solid fa-chart-line', 'url' => route('bk.student-scores.index'), 'active' => request()->routeIs('bk.student-scores.index', 'bk.student-scores.show', 'bk.student-scores.data', 'bk.student-scores.export')],
                ['label' => 'Laporan Nilai', 'icon' => 'fa-solid fa-chart-pie', 'url' => route('bk.student-scores.report'), 'active' => request()->routeIs('bk.student-scores.report')],
                ['label' => 'Persetujuan Nilai', 'icon' => 'fa-solid fa-stamp', 'url' => route('bk.score-edit-requests.index'), 'active' => request()->routeIs('bk.score-edit-requests.*')],
            ],
        ],
        [
            'label' => 'BK & Karir',
            'items' => [
                ['label' => 'Biodata', 'icon' => 'fa-solid fa-id-card', 'url' => $isStudent ? route('siswa.biodata.index') : ($canManageBiodata ? route('bk.biodata.index') : null), 'active' => ($isStudent && request()->routeIs('siswa.biodata.*')) || ($canManageBiodata && request()->routeIs('bk.biodata.index', 'bk.students.biodata.*')), 'disabled' => ! $isStudent && ! $canManageBiodata, 'badge' => $isStudent || $canManageBiodata ? null : 'Soon'],
                ['label' => 'Laporan Biodata', 'icon' => 'fa-solid fa-chart-pie', 'url' => $canManageBiodata ? route('bk.biodata.report') : null, 'active' => $canManageBiodata && request()->routeIs('bk.biodata.report'), 'disabled' => ! $canManageBiodata, 'badge' => $canManageBiodata ? null : 'Soon'],
                ['label' => 'Karir Siswa', 'icon' => 'fa-solid fa-compass', 'url' => null, 'active' => false, 'disabled' => true, 'badge' => 'Soon'],
                ['label' => 'Prestasi', 'icon' => 'fa-solid fa-trophy', 'url' => null, 'active' => false, 'disabled' => true, 'badge' => 'Soon'],
                ['label' => 'Alumni', 'icon' => 'fa-solid fa-user-graduate', 'url' => null, 'active' => false, 'disabled' => true, 'badge' => 'Soon'],
            ],
        ],
        [
            'label' => 'Pembelajaran',
            'items' => [
                ['label' => 'Tugas', 'icon' => 'fa-solid fa-file-pen', 'url' => null, 'active' => false, 'disabled' => true, 'badge' => 'Soon'],
                ['label' => 'CBT', 'icon' => 'fa-solid fa-laptop-file', 'url' => null, 'active' => false, 'disabled' => true, 'badge' => 'Soon'],
            ],
        ],
        [
            'label' => 'Kelulusan',
            'items' => [
                ['label' => 'Eligible', 'icon' => 'fa-solid fa-star', 'url' => null, 'active' => false, 'disabled' => true, 'badge' => 'Soon'],
                ['label' => 'Kelulusan', 'icon' => 'fa-solid fa-graduation-cap', 'url' => null, 'active' => false, 'disabled' => true, 'badge' => 'Soon'],
                ['label' => 'Pengumuman', 'icon' => 'fa-solid fa-bullhorn', 'url' => null, 'active' => false, 'disabled' => true, 'badge' => 'Soon'],
            ],
        ],
        [
            'label' => 'Sistem',
            'items' => [
                ['label' => 'Monitoring', 'icon' => 'fa-solid fa-chart-pie', 'url' => null, 'active' => false, 'disabled' => true, 'badge' => 'Soon'],
                ['label' => 'Laporan', 'icon' => 'fa-solid fa-file-export', 'url' => null, 'active' => false, 'disabled' => true, 'badge' => 'Soon'],
                ['label' => 'Pengaturan', 'icon' => 'fa-solid fa-gear', 'url' => null, 'active' => false, 'disabled' => true, 'badge' => 'Soon'],
            ],
        ],
    ];

    if ($isStudent) {
        $navigation = [
            [
                'label' => null,
                'items' => [
                    [
                        'label' => 'Dashboard',
                        'icon' => 'fa-solid fa-house',
                        'url' => route('siswa.dashboard'),
                        'active' => request()->routeIs('siswa.dashboard'),
                    ],
                ],
            ],
            [
                'label' => 'BK & Karir',
                'items' => [
                    ['label' => 'Biodata', 'icon' => 'fa-solid fa-id-card', 'url' => route('siswa.biodata.index'), 'active' => request()->routeIs('siswa.biodata.*')],
                ],
            ],
            [
                'label' => 'Akademik',
                'items' => [
                    ['label' => 'Nilai', 'icon' => 'fa-solid fa-chart-line', 'url' => route('siswa.scores.index'), 'active' => request()->routeIs('siswa.scores.*')],
                ],
            ],
        ];
    }
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <x-seo-meta :title="$title" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 text-slate-800 antialiased">
    <div class="min-h-screen lg:flex">
        <div class="hidden transition-transform duration-300 ease-in-out lg:fixed lg:inset-y-0 lg:z-30 lg:flex" data-sidebar-desktop>
            <x-layout.sidebar :navigation="$navigation" id-prefix="desktop-sidebar" />
        </div>

        <div class="fixed inset-0 z-40 hidden bg-slate-900/40 lg:hidden" data-sidebar-overlay></div>
        <div class="fixed inset-y-0 left-0 z-50 hidden lg:hidden" data-sidebar-drawer>
            <x-layout.sidebar :navigation="$navigation" id-prefix="mobile-sidebar" />
        </div>

        <div class="min-w-0 flex-1 transition-[padding] duration-300 ease-in-out lg:pl-64" data-app-content>
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

    @includeWhen(auth()->check(), 'components.auth.force-change-password-modal')

    @stack('scripts')
</body>
</html>
