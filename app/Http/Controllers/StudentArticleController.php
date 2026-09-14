<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentArticleController extends Controller
{
    public function index(Request $request): View
    {
        $articles = Article::published()
            ->when($request->filled('category'), fn ($query) => $query->where('category', $request->string('category')))
            ->when($request->filled('search'), fn ($query) => $query->where('title', 'like', '%'.$request->string('search').'%'))
            ->latest('is_pinned')
            ->latest('published_at')
            ->paginate(9)
            ->withQueryString();

        return view('siswa.articles.index', compact('articles'));
    }

    public function show(Article $article): View
    {
        abort_unless($article->status === 'published' && $article->published_at, 404);

        return view('siswa.articles.show', compact('article'));
    }
}
