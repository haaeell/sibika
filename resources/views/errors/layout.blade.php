<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $status }} | {{ config('app.name') }}</title>
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen bg-slate-950 text-slate-900 antialiased">
    <main class="relative isolate flex min-h-screen items-center justify-center overflow-hidden px-5 py-10">
        <div class="absolute inset-0 -z-10 bg-[radial-gradient(circle_at_top_right,_#1e40af_0,_transparent_32%),radial-gradient(circle_at_bottom_left,_#0f766e_0,_transparent_28%)] opacity-70"></div>

        <section class="w-full max-w-xl rounded-3xl border border-white/15 bg-white p-7 shadow-2xl shadow-black/30 sm:p-12">
            <img src="{{ asset('images/logo.png') }}" alt="Logo {{ config('app.name') }}" class="h-14 w-auto object-contain">
            <p class="mt-10 text-sm font-extrabold tracking-[0.24em] text-blue-800">ERROR {{ $status }}</p>
            <h1 class="mt-3 text-3xl font-extrabold tracking-tight text-slate-950 sm:text-4xl">{{ $title }}</h1>
            <p class="mt-4 max-w-md text-base font-medium leading-7 text-slate-600">{{ $message }}</p>

            <a href="{{ route('home') }}" class="mt-9 inline-flex min-h-12 items-center justify-center rounded-xl bg-blue-800 px-5 text-sm font-bold text-white shadow-lg shadow-blue-900/20 transition hover:bg-blue-900 focus:outline-none focus:ring-4 focus:ring-blue-700/25">
                Kembali ke beranda
            </a>
        </section>
    </main>
</body>
</html>
