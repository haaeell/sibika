@props(['resource'])

<div class="flex flex-wrap items-center gap-2">
    <a href="{{ route('bk.exports.download', ['resource' => $resource, 'format' => 'xlsx']) }}" class="inline-flex items-center justify-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-3.5 py-2.5 text-sm font-semibold text-emerald-700 transition hover:bg-emerald-100 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2" title="Export Excel">
        <i class="fa-solid fa-file-excel"></i>
        Excel
    </a>
    <a href="{{ route('bk.exports.download', ['resource' => $resource, 'format' => 'pdf']) }}" class="inline-flex items-center justify-center gap-2 rounded-xl border border-rose-200 bg-rose-50 px-3.5 py-2.5 text-sm font-semibold text-rose-700 transition hover:bg-rose-100 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2" title="Export PDF">
        <i class="fa-solid fa-file-pdf"></i>
        PDF
    </a>
</div>
