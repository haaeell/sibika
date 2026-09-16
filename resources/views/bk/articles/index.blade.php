@component('layouts.app', ['title' => 'Artikel'])
    <x-page-header title="Artikel" description="Kelola informasi universitas, beasiswa, karir, dan pengumuman untuk siswa.">
        <x-slot:actions><x-button :href="route('bk.articles.create')"><i class="fa-solid fa-plus"></i> Tulis Artikel</x-button></x-slot:actions>
    </x-page-header>

    <x-card>
        <form method="GET" class="mb-5 grid gap-3 md:grid-cols-[minmax(0,1fr)_12rem_12rem_auto]">
            <x-form.input name="search" placeholder="Cari judul artikel..." :value="request('search')" icon="fa-solid fa-magnifying-glass" />
            <x-form.select name="category" class="select2">
                <option value="">Semua kategori</option>
                @foreach (\App\Models\Article::CATEGORIES as $value => $label)
                    <option value="{{ $value }}" @selected(request('category') === $value)>{{ $label }}</option>
                @endforeach
            </x-form.select>
            <x-form.select name="status" class="select2">
                <option value="">Semua status</option>
                <option value="published" @selected(request('status') === 'published')>Published</option>
                <option value="draft" @selected(request('status') === 'draft')>Draft</option>
            </x-form.select>
            <x-button type="submit"><i class="fa-solid fa-filter"></i> Filter</x-button>
        </form>

        <div class="space-y-3">
            @forelse ($articles as $article)
                <div class="flex flex-col gap-4 rounded-2xl border border-slate-100 bg-slate-50 p-4 md:flex-row md:items-center">
                    @if ($article->coverImageUrl())
                        <img src="{{ $article->coverImageUrl() }}" alt="{{ $article->title }}" class="h-24 w-full rounded-xl object-cover md:w-36">
                    @else
                        <div class="flex h-24 w-full items-center justify-center rounded-xl bg-white text-slate-300 md:w-36"><i class="fa-regular fa-newspaper text-3xl"></i></div>
                    @endif

                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <x-badge variant="info">{{ $article->categoryLabel() }}</x-badge>
                            <x-badge :variant="$article->status === 'published' ? 'success' : 'neutral'">{{ ucfirst($article->status) }}</x-badge>
                            @if ($article->is_pinned)<x-badge variant="warning">Pinned</x-badge>@endif
                        </div>
                        <h2 class="mt-2 line-clamp-2 text-lg font-extrabold tracking-tight text-slate-950">{{ $article->title }}</h2>
                        <p class="mt-1 line-clamp-2 text-sm text-slate-500">{{ $article->excerpt ?: str(strip_tags($article->content_html))->limit(150) }}</p>
                        <p class="mt-2 text-xs font-semibold text-slate-400">{{ $article->published_at?->translatedFormat('d M Y') ?? 'Belum dipublish' }}</p>
                    </div>

                    <div class="flex items-center justify-end gap-1">
                        <a href="{{ route('bk.articles.edit', $article) }}" class="btn-icon has-tooltip" data-tooltip="Edit" aria-label="Edit"><i class="fa-solid fa-pen"></i></a>
                        <form action="{{ route('bk.articles.destroy', $article) }}" method="POST" class="js-delete-form inline-flex">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn-icon has-tooltip text-rose-600" data-tooltip="Hapus" aria-label="Hapus"><i class="fa-solid fa-trash"></i></button>
                        </form>
                    </div>
                </div>
            @empty
                <x-empty-state icon="fa-regular fa-newspaper" title="Belum ada artikel" description="Tulis artikel pertama untuk siswa." />
            @endforelse
        </div>

        <div class="mt-5">{{ $articles->links() }}</div>
    </x-card>
@endcomponent
