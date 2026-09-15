<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        @page { margin: 28px 24px; }
        body { color: #334155; font-family: DejaVu Sans, sans-serif; font-size: 9px; }
        .header { border-bottom: 3px solid #1e3a8a; margin-bottom: 18px; padding-bottom: 10px; }
        .brand { height: 34px; margin: 0; }
        .title { color: #0f172a; font-size: 13px; font-weight: bold; margin: 5px 0 0; }
        .meta { color: #64748b; font-size: 8px; margin-top: 4px; }
        .chart { margin-bottom: 22px; }
        .chart:last-child { margin-bottom: 0; }
        .chart-title { color: #0f172a; font-size: 11px; font-weight: bold; margin: 0 0 8px; }
        .chart img { width: 100%; }
        .page-break { page-break-after: always; }
    </style>
</head>
<body>
    <div class="header">
        <img class="brand" src="{{ public_path('images/logo.png') }}" alt="Logo">
        <p class="title">{{ $title }}</p>
        <p class="meta">Diekspor pada {{ $generatedAt }}</p>
    </div>

    @foreach ($charts as $chart)
        <div class="chart{{ $loop->last ? '' : ' page-break' }}">
            <p class="chart-title">{{ $chart['title'] }}</p>
            <img src="{{ $chart['image'] }}" alt="{{ $chart['title'] }}">
        </div>
    @endforeach
</body>
</html>
