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
        table { border-collapse: collapse; width: 100%; }
        th { background: #1e3a8a; color: #fff; font-size: 8px; padding: 8px 6px; text-align: left; }
        td { border-bottom: 1px solid #e2e8f0; padding: 7px 6px; }
        tr:nth-child(even) td { background: #f8fafc; }
        .empty { color: #64748b; padding: 16px 6px; text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <img class="brand" src="{{ public_path('images/logo.png') }}" alt="Logo">
        <p class="title">{{ $title }}</p>
        <p class="meta">Diekspor pada {{ $generatedAt }}</p>
    </div>

    <table>
        <thead>
            <tr>
                @foreach ($headings as $heading)
                    <th>{{ $heading }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                <tr>
                    @foreach ($row as $value)
                        <td>{{ $value }}</td>
                    @endforeach
                </tr>
            @empty
                <tr><td class="empty" colspan="{{ count($headings) }}">Belum ada data.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
