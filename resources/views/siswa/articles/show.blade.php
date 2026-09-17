@component('layouts.app', ['title' => 'Artikel'])
    <article class="mx-auto max-w-4xl">
        @if (request()->routeIs('bk.articles.preview'))
            <div class="mb-5 flex items-center justify-between gap-3 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-bold text-amber-800">
                <span><i class="fa-regular fa-eye mr-1.5"></i>Mode preview admin{{ $article->status === 'draft' ? ': draft belum terlihat siswa.' : '.' }}</span>
                <a href="{{ route('bk.articles.edit', $article) }}" class="underline underline-offset-2">Kembali edit</a>
            </div>
        @else
            <a href="{{ route('siswa.articles.index') }}" class="mb-5 inline-flex items-center gap-2 text-sm font-bold text-slate-500 transition hover:text-blue-900"><i class="fa-solid fa-arrow-left"></i> Kembali ke artikel</a>
        @endif

        <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm sm:rounded-[2rem]">
            @if ($article->coverImageUrl())
                <img src="{{ $article->coverImageUrl() }}" alt="{{ $article->title }}" class="h-52 w-full object-cover sm:h-64 md:h-96">
            @endif

            <div class="p-5 sm:p-6 md:p-10">
                <div class="flex flex-wrap items-center gap-2">
                    <x-badge variant="info">{{ $article->categoryLabel() }}</x-badge>
                    @if ($article->is_pinned)<x-badge variant="warning">Pinned</x-badge>@endif
                    <span class="text-sm font-semibold text-slate-400">{{ $article->published_at?->translatedFormat('d F Y') }}</span>
                </div>

                <h1 class="mt-5 break-words text-3xl font-black leading-tight tracking-tight text-slate-950 sm:text-4xl md:text-5xl">{{ $article->title }}</h1>
                @if ($article->excerpt)
                    <p class="mt-5 text-lg leading-8 text-slate-500">{{ $article->excerpt }}</p>
                @endif

                <div class="article-content mt-8 border-t border-slate-100 pt-2">
                    {!! $article->content_html !!}
                </div>
            </div>
        </div>
    </article>
@endcomponent
