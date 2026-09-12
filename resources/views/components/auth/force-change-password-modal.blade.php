@if (auth()->check() && auth()->user()->hasRole('siswa') && ! (bool) auth()->user()->must_change_password)
    <div
        id="change-password-modal"
        data-change-password-modal
        class="fixed inset-0 z-[100] hidden items-center justify-center bg-slate-900/60 p-4"
        role="dialog"
        aria-modal="true"
        aria-labelledby="change-password-title"
    >
        <div class="max-h-[90vh] w-full max-w-md overflow-y-auto rounded-2xl bg-white shadow-2xl">
            <div class="flex items-center justify-between bg-blue-900 px-6 py-4 text-white">
                <h2 id="change-password-title" class="text-base font-extrabold tracking-tight">Ganti Password</h2>
                <button type="button" data-change-password-close class="flex size-9 items-center justify-center rounded-xl bg-white/15 transition hover:bg-white/25" aria-label="Tutup">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <form action="{{ route('siswa.password.update') }}" method="POST" data-pw-scope class="space-y-4 px-6 py-6">
                @csrf
                @method('PUT')
                <div>
                    <label for="change-current-password" class="mb-1.5 block text-sm font-semibold text-slate-700">Password Saat Ini <span class="text-rose-500">*</span></label>
                    <div class="relative">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex w-11 items-center justify-center @error('current_password') text-rose-400 @else text-slate-400 @enderror">
                            <i class="fa-solid fa-lock"></i>
                        </span>
                        <input
                            id="change-current-password"
                            name="current_password"
                            type="password"
                            autocomplete="current-password"
                            data-pw-current
                            placeholder="Masukkan password saat ini"
                            class="w-full rounded-xl border bg-white py-2.5 pl-11 pr-11 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:ring-4 @error('current_password') border-rose-400 focus:border-rose-500 focus:ring-rose-500/10 @else border-slate-300 focus:border-blue-700 focus:ring-blue-700/10 @enderror"
                        >
                        <button type="button" class="js-toggle-password absolute inset-y-0 right-0 flex w-11 items-center justify-center text-slate-400 transition hover:text-slate-600" aria-label="Tampilkan password">
                            <i class="fa-regular fa-eye"></i>
                        </button>
                    </div>
                    <p data-caps-warning class="mt-1.5 hidden items-center gap-1.5 text-xs font-bold text-amber-600"><i class="fa-solid fa-triangle-exclamation"></i> Caps Lock aktif — periksa huruf besar/kecil.</p>
                    <x-form.error name="current_password" />
                </div>
                <div>
                    <label for="change-password" class="mb-1.5 block text-sm font-semibold text-slate-700">Password Baru <span class="text-rose-500">*</span></label>
                    <div class="relative">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex w-11 items-center justify-center @error('password') text-rose-400 @else text-slate-400 @enderror">
                            <i class="fa-solid fa-key"></i>
                        </span>
                        <input
                            id="change-password"
                            name="password"
                            type="password"
                            autocomplete="new-password"
                            data-pw-new
                            placeholder="Minimal 8 karakter, huruf + angka"
                            class="w-full rounded-xl border bg-white py-2.5 pl-11 pr-11 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:ring-4 @error('password') border-rose-400 focus:border-rose-500 focus:ring-rose-500/10 @else border-slate-300 focus:border-blue-700 focus:ring-blue-700/10 @enderror"
                        >
                        <button type="button" class="js-toggle-password absolute inset-y-0 right-0 flex w-11 items-center justify-center text-slate-400 transition hover:text-slate-600" aria-label="Tampilkan password">
                            <i class="fa-regular fa-eye"></i>
                        </button>
                    </div>
                    <x-form.error name="password" />
                </div>
                <div>
                    <label for="change-password-confirmation" class="mb-1.5 block text-sm font-semibold text-slate-700">Konfirmasi Password Baru <span class="text-rose-500">*</span></label>
                    <div class="relative">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex w-11 items-center justify-center text-slate-400">
                            <i class="fa-solid fa-lock"></i>
                        </span>
                        <input
                            id="change-password-confirmation"
                            name="password_confirmation"
                            type="password"
                            autocomplete="new-password"
                            data-pw-confirm
                            placeholder="Ulangi password baru"
                            class="w-full rounded-xl border border-slate-300 bg-white py-2.5 pl-11 pr-11 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-blue-700 focus:ring-4 focus:ring-blue-700/10"
                        >
                        <button type="button" class="js-toggle-password absolute inset-y-0 right-0 flex w-11 items-center justify-center text-slate-400 transition hover:text-slate-600" aria-label="Tampilkan password">
                            <i class="fa-regular fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                    <p class="text-xs font-extrabold uppercase tracking-wide text-slate-500">Syarat password baru</p>
                    <ul class="mt-2 space-y-1.5 text-sm font-semibold">
                        <li data-check="length" class="flex items-center gap-2 text-slate-400"><i class="fa-solid fa-circle text-xs"></i> Minimal 8 karakter</li>
                        <li data-check="letter" class="flex items-center gap-2 text-slate-400"><i class="fa-solid fa-circle text-xs"></i> Mengandung huruf (A–Z)</li>
                        <li data-check="number" class="flex items-center gap-2 text-slate-400"><i class="fa-solid fa-circle text-xs"></i> Mengandung angka (0–9)</li>
                        <li data-check="match" class="flex items-center gap-2 text-slate-400"><i class="fa-solid fa-circle text-xs"></i> Konfirmasi sama dengan password baru</li>
                        <li data-check="different" class="flex items-center gap-2 text-slate-400"><i class="fa-solid fa-circle text-xs"></i> Berbeda dari password saat ini</li>
                    </ul>
                </div>

                <button type="submit" data-pw-submit class="inline-flex h-11 w-full items-center justify-center gap-2 rounded-xl bg-blue-900 px-4 text-sm font-bold text-white transition hover:bg-blue-800 disabled:cursor-not-allowed disabled:opacity-50">
                    <i class="fa-solid fa-save"></i> Simpan
                </button>
            </form>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                var modal = document.querySelector('[data-change-password-modal]');
                if (!modal) return;
                var open = function () { modal.classList.remove('hidden'); modal.classList.add('flex'); };
                var close = function () { modal.classList.add('hidden'); modal.classList.remove('flex'); };
                document.querySelectorAll('[data-change-password-open]').forEach(function (btn) {
                    btn.addEventListener('click', function (e) { e.stopPropagation(); open(); });
                });
                modal.querySelectorAll('[data-change-password-close]').forEach(function (btn) {
                    btn.addEventListener('click', close);
                });
                modal.addEventListener('click', function (e) { if (e.target === modal) close(); });
                document.addEventListener('keydown', function (e) { if (e.key === 'Escape') close(); });
            });
        </script>
    @endpush
