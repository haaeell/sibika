@props([
    'title' => null,
    'description' => 'SIBIKA SMA Plus Astha Hannas — portal bimbingan konseling, akademik, dan perencanaan karir siswa.',
])

@php
    $seoTitle = $title ? $title.' - '.config('app.name', 'SIBIKA') : config('app.name', 'SIBIKA');
    $seoLogo = asset('images/logo.png');
@endphp

<title>{{ $seoTitle }}</title>
<meta name="description" content="{{ $description }}">
<meta name="author" content="SMA Plus Astha Hannas">
<meta name="robots" content="noindex, nofollow">
<meta name="theme-color" content="#1e3a8a">

<link rel="icon" type="image/png" href="{{ $seoLogo }}">
<link rel="shortcut icon" type="image/png" href="{{ $seoLogo }}">
<link rel="apple-touch-icon" href="{{ $seoLogo }}">

<meta property="og:type" content="website">
<meta property="og:locale" content="id_ID">
<meta property="og:site_name" content="{{ config('app.name', 'SIBIKA') }}">
<meta property="og:title" content="{{ $seoTitle }}">
<meta property="og:description" content="{{ $description }}">
<meta property="og:image" content="{{ $seoLogo }}">

<meta name="twitter:card" content="summary">
<meta name="twitter:title" content="{{ $seoTitle }}">
<meta name="twitter:description" content="{{ $description }}">
<meta name="twitter:image" content="{{ $seoLogo }}">
