<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Login - {{ config('app.name', 'SIBIKA') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 text-slate-800 antialiased">
    <main class="flex min-h-screen items-center justify-center p-4">
        <section class="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="mb-6">
                <div class="mb-4 flex size-12 items-center justify-center rounded-xl bg-indigo-600 text-sm font-bold text-white">
                    SB
                </div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">Masuk ke SIBIKA</h1>
                <p class="mt-1 text-sm text-slate-500">Template login awal untuk E-Learning BK.</p>
            </div>

            <form action="#" method="POST" class="space-y-4">
                @csrf

                <x-form.input
                    name="login"
                    label="Username / NIS / Email"
                    autocomplete="username"
                    placeholder="Masukkan akun"
                />

                <x-form.input
                    name="password"
                    label="Password"
                    type="password"
                    autocomplete="current-password"
                    placeholder="Masukkan password"
                />

                <label class="flex items-center gap-2 text-sm font-medium text-slate-600">
                    <input type="checkbox" name="remember" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                    Ingat saya
                </label>

                <x-button type="submit" class="w-full">
                    <i class="fa-solid fa-right-to-bracket"></i>
                    Masuk
                </x-button>
            </form>
        </section>
    </main>
</body>
</html>