@endif
@if (auth()->check() && auth()->user()->hasRole('siswa') && (bool) auth()->user()->must_change_password)
    <div
        id="force-password-modal"
        data-force-password-modal
        class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/70 p-4"
        role="dialog"
        aria-modal="true"
        aria-labelledby="force-password-title"
    >
        <div class="max-h-[90vh] w-full max-w-md overflow-y-auto rounded-2xl bg-white shadow-2xl">
            <div class="bg-blue-900 px-6 py-5 text-white">
                <div class="flex items-center gap-3">
                    <span class="flex size-11 items-center justify-center rounded-xl bg-white/15">
                        <i class="fa-solid fa-shield-halved text-xl"></i>
                    </span>
                    <div>
                        <h2 id="force-password-title" class="text-lg font-extrabold tracking-tight">Ganti Password Dulu</h2>
                        <p class="text-sm font-medium text-white/80">Akunmu masih memakai password default (NIS).</p>
                    </div>
                </div>
            </div>

            <form action="{{ route('siswa.password.update') }}" method="POST" data-pw-scope class="space-y-4 px-6 py-6">
                @csrf
                @method('PUT')

                <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-semibold text-amber-800">
                    <i class="fa-solid fa-triangle-exclamation mr-1.5"></i>
                    Demi keamanan, kamu wajib mengganti password sebelum bisa memakai aplikasi.
                </div>

                <div>
                    <label for="force-current-password" class="mb-1.5 block text-sm font-semibold text-slate-700">
                        Password Saat Ini (NIS) <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex w-11 items-center justify-center @error('current_password') text-rose-400 @else text-slate-400 @enderror">
                            <i class="fa-solid fa-lock"></i>
                        </span>
                        <input
                            id="force-current-password"
                            name="current_password"
                            type="password"
                            autocomplete="current-password"
                            data-pw-current
                            placeholder="Masukkan NIS sebagai password saat ini"
                            class="w-full rounded-xl border bg-white py-2.5 pl-11 pr-11 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:ring-4 @error('current_password') border-rose-400 focus:border-rose-500 focus:ring-rose-500/10 @else border-slate-300 focus:border-blue-700 focus:ring-blue-700/10 @enderror"
                        >
                        <button type="button" class="js-toggle-password absolute inset-y-0 right-0 flex w-11 items-center justify-center text-slate-400 transition hover:text-slate-600" aria-label="Tampilkan password">
                            <i class="fa-regular fa-eye"></i>
                        </button>
                    </div>
                    <p data-caps-warning class="mt-1.5 hidden items-center gap-1.5 text-xs font-bold text-amber-600"><i class="fa-solid fa-triangle-exclamation"></i> Caps Lock aktif — periksa huruf besar/kecil.</p>
                    <x-form.error name="current_password" />
                </div>

                <div>
                    <label for="force-password" class="mb-1.5 block text-sm font-semibold text-slate-700">
                        Password Baru <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex w-11 items-center justify-center @error('password') text-rose-400 @else text-slate-400 @enderror">
                            <i class="fa-solid fa-key"></i>
                        </span>
                        <input
                            id="force-password"
                            name="password"
                            type="password"
                            autocomplete="new-password"
                            data-pw-new
                            placeholder="Minimal 8 karakter, huruf + angka"
                            class="w-full rounded-xl border bg-white py-2.5 pl-11 pr-11 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:ring-4 @error('password') border-rose-400 focus:border-rose-500 focus:ring-rose-500/10 @else border-slate-300 focus:border-blue-700 focus:ring-blue-700/10 @enderror"
                        >
                        <button type="button" class="js-toggle-password absolute inset-y-0 right-0 flex w-11 items-center justify-center text-slate-400 transition hover:text-slate-600" aria-label="Tampilkan password">
                            <i class="fa-regular fa-eye"></i>
                        </button>
                    </div>
                    <x-form.error name="password" />
                </div>

                <div>
                    <label for="force-password-confirmation" class="mb-1.5 block text-sm font-semibold text-slate-700">
                        Konfirmasi Password Baru <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex w-11 items-center justify-center text-slate-400">
                            <i class="fa-solid fa-lock"></i>
                        </span>
                        <input
                            id="force-password-confirmation"
                            name="password_confirmation"
                            type="password"
                            autocomplete="new-password"
                            data-pw-confirm
                            placeholder="Ulangi password baru"
                            class="w-full rounded-xl border border-slate-300 bg-white py-2.5 pl-11 pr-11 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-blue-700 focus:ring-4 focus:ring-blue-700/10"
                        >
                        <button type="button" class="js-toggle-password absolute inset-y-0 right-0 flex w-11 items-center justify-center text-slate-400 transition hover:text-slate-600" aria-label="Tampilkan password">
                            <i class="fa-regular fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                    <p class="text-xs font-extrabold uppercase tracking-wide text-slate-500">Syarat password baru</p>
                    <ul class="mt-2 space-y-1.5 text-sm font-semibold">
                        <li data-check="length" class="flex items-center gap-2 text-slate-400"><i class="fa-solid fa-circle text-xs"></i> Minimal 8 karakter</li>
                        <li data-check="letter" class="flex items-center gap-2 text-slate-400"><i class="fa-solid fa-circle text-xs"></i> Mengandung huruf (A–Z)</li>
                        <li data-check="number" class="flex items-center gap-2 text-slate-400"><i class="fa-solid fa-circle text-xs"></i> Mengandung angka (0–9)</li>
                        <li data-check="match" class="flex items-center gap-2 text-slate-400"><i class="fa-solid fa-circle text-xs"></i> Konfirmasi sama dengan password baru</li>
                        <li data-check="different" class="flex items-center gap-2 text-slate-400"><i class="fa-solid fa-circle text-xs"></i> Berbeda dari password saat ini</li>
                    </ul>
                </div>

                <button
                    type="submit"
                    data-pw-submit
                    class="inline-flex h-12 w-full items-center justify-center gap-2 rounded-xl bg-blue-900 px-4 text-sm font-bold text-white shadow-sm transition hover:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-700 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                >
                    <i class="fa-solid fa-save"></i>
                    Simpan Password Baru
                </button>
            </form>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                document.body.classList.add('overflow-hidden');
                var input = document.getElementById('force-current-password');
                if (input) input.focus();
                document.addEventListener('keydown', function (e) {
                    if (e.key === 'Escape') e.preventDefault();
                }, true);
            });
        </script>
    @endpush
