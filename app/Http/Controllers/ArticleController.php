<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreArticleRequest;
use App\Http\Requests\UpdateArticleRequest;
use App\Models\Article;
use App\Support\ArticleContentSanitizer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ArticleController extends Controller
{
    public function index(Request $request): View
    {
        $articles = Article::query()
            ->when($request->filled('category'), fn ($query) => $query->where('category', $request->string('category')))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->when($request->filled('search'), fn ($query) => $query->where('title', 'like', '%'.$request->string('search').'%'))
            ->latest('is_pinned')
            ->latest('published_at')
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('bk.articles.index', compact('articles'));
    }

    public function create(): View
    {
        return view('bk.articles.create', [
            'article' => new Article(['category' => 'universitas', 'status' => 'draft']),
        ]);
    }

    public function store(StoreArticleRequest $request, ArticleContentSanitizer $sanitizer): RedirectResponse
    {
        $article = Article::create($this->data($request, $sanitizer) + [
            'created_by' => $request->user()->id,
            'slug' => $this->uniqueSlug((string) $request->string('title')),
        ]);

        $this->storeCover($request, $article);

        return redirect()->route('bk.articles.index')->with('success', 'Artikel berhasil ditambahkan.');
    }

    public function edit(Article $article): View
    {
        return view('bk.articles.edit', compact('article'));
    }

    public function update(UpdateArticleRequest $request, Article $article, ArticleContentSanitizer $sanitizer): RedirectResponse
    {
        $article->update($this->data($request, $sanitizer, $article));
        $this->storeCover($request, $article);

        return redirect()->route('bk.articles.index')->with('success', 'Artikel berhasil diperbarui.');
    }

    public function destroy(Article $article): RedirectResponse
    {
        if ($article->cover_image_path) {
            Storage::disk('public')->delete($article->cover_image_path);
        }

        $article->delete();

        return redirect()->route('bk.articles.index')->with('success', 'Artikel berhasil dihapus.');
    }

    public function uploadImage(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ], [
            'image.required' => 'Gambar wajib dipilih.',
            'image.image' => 'File harus berupa gambar.',
            'image.mimes' => 'Gambar hanya boleh JPG, JPEG, PNG, atau WEBP.',
            'image.max' => 'Ukuran gambar maksimal 4MB.',
        ]);

        $path = $validated['image']->store('articles/content', 'public');

        return response()->json(['url' => Storage::disk('public')->url($path)]);
    }

    private function data(StoreArticleRequest $request, ArticleContentSanitizer $sanitizer, ?Article $article = null): array
    {
        $data = $request->validated();
        unset($data['cover_image']);

        $data['content_html'] = $sanitizer->clean($data['content_html']);
        $data['is_pinned'] = $request->boolean('is_pinned');
        $data['published_at'] = $data['status'] === 'published' ? ($article?->published_at ?? now()) : null;

        return $data;
    }

    private function storeCover(Request $request, Article $article): void
    {
        if (! $request->hasFile('cover_image')) {
            return;
        }

        if ($article->cover_image_path) {
            Storage::disk('public')->delete($article->cover_image_path);
        }

        $article->update(['cover_image_path' => $request->file('cover_image')->store('articles/covers', 'public')]);
    }

    private function uniqueSlug(string $title): string
    {
        $base = Str::slug($title) ?: 'artikel';
        $slug = $base;
        $counter = 2;

        while (Article::where('slug', $slug)->exists()) {
            $slug = $base.'-'.$counter++;
        }

        return $slug;
    }
}
