<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <x-seo-meta title="Login" :description="$loginSetting->seo_description" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-white text-slate-800 antialiased">
    <main class="min-h-screen lg:grid lg:h-screen lg:grid-cols-[1.15fr_0.85fr] lg:overflow-hidden">
        <section class="relative hidden min-h-screen overflow-hidden bg-slate-900 lg:block lg:min-h-0">
            <img
                src="{{ $loginSetting->heroImageUrl() }}"
                alt="{{ $loginSetting->school_name }}"
                class="absolute inset-0 h-full w-full object-cover"
            >
            <div class="absolute inset-0 bg-blue-950/40"></div>
            <div class="absolute inset-y-0 left-0 w-2/3 bg-gradient-to-r from-blue-950/80 via-blue-950/45 to-transparent"></div>
            <div class="absolute inset-x-0 bottom-0 h-1/2 bg-gradient-to-t from-blue-950/70 to-transparent"></div>

            <div class="relative flex h-full min-h-screen flex-col justify-between px-10 py-8 xl:px-14">
                <div class="flex items-center gap-4 text-white">
                    <img
                        src="{{ $loginSetting->logoUrl() }}"
                        alt="Logo {{ $loginSetting->school_name }}"
                        class="size-14 object-contain drop-shadow-xl"
                    >
                    <div>
                        <p class="text-xl font-bold tracking-tight">{{ $loginSetting->app_name }}</p>
                        <p class="mt-1 text-base font-medium text-white/85">{{ $loginSetting->school_name }}</p>
                    </div>
                </div>

                <div class="max-w-xl">
                    <div class="mb-8 h-1 w-12 rounded-full bg-yellow-400"></div>
                    <h1 class="text-4xl font-bold leading-tight tracking-tight text-white xl:text-5xl">
                        {{ $loginSetting->hero_title }}
                    </h1>
                    <p class="mt-5 text-lg leading-8 text-white/85">
                        {{ $loginSetting->hero_description }}
                    </p>
                </div>

                <div class="text-white">
                    <div class="mb-5 h-1 w-12 rounded-full bg-yellow-400"></div>
                    <p class="text-sm font-bold">{{ $loginSetting->footer_name }}</p>
                    <p class="mt-1 text-sm font-semibold text-white/80">{{ $loginSetting->footer_tagline }}</p>
                </div>
            </div>
        </section>

        <section class="flex min-h-screen flex-col bg-slate-50 px-5 py-5 sm:px-8 lg:min-h-0 lg:overflow-y-auto lg:px-10 xl:px-16">
            <div class="flex justify-end">
                <a href="{{ $loginSetting->helpWhatsappUrl() ?? '#' }}" @if ($loginSetting->helpWhatsappUrl()) target="_blank" rel="noopener" @endif class="inline-flex items-center gap-2 text-sm font-semibold text-slate-500 transition hover:text-blue-800">
                    <i class="fa-regular fa-circle-question"></i>
                    {{ $loginSetting->help_text }}
                </a>
            </div>

            <div class="flex flex-1 items-center justify-center py-5">
                <div class="w-full max-w-lg rounded-2xl border border-slate-200 bg-white px-6 py-7 shadow-sm sm:px-8 lg:px-10">
                    <div class="text-center">
                        <img
                            src="{{ $loginSetting->logoUrl() }}"
                            alt="Logo {{ $loginSetting->school_name }}"
                            class="mx-auto size-16 object-contain"
                        >
                        <h1 class="mt-5 text-2xl font-bold tracking-tight text-slate-950">{{ $loginSetting->welcome_title }}</h1>
                        <p class="mt-2 text-sm font-semibold text-slate-500">{{ $loginSetting->welcome_subtitle }}</p>
                    </div>

                    <form action="{{ route('login.store') }}" method="POST" class="mt-7 space-y-4">
                        @csrf

                        <div>
                            <label for="login" class="mb-2 block text-sm font-bold text-slate-800">Email / NIS / NISN</label>
                            <div class="relative">
                                <span class="pointer-events-none absolute inset-y-0 left-0 flex w-12 items-center justify-center text-slate-400">
                                    <i class="fa-regular fa-user"></i>
                                </span>
                                <input
                                    id="login"
                                    name="login"
                                    type="text"
                                    autocomplete="username"
                                    placeholder="Masukkan email, NIS, atau NISN"
                                    value="{{ old('login') }}"
                                    class="h-12 w-full rounded-xl border border-slate-200 bg-white pl-12 pr-4 text-sm font-semibold text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-blue-700 focus:ring-4 focus:ring-blue-700/10"
                                >
                            </div>
                            <x-form.error name="login" />
                        </div>

                        <div>
                            <label for="password" class="mb-2 block text-sm font-bold text-slate-800">Password</label>
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
                </div>
            </div>

            <p class="text-center text-sm font-semibold text-slate-400">{{ $loginSetting->copyright_text }}</p>
        </section>
    </main>
</body>
</html>
