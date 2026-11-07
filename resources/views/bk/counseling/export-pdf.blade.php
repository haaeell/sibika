<!DOCTYPE html>
<html lang="id">
<head><meta charset="utf-8"><title>Rekap Konseling</title>
<style>
    body { font-family: DejaVu Sans, sans-serif; color: #1e293b; font-size: 10px; }
    h1 { font-size: 18px; margin-bottom: 4px; }
    .subtitle { color: #64748b; margin-bottom: 18px; }
    .session { border: 1px solid #cbd5e1; padding: 14px; margin-bottom: 12px; page-break-inside: avoid; }
    .title { font-size: 13px; font-weight: bold; }
    .meta { color: #475569; margin: 6px 0 10px; }
    .label { font-weight: bold; margin: 8px 0 3px; }
    .content { white-space: pre-line; overflow-wrap: anywhere; }
</style></head>
<body>
<h1>Rekap Konseling</h1>
<div class="subtitle">{{ $sessions->count() }} sesi · Dicetak {{ now()->format('d-m-Y H:i') }} · Khusus BK</div>
@forelse ($sessions as $session)
    <div class="session">
        <div class="title">{{ $session->student->name }} ({{ $session->student->nis }}) — {{ $session->topic }}</div>
        <div class="meta">{{ $session->held_at->format('d-m-Y H:i') }} · {{ ucfirst($session->type) }} · {{ $session->student->schoolClass?->name ?? 'Tanpa kelas' }} · {{ $session->student->cohort?->name ?? 'Tanpa angkatan' }} · {{ $session->export_status }}</div>
        <div class="label">Ringkasan masalah</div><div class="content">{{ $session->summary }}</div>
        @if ($session->outcome)<div class="label">Hasil pembahasan</div><div class="content">{{ $session->outcome }}</div>@endif
        @if ($session->follow_up)<div class="label">Tindak lanjut</div><div class="content">{{ $session->follow_up }}</div><div class="meta">Tenggat: {{ $session->due_at?->format('d-m-Y') ?? '—' }}</div>@endif
        <div class="meta">Pencatat: {{ $session->creator?->name ?? 'Akun dihapus' }}</div>
    </div>
@empty
    <p>Tidak ada sesi sesuai filter.</p>
@endforelse
</body></html>
