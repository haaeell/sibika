@props([
    'title' => 'Dashboard',
])

@php
    $isStudent = auth()->check() && auth()->user()->hasRole('siswa');
    $isPrincipal = auth()->check() && auth()->user()->hasRole('kepala_sekolah') && ! auth()->user()->hasAnyRole(['bk', 'super_admin']);
    $canManageBiodata = auth()->check() && auth()->user()->hasAnyRole(['bk', 'super_admin', 'kepala_sekolah']);
    $dashboardUrl = auth()->check() ? \App\Support\DashboardRedirector::for(auth()->user()) : route('login');
    $navigation = [
        [
            'label' => null,
            'items' => [
                [
                    'label' => 'Dashboard',
                    'icon' => 'fa-solid fa-house',
                    'url' => $dashboardUrl,
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
                ['label' => 'Mapel TKA', 'icon' => 'fa-solid fa-list-check', 'url' => route('bk.tka-subjects.index'), 'active' => request()->routeIs('bk.tka-subjects.*')],
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
                ['label' => 'Konseling', 'icon' => 'fa-solid fa-comments', 'url' => route('bk.counseling.index'), 'active' => request()->routeIs('bk.counseling.*')],
                ['label' => 'Artikel', 'icon' => 'fa-regular fa-newspaper', 'url' => $isStudent ? route('siswa.articles.index') : ($canManageBiodata ? route('bk.articles.index') : null), 'active' => request()->routeIs('siswa.articles.*') || request()->routeIs('bk.articles.*'), 'disabled' => ! $isStudent && ! $canManageBiodata, 'badge' => $isStudent || $canManageBiodata ? null : 'Soon'],
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
            'label' => 'Pengasuhan',
            'items' => [
                ['label' => 'Pelanggaran', 'icon' => 'fa-solid fa-triangle-exclamation', 'url' => route('bk.violations.index'), 'active' => request()->routeIs('bk.violations.*')],
            ],
        ],
        [
            'label' => 'Pengajaran',
            'items' => [
                ['label' => 'Absensi', 'icon' => 'fa-solid fa-calendar-check', 'url' => route('bk.attendance.index'), 'active' => request()->routeIs('bk.attendance.*')],
            ],
        ],
        [
            'label' => 'Pengumuman',
            'items' => [
                ['label' => 'Eligible', 'icon' => 'fa-solid fa-star', 'url' => route('bk.eligible.index'), 'active' => request()->routeIs('bk.eligible.*')],
                ['label' => 'Kelulusan', 'icon' => 'fa-solid fa-graduation-cap', 'url' => route('bk.graduation.index'), 'active' => request()->routeIs('bk.graduation.*')],
                ['label' => 'Pengumuman', 'icon' => 'fa-solid fa-bullhorn', 'url' => null, 'active' => false, 'disabled' => true, 'badge' => 'Soon'],
            ],
        ],
        [
            'label' => 'Sistem',
            'items' => [
                ['label' => 'Monitoring', 'icon' => 'fa-solid fa-chart-pie', 'url' => null, 'active' => false, 'disabled' => true, 'badge' => 'Soon'],
                ['label' => 'Laporan', 'icon' => 'fa-solid fa-file-export', 'url' => null, 'active' => false, 'disabled' => true, 'badge' => 'Soon'],
                ['label' => 'Akun Superadmin', 'icon' => 'fa-solid fa-user-shield', 'url' => route('admin.account.edit'), 'active' => request()->routeIs('admin.account.*')],
                ['label' => 'Akun Pengasuhan', 'icon' => 'fa-solid fa-user-tag', 'url' => route('admin.pengasuhan-accounts.index'), 'active' => request()->routeIs('admin.pengasuhan-accounts.*')],
                ['label' => 'Akun Kepala Sekolah', 'icon' => 'fa-solid fa-user-tie', 'url' => route('admin.principals.index'), 'active' => request()->routeIs('admin.principals.*')],
                ['label' => 'Pengaturan Login', 'icon' => 'fa-solid fa-gear', 'url' => route('admin.login-settings.edit'), 'active' => request()->routeIs('admin.login-settings.*')],
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
                    ['label' => 'Konseling', 'icon' => 'fa-solid fa-comments', 'url' => route('siswa.counseling.index'), 'active' => request()->routeIs('siswa.counseling.*')],
                    ['label' => 'Artikel', 'icon' => 'fa-regular fa-newspaper', 'url' => route('siswa.articles.index'), 'active' => request()->routeIs('siswa.articles.*')],
                ],
            ],
            [
                'label' => 'Akademik',
                'items' => [
                    ['label' => 'Nilai', 'icon' => 'fa-solid fa-chart-line', 'url' => route('siswa.scores.index'), 'active' => request()->routeIs('siswa.scores.*')],
                ],
            ],
            // Menu pelanggaran siswa sementara dinonaktifkan; aktifkan bersama rutenya di routes/siswa.php.
            // ['label' => 'Pengasuhan', 'items' => [
            //     ['label' => 'Pelanggaran', 'icon' => 'fa-solid fa-triangle-exclamation', 'url' => route('siswa.violations.index'), 'active' => request()->routeIs('siswa.violations.*')],
            // ]],
            ['label' => 'Kelulusan', 'items' => [
                ['label' => 'Eligible', 'icon' => 'fa-solid fa-star', 'url' => route('siswa.eligible.index'), 'active' => request()->routeIs('siswa.eligible.*')],
                ['label' => 'Kelulusan', 'icon' => 'fa-solid fa-graduation-cap', 'url' => route('siswa.graduation.index'), 'active' => request()->routeIs('siswa.graduation.*')],
            ]],
        ];
    } elseif ($isPrincipal) {
        $navigation = array_values(array_filter(array_map(function ($group) {
            $group['items'] = array_values(array_filter($group['items'], fn ($item) => ! in_array($item['label'], ['Konseling', 'Akun Superadmin', 'Akun Kepala Sekolah'], true)
                && ($item['url'] === null || ! str_contains($item['url'], '/export'))));
            return $group['items'] ? $group : null;
        }, $navigation)));
    } elseif (auth()->check() && auth()->user()->hasRole('pengasuhan')) {
        $navigation = [];
        if (auth()->user()->can('pengasuhan.pelanggaran')) {
            $navigation[] = ['label' => 'Pengasuhan', 'items' => [
                ['label' => 'Pelanggaran', 'icon' => 'fa-solid fa-triangle-exclamation', 'url' => route('bk.violations.index'), 'active' => request()->routeIs('bk.violations.*')],
            ]];
        }
        if (auth()->user()->can('pengasuhan.absensi')) {
            $navigation[] = ['label' => 'Pengajaran', 'items' => [
                ['label' => 'Absensi', 'icon' => 'fa-solid fa-calendar-check', 'url' => route('bk.attendance.index'), 'active' => request()->routeIs('bk.attendance.*')],
            ]];
        }
    } elseif (auth()->check() && auth()->user()->hasRole('wali_kelas')) {
        $navigation = [
            ['label' => null, 'items' => [['label' => 'Dashboard', 'icon' => 'fa-solid fa-house', 'url' => route('wali-kelas.dashboard'), 'active' => request()->routeIs('wali-kelas.dashboard')]]],
            ['label' => 'Akademik', 'items' => [
                ['label' => 'Data Nilai', 'icon' => 'fa-solid fa-chart-line', 'url' => route('wali-kelas.scores.index'), 'active' => request()->routeIs('wali-kelas.scores.index', 'wali-kelas.scores.show', 'wali-kelas.scores.data')],
                ['label' => 'Laporan Nilai', 'icon' => 'fa-solid fa-chart-pie', 'url' => route('wali-kelas.scores.report'), 'active' => request()->routeIs('wali-kelas.scores.report')],
            ]],
            ['label' => 'BK & Karir', 'items' => [
                ['label' => 'Biodata', 'icon' => 'fa-solid fa-id-card', 'url' => route('wali-kelas.biodata.index'), 'active' => request()->routeIs('wali-kelas.biodata.index', 'wali-kelas.biodata.show', 'wali-kelas.biodata.data')],
                ['label' => 'Laporan Biodata', 'icon' => 'fa-solid fa-chart-pie', 'url' => route('wali-kelas.biodata.report'), 'active' => request()->routeIs('wali-kelas.biodata.report')],
            ]],
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
<body class="bg-slate-50 text-slate-800 antialiased {{ $isPrincipal ? 'principal-readonly' : '' }}">
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

    @php($helpSetting = request()->routeIs('siswa.*') ? \App\Models\LoginSetting::current() : null)
    @if ($helpSetting && ! request()->routeIs('siswa.eligible.index'))
        <a href="{{ $helpSetting->helpWhatsappUrl() ?? 'https://wa.me/62882006381163' }}" target="_blank" rel="noopener noreferrer" class="fixed bottom-5 right-5 z-50 inline-flex items-center gap-2 rounded-full bg-emerald-600 px-4 py-3 text-sm font-extrabold text-white shadow-lg shadow-emerald-900/20 transition hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-2" aria-label="Hubungi bantuan via WhatsApp">
            <i class="fa-brands fa-whatsapp text-lg"></i><span>{{ $helpSetting->help_text }}</span>
        </a>
    @endif

    @stack('scripts')
    @if ($isPrincipal)
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const hideActions = () => {
                    document.querySelectorAll('main form').forEach(form => {
                        if (form.method.toLowerCase() !== 'get') {
                            form.addEventListener('submit', event => event.preventDefault());
                            form.querySelectorAll('input, textarea, select').forEach(input => {
                                input.disabled = true;
                                if (input.type === 'file' || input.type === 'hidden' || input.type === 'password') input.hidden = true;
                            });
                            form.querySelectorAll('button, [type="submit"]').forEach(button => button.remove());
                        }
                    });
                    document.querySelectorAll('main a[href]').forEach(link => {
                        if (/\/(create|edit|export|download|template|import|warning-letter|attachment)(\/|\?|$)/.test(link.pathname) || /\/(create|edit)$/.test(link.pathname)) link.remove();
                    });
                    document.querySelectorAll('main button').forEach(button => {
                        if (!button.closest('form[method="GET"], form[method="get"]') && /^(tambah|buat|catat|impor|import|unggah|upload|hapus|ubah|simpan|ganti|reset|unduh|pdf|excel)/i.test(button.textContent.trim())) button.remove();
                    });
                };
                hideActions();
                new MutationObserver(hideActions).observe(document.querySelector('main'), { childList: true, subtree: true });
            });
        </script>
    @endif
</body>
</html>
