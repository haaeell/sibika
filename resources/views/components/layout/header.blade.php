@props([
    'title' => 'Dashboard',
])

@php
    $headerNotifications = auth()->check()
        ? \App\Models\Notification::where('user_id', auth()->id())->latest()->take(10)->get()
        : collect();
    $headerUnreadCount = auth()->check()
        ? \App\Models\Notification::where('user_id', auth()->id())->unread()->count()
        : 0;
    $headerNotifIcons = [
        'score_edit_requested' => 'fa-pen-to-square text-amber-600',
        'score_edit_approved' => 'fa-circle-check text-emerald-600',
        'score_edit_rejected' => 'fa-circle-xmark text-rose-600',
    ];
@endphp

<header {{ $attributes->merge(['class' => 'flex h-16 items-center justify-between border-b border-slate-200 bg-white px-4 lg:px-6']) }}>
    <div class="flex min-w-0 flex-1 items-center gap-3">
        <button type="button" class="js-sidebar-layout-toggle inline-flex size-10 items-center justify-center rounded-xl border border-slate-200 text-slate-600 transition hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-700 focus:ring-offset-2" aria-label="Buka atau tutup menu" aria-expanded="true">
            <i class="fa-solid fa-bars"></i>
        </button>

        <div class="min-w-0">
            <p class="truncate text-xs font-medium text-slate-500">{{ $appSetting->app_name }}</p>
            <h1 class="truncate text-base font-bold text-slate-900">{{ $title }}</h1>
        </div>
    </div>

    <div class="flex shrink-0 items-center gap-2">
        <div class="relative">
            <button type="button" class="js-notif-toggle relative inline-flex size-10 items-center justify-center rounded-xl border border-slate-200 text-slate-600 transition hover:bg-slate-50" aria-label="Notifikasi" aria-expanded="false" aria-controls="notif-menu">
                <i class="fa-solid fa-bell"></i>
                @if ($headerUnreadCount > 0)
                    <span data-notif-badge class="absolute -right-1 -top-1 flex h-5 min-w-5 items-center justify-center rounded-full bg-rose-600 px-1 text-[11px] font-extrabold text-white">{{ $headerUnreadCount > 9 ? '9+' : $headerUnreadCount }}</span>
                @endif
            </button>

            <div id="notif-menu" class="fixed inset-x-3 bottom-3 top-20 z-[60] hidden flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xl sm:absolute sm:inset-auto sm:right-0 sm:top-auto sm:mt-2 sm:w-[26.25rem] sm:max-w-[calc(100vw-2rem)]" data-notif-menu>
                <div class="flex items-center justify-between gap-3 border-b border-slate-100 px-4 py-3">
                    <p class="shrink-0 text-sm font-extrabold text-slate-900">Notifikasi</p>
                    @if ($headerUnreadCount > 0)
                        <button type="button" data-notif-read-all class="whitespace-nowrap rounded-lg text-xs font-bold text-blue-800 hover:underline focus:outline-none focus:ring-2 focus:ring-blue-700 focus:ring-offset-2">Tandai semua dibaca</button>
                    @endif
                </div>
                <div class="min-h-0 flex-1 overflow-y-auto py-1 sm:max-h-80" data-notif-list>
                    @forelse ($headerNotifications as $notif)
                        <form action="{{ route('notifications.read', $notif) }}" method="POST" class="block border-b border-slate-50 last:border-0 {{ is_null($notif->read_at) ? 'bg-blue-50/50' : '' }}">
                            @csrf
                            <button type="submit" class="flex w-full items-start gap-3 px-4 py-3 text-left transition hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-blue-700">
                                <i class="fa-solid {{ $headerNotifIcons[$notif->type] ?? 'fa-circle-info text-slate-400' }} mt-1 shrink-0"></i>
                                <span class="min-w-0 flex-1">
                                    <span class="block text-sm font-bold leading-6 text-slate-900">{{ $notif->title }}</span>
                                    <span class="mt-0.5 block text-xs leading-5 text-slate-500">{{ $notif->message }}</span>
                                    <span class="mt-1 block text-[11px] font-semibold text-slate-400">{{ $notif->created_at->locale('id')->diffForHumans() }}</span>
                                </span>
                                @if (is_null($notif->read_at))
                                    <span class="mt-1.5 size-2 shrink-0 rounded-full bg-blue-700"></span>
                                @endif
                            </button>
                        </form>
                    @empty
                        <p class="px-4 py-6 text-center text-sm font-semibold text-slate-400">Belum ada notifikasi.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="relative">
            <button type="button" class="js-user-menu-toggle flex items-center gap-3 rounded-xl border border-slate-200 bg-white px-2 py-2 transition hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-700 focus:ring-offset-2 sm:px-3" aria-expanded="false" aria-controls="user-menu">
                <span class="flex size-8 items-center justify-center rounded-full bg-blue-50 text-xs font-bold text-blue-900">
                    {{ strtoupper(substr(auth()->user()->name ?? 'Guest', 0, 1)) }}
                </span>
                <span class="hidden text-left sm:block">
                    <span class="block text-sm font-semibold text-slate-900">{{ auth()->user()->name ?? 'Guest' }}</span>
                    <span class="block text-xs text-slate-500">{{ auth()->user()->email ?? 'guest@example.test' }}</span>
                </span>
                <i class="hidden fa-solid fa-chevron-down text-xs text-slate-400 sm:inline-block"></i>
            </button>

            <div id="user-menu" class="absolute right-0 z-50 mt-2 hidden w-56 overflow-hidden rounded-2xl border border-slate-200 bg-white py-2 shadow-xl" data-user-menu>
                <div class="border-b border-slate-100 px-4 py-3">
                    <p class="truncate text-sm font-bold text-slate-900">{{ auth()->user()->name ?? 'Guest' }}</p>
                    <p class="truncate text-xs font-medium text-slate-500">{{ auth()->user()->email ?? 'guest@example.test' }}</p>
                </div>
                @if (auth()->check() && auth()->user()->hasRole('siswa'))
                    <button type="button" data-change-password-open class="flex w-full items-center gap-3 px-4 py-2.5 text-left text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                        <i class="fa-solid fa-key w-5 text-center text-slate-400"></i>
                        Ganti Password
                    </button>
                @endif
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="flex w-full items-center gap-3 px-4 py-2.5 text-left text-sm font-semibold text-rose-600 transition hover:bg-rose-50">
                        <i class="fa-solid fa-right-from-bracket w-5 text-center"></i>
                        Keluar
                    </button>
                </form>
            </div>
        </div>
    </div>