@endif

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('[data-pw-scope]').forEach(function (scope) {
                var current = scope.querySelector('[data-pw-current]');
                var fresh = scope.querySelector('[data-pw-new]');
                var confirm = scope.querySelector('[data-pw-confirm]');
                var submit = scope.querySelector('[data-pw-submit]');
                var items = {};
                scope.querySelectorAll('[data-check]').forEach(function (li) {
                    items[li.getAttribute('data-check')] = li;
                });

                var setState = function (li, ok) {
                    if (!li) return;
                    li.classList.toggle('text-emerald-600', ok);
                    li.classList.toggle('text-slate-400', !ok);
                    var icon = li.querySelector('i');
                    if (icon) {
                        icon.classList.toggle('fa-circle-check', ok);
                        icon.classList.toggle('text-emerald-500', ok);
                        icon.classList.toggle('fa-circle', !ok);
                    }
                };

                var update = function () {
                    var v = fresh ? fresh.value : '';
                    var c = confirm ? confirm.value : '';
                    var o = current ? current.value : '';
                    var state = {
                        length: v.length >= 8,
                        letter: /[A-Za-z]/.test(v),
                        number: /[0-9]/.test(v),
                        match: v !== '' && v === c,
                        different: v !== '' && o !== '' && v !== o,
                    };
                    var allOk = true;
                    Object.keys(items).forEach(function (key) {
                        setState(items[key], !!state[key]);
                        if (!state[key]) allOk = false;
                    });
                    if (submit) submit.disabled = !allOk;
                };

                [current, fresh, confirm].forEach(function (el) {
                    if (el) el.addEventListener('input', update);
                });

                // Peringatan Caps Lock pada password saat ini
                if (current) {
                    var warn = scope.querySelector('[data-caps-warning]');
                    current.addEventListener('keyup', function (e) {
                        if (!warn) return;
                        var caps = e.getModifierState && e.getModifierState('CapsLock');
                        warn.classList.toggle('hidden', !caps);
                        warn.classList.toggle('flex', !!caps);
                    });
                }

                update();
            });
        });
    </script>
@endpush
