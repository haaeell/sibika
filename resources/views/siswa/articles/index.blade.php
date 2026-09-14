@component('layouts.app', ['title' => 'Artikel'])
    <x-page-header title="Artikel" description="Informasi pilihan universitas, beasiswa, karir, dan pengumuman penting." />

    <form method="GET" class="grid gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm md:grid-cols-[minmax(0,1fr)_14rem_auto]">
        <x-form.input name="search" placeholder="Cari artikel..." :value="request('search')" icon="fa-solid fa-magnifying-glass" />
        <x-form.select name="category">
            <option value="">Semua kategori</option>
            @foreach (\App\Models\Article::CATEGORIES as $value => $label)
                <option value="{{ $value }}" @selected(request('category') === $value)>{{ $label }}</option>
            @endforeach
        </x-form.select>
        <x-button type="submit"><i class="fa-solid fa-filter"></i> Filter</x-button>
    </form>

    <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
        @forelse ($articles as $article)
            <a href="{{ route('siswa.articles.show', $article) }}" class="group overflow-hidden rounded-[1.5rem] border border-slate-200 bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-xl hover:shadow-slate-200/70">
                <div class="relative h-48 overflow-hidden bg-slate-100">
                    @if ($article->coverImageUrl())
                        <img src="{{ $article->coverImageUrl() }}" alt="{{ $article->title }}" class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
                    @else
                        <div class="flex h-full w-full items-center justify-center bg-gradient-to-br from-blue-50 to-slate-100 text-blue-200"><i class="fa-regular fa-newspaper text-5xl"></i></div>
                    @endif
                    <div class="absolute left-4 top-4 flex flex-wrap gap-2">
                        <span class="rounded-full bg-white/95 px-3 py-1 text-xs font-extrabold text-blue-900 shadow-sm">{{ $article->categoryLabel() }}</span>
                        @if ($article->is_pinned)<span class="rounded-full bg-amber-400 px-3 py-1 text-xs font-extrabold text-amber-950 shadow-sm">Pinned</span>@endif
                    </div>
                </div>
                <div class="p-5">
                    <p class="text-xs font-bold uppercase tracking-widest text-slate-400">{{ $article->published_at?->translatedFormat('d M Y') }}</p>
                    <h2 class="mt-2 line-clamp-2 text-xl font-extrabold tracking-tight text-slate-950 transition group-hover:text-blue-900">{{ $article->title }}</h2>
                    <p class="mt-3 line-clamp-3 text-sm leading-6 text-slate-500">{{ $article->excerpt ?: str(strip_tags($article->content_html))->limit(160) }}</p>
                    <span class="mt-5 inline-flex items-center gap-2 text-sm font-extrabold text-blue-900">Baca artikel <i class="fa-solid fa-arrow-right text-xs transition group-hover:translate-x-1"></i></span>
                </div>
            </a>
        @empty
            <div class="md:col-span-2 xl:col-span-3"><x-empty-state icon="fa-regular fa-newspaper" title="Belum ada artikel" description="Artikel yang dipublish akan muncul di sini." /></div>
        @endforelse
    </div>

    <div>{{ $articles->links() }}</div>
@endcomponent
