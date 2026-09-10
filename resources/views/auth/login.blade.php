<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Login - {{ config('app.name', 'SIBIKA') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-white text-slate-800 antialiased">
    <main class="min-h-screen lg:grid lg:h-screen lg:grid-cols-[1.15fr_0.85fr] lg:overflow-hidden">
        <section class="relative hidden min-h-screen overflow-hidden bg-slate-900 lg:block lg:min-h-0">
            <img
                src="{{ asset('images/login-school-hero.png') }}"
                alt="Gedung sekolah modern"
                class="absolute inset-0 h-full w-full object-cover"
            >
            <div class="absolute inset-0 bg-blue-950/40"></div>
            <div class="absolute inset-y-0 left-0 w-2/3 bg-gradient-to-r from-blue-950/80 via-blue-950/45 to-transparent"></div>
            <div class="absolute inset-x-0 bottom-0 h-1/2 bg-gradient-to-t from-blue-950/70 to-transparent"></div>

            <div class="relative flex h-full min-h-screen flex-col justify-between px-10 py-8 xl:px-14">
                <div class="flex items-center gap-4 text-white">
                    <img
                        src="{{ asset('images/logo.webp') }}"
                        alt="Logo SMA Plus Astha Hannas"
                        class="size-14 object-contain drop-shadow-xl"
                    >
                    <div>
                        <p class="text-xl font-bold tracking-tight">E-Learning BK</p>
                        <p class="mt-1 text-base font-medium text-white/85">SMA Plus Astha Hannas</p>
                    </div>
                </div>

                <div class="max-w-xl">
                    <div class="mb-8 h-1 w-12 rounded-full bg-yellow-400"></div>
                    <h1 class="text-4xl font-bold leading-tight tracking-tight text-white xl:text-5xl">
                        Portal BK dan Karir Siswa
                    </h1>
                    <p class="mt-5 text-lg leading-8 text-white/85">
                        Satu akses untuk pendampingan akademik, pengembangan diri, dan rencana masa depan.
                    </p>
                </div>

                <div class="text-white">
                    <div class="mb-5 h-1 w-12 rounded-full bg-yellow-400"></div>
                    <p class="text-sm font-bold">SMA Plus Astha Hannas</p>
                    <p class="mt-1 text-sm font-semibold text-white/80">Berilmu &bull; Berakhlak &bull; Berprestasi</p>
                </div>
            </div>
        </section>

        <section class="flex min-h-screen flex-col bg-slate-50 px-5 py-5 sm:px-8 lg:min-h-0 lg:overflow-y-auto lg:px-10 xl:px-16">
            <div class="flex justify-end">
                <a href="#" class="inline-flex items-center gap-2 text-sm font-semibold text-slate-500 transition hover:text-blue-800">
                    <i class="fa-regular fa-circle-question"></i>
                    Butuh bantuan?
                </a>
            </div>

            <div class="flex flex-1 items-center justify-center py-5">
                <div class="w-full max-w-lg rounded-2xl border border-slate-200 bg-white px-6 py-7 shadow-sm sm:px-8 lg:px-10">
                    <div class="text-center">
                        <img
                            src="{{ asset('images/logo.webp') }}"
                            alt="Logo SMA Plus Astha Hannas"
                            class="mx-auto size-16 object-contain"
                        >
                        <h1 class="mt-5 text-2xl font-bold tracking-tight text-slate-950">Selamat Datang</h1>
                        <p class="mt-2 text-sm font-semibold text-slate-500">E-Learning BK SMA Plus Astha Hannas</p>
                    </div>

                    <form action="{{ route('login.store') }}" method="POST" class="mt-7 space-y-4">
                        @csrf

                        <div>
                            <label for="login" class="mb-2 block text-sm font-bold text-slate-800">Email</label>
                            <div class="relative">
                                <span class="pointer-events-none absolute inset-y-0 left-0 flex w-12 items-center justify-center text-slate-400">
                                    <i class="fa-regular fa-user"></i>
                                </span>
                                <input
                                    id="login"
                                    name="login"
                                    type="email"
                                    autocomplete="username"
                                    placeholder="Masukkan email"
                                    value="{{ old('login') }}"
                                    class="h-12 w-full rounded-xl border border-slate-200 bg-white pl-12 pr-4 text-sm font-semibold text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-blue-700 focus:ring-4 focus:ring-blue-700/10"
                                >
                            </div>
                            <x-form.error name="login" />
                        </div>

                        <div>
                            <div class="mb-2 flex items-center justify-between gap-3">
                                <label for="password" class="block text-sm font-bold text-slate-800">Password</label>
                                <a href="#" class="text-sm font-bold text-blue-800 hover:text-blue-900">Lupa password?</a>
                            </div>
                            <div class="relative">
                                <span class="pointer-events-none absolute inset-y-0 left-0 flex w-12 items-center justify-center text-slate-400">
                                    <i class="fa-solid fa-lock"></i>
                                </span>
                                <input
                                    id="password"
                                    name="password"
                                    type="password"
                                    autocomplete="current-password"
                                    placeholder="Masukkan password"
                                    class="h-12 w-full rounded-xl border border-slate-200 bg-white pl-12 pr-12 text-sm font-semibold text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-blue-700 focus:ring-4 focus:ring-blue-700/10"
                                >
                                <button type="button" class="js-toggle-password absolute inset-y-0 right-0 flex w-12 items-center justify-center text-slate-400 transition hover:text-slate-600" aria-label="Tampilkan password">
                                    <i class="fa-regular fa-eye"></i>
                                </button>
                            </div>
                            <x-form.error name="password" />
                        </div>

                        <label class="flex items-center gap-3 text-sm font-semibold text-slate-600">
                            <input type="checkbox" name="remember" class="size-5 rounded border-slate-300 text-blue-800 focus:ring-blue-700">
                            Ingat saya
                        </label>

                        <button
                            type="submit"
                            class="inline-flex h-12 w-full items-center justify-center gap-3 rounded-xl bg-blue-900 px-4 text-base font-bold text-white shadow-sm transition hover:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-700 focus:ring-offset-2"
                        >
                            Masuk
                            <i class="fa-solid fa-arrow-right"></i>
                        </button>
                    </form>

                    <div class="mt-6 flex items-center justify-center gap-3 rounded-2xl bg-blue-50 px-5 py-4 text-blue-900">
                        <i class="fa-solid fa-shield-halved text-lg"></i>
                        <p class="text-sm font-semibold">Khusus civitas akademik sekolah</p>
                    </div>
                </div>
            </div>

            <p class="text-center text-sm font-semibold text-slate-400">&copy; 2026 SMA Plus Astha Hannas. All rights reserved.</p>
        </section>
    </main>
</body>
</html>
