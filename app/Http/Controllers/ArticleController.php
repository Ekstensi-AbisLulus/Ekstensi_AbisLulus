<?php

namespace App\Http\Controllers;

use App\Enums\ArticleStatus;
use App\Models\Article;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ArticleController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) ($request->input('search') ?? $request->input('q')));

        $articles = Article::published()
            ->with('author')
            ->when($search !== '', fn (Builder $query) => $query->where('title', 'like', '%'.$search.'%'))
            ->latest('published_at')
            ->latest('id')
            ->paginate(9)
            ->withQueryString();

        return view('articles.index', compact('articles', 'search'));
    }

    public function show(Article $article): View
    {
        abort_unless($article->status === ArticleStatus::Published, 404);

        $article->load('author');

        $relatedArticles = Article::published()
            ->whereKeyNot($article->getKey())
            ->latest('published_at')
            ->latest('id')
            ->take(3)
            ->get();

        return view('articles.show', compact('article', 'relatedArticles'));
    }
}
