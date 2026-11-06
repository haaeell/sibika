<!DOCTYPE html>
<html lang="id"><head><meta charset="utf-8"><title>Rekap Eligible</title>
<style>
    body { font-family: DejaVu Sans, sans-serif; color: #1e293b; font-size: 10px; }
    h1 { font-size: 18px; margin-bottom: 4px; }
    p { margin-top: 0; color: #64748b; }
    table { width: 100%; border-collapse: collapse; }
    th { background: #1e3a8a; color: white; text-align: left; }
    th, td { padding: 7px; border-bottom: 1px solid #e2e8f0; }
    tr:nth-child(even) { background: #f8fafc; }
</style></head><body>
<h1>Rekap Pengumuman Eligible — {{ $announcement->cohort->name }}</h1>
<p>Kuota Eligible {{ $announcement->eligible_quota }} · Cadangan {{ $announcement->reserve_quota }} · Terbit {{ $announcement->published_at->format('d-m-Y H:i') }}</p>
<table><thead><tr><th>Peringkat</th><th>NIS</th><th>Nama</th><th>Kelas</th><th>Total Nilai</th><th>Rata-rata</th><th>Status</th><th>Respons</th></tr></thead><tbody>
@foreach ($results as $result)
    <tr><td>{{ $result->rank ?? '—' }}</td><td>{{ $result->student->nis }}</td><td>{{ $result->student->name }}</td><td>{{ $result->student->schoolClass?->name ?? '—' }}</td><td>{{ $result->total_score === null ? '—' : number_format((float) $result->total_score, 2, ',', '.') }}</td><td>{{ $result->average === null ? '—' : number_format((float) $result->average, 2, ',', '.') }}</td><td>{{ ucfirst($result->category) }}</td><td>{{ $result->response ? ucfirst($result->response) : 'Belum memilih' }}</td></tr>
@endforeach
</tbody></table>
</body></html>