</header>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var $toggle = window.$('.js-notif-toggle');
            var $menu = window.$('[data-notif-menu]');

            $toggle.on('click', function (event) {
                event.stopPropagation();
                var expanded = $toggle.attr('aria-expanded') === 'true';
                $toggle.attr('aria-expanded', String(!expanded));
                $menu.toggleClass('hidden', expanded).toggleClass('flex', !expanded);
                if (!expanded) $menu.find('button').first().trigger('focus');
            });

            window.$(document).on('click', function (event) {
                if (!window.$(event.target).closest('.js-notif-toggle, [data-notif-menu]').length) {
                    $menu.addClass('hidden').removeClass('flex');
                    $toggle.attr('aria-expanded', 'false');
                }
            });

            window.$(document).on('keydown', function (event) {
                if (event.key !== 'Escape' || $menu.hasClass('hidden')) return;
                $menu.addClass('hidden').removeClass('flex');
                $toggle.attr('aria-expanded', 'false').trigger('focus');
            });

            window.$('.js-sidebar-layout-toggle').on('click', function () {
                $menu.addClass('hidden').removeClass('flex');
                $toggle.attr('aria-expanded', 'false');
            });

            window.$('[data-notif-read-all]').on('click', function () {
                var $btn = window.$(this);
                window.setButtonLoading($btn, true, '...');
                fetch(@json(route('notifications.read-all')), {
                    method: 'POST',
                    headers: {
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': window.$('meta[name="csrf-token"]').attr('content'),
                    },
                })
                    .then(function (response) {
                        if (!response.ok) throw new Error('Gagal menandai notifikasi.');
                        window.location.reload();
                    })
                    .catch(function () {
                        window.setButtonLoading($btn, false);
                        window.handleAjaxError({ responseJSON: { message: 'Gagal menandai notifikasi.' } });
                    });
            });
        });
    </script>
@endpush
