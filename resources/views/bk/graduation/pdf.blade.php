<!DOCTYPE html>
<html lang="id"><head><meta charset="utf-8"><title>Rekap Kelulusan</title>
<style>body{font-family:DejaVu Sans,sans-serif;color:#1e293b;font-size:10px}h1{font-size:18px}table{width:100%;border-collapse:collapse}th{background:#1e3a8a;color:#fff;text-align:left}th,td{padding:7px;border-bottom:1px solid #e2e8f0}tr:nth-child(even){background:#f8fafc}</style></head><body>
<h1>Rekap Kelulusan — {{ $cohort->name }}</h1><p>{{ $rows->count() }} siswa · Seluruhnya dinyatakan Lulus</p>
<table><thead><tr><th>NIS</th><th>NISN</th><th>Nama</th><th>Kelas</th><th>Total Nilai</th><th>Rata-rata</th><th>Peringkat</th><th>Status</th></tr></thead><tbody>
@foreach ($rows as $row)
    <tr><td>{{ $row['student']->nis }}</td><td>{{ $row['student']->nisn ?? '—' }}</td><td>{{ $row['student']->name }}</td><td>{{ $row['student']->schoolClass?->name ?? '—' }}</td><td>{{ $row['total_score'] === null ? '—' : number_format((float) $row['total_score'], 2, ',', '.') }}</td><td>{{ $row['average'] === null ? '—' : number_format((float) $row['average'], 2, ',', '.') }}</td><td>{{ $row['rank'] ?? '—' }}</td><td>Lulus</td></tr>
@endforeach
</tbody></table></body></html>
