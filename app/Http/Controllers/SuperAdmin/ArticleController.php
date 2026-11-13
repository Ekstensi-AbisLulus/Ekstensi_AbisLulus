<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Enums\ArticleStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\SuperAdmin\StoreArticleRequest;
use App\Http\Requests\SuperAdmin\UpdateArticleRequest;
use App\Models\Article;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ArticleController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Article::class);

        $search = trim((string) ($request->input('search') ?? $request->input('q')));
        $selectedStatus = trim((string) $request->input('status'));

        if (! in_array($selectedStatus, ArticleStatus::values(), true)) {
            $selectedStatus = '';
        }

        $articles = Article::with('author')
            ->when($search !== '', fn (Builder $query) => $query->where('title', 'like', '%'.$search.'%'))
            ->when($selectedStatus !== '', fn (Builder $query) => $query->where('status', $selectedStatus))
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        return view('super-admin.articles.index', [
            'articles' => $articles,
            'statuses' => ArticleStatus::options(),
            'search' => $search,
            'selectedStatus' => $selectedStatus,
        ]);
    }

    public function create(): View
    {
        Gate::authorize('create', Article::class);

        return view('super-admin.articles.create', [
            'statuses' => ArticleStatus::options(),
        ]);
    }

    public function store(StoreArticleRequest $request): RedirectResponse
    {
        Gate::authorize('create', Article::class);

        $coverFile = $request->file('cover_image') ?? $request->file('cover');
        $coverPath = $coverFile ? $coverFile->store('articles', 'public') : null;

        $status = ArticleStatus::from($request->validated('status'));

        Article::create([
            'super_admin_id' => $request->user()->id,
            'title' => $request->validated('title'),
            'excerpt' => $request->validated('excerpt'),
            'content' => $request->validated('content'),
            'cover_image_url' => $coverPath,
            'status' => $status,
            'published_at' => $status === ArticleStatus::Published ? now() : null,
        ]);

        return redirect()
            ->route('super-admin.articles.index')
            ->with('success', 'Artikel berhasil ditambahkan.');
    }

    public function show(Article $article): View
    {
        Gate::authorize('view', $article);

        $article->load('author');

        return view('super-admin.articles.show', compact('article'));
    }

    public function edit(Article $article): View
    {
        Gate::authorize('update', $article);

        return view('super-admin.articles.edit', [
            'article' => $article,
            'statuses' => ArticleStatus::options(),
        ]);
    }

    public function update(UpdateArticleRequest $request, Article $article): RedirectResponse
    {
        Gate::authorize('update', $article);

        $coverFile = $request->file('cover_image') ?? $request->file('cover');
        $coverPath = $article->cover_image_url;

        if ($coverFile !== null) {
            $article->deleteStoredCover();
            $coverPath = $coverFile->store('articles', 'public');
        }

        $status = ArticleStatus::from($request->validated('status'));
        $slug = $request->boolean('update_slug')
            ? Article::uniqueSlug($request->validated('title'), $article->id)
            : $article->slug;

        $publishedAt = $article->published_at;
        if ($status === ArticleStatus::Published && $publishedAt === null) {
            $publishedAt = now();
        }

        $article->update([
            'title' => $request->validated('title'),
            'slug' => $slug,
            'excerpt' => $request->validated('excerpt'),
            'content' => $request->validated('content'),
            'cover_image_url' => $coverPath,
            'status' => $status,
            'published_at' => $publishedAt,
        ]);

        return redirect()
            ->route('super-admin.articles.index')
            ->with('success', 'Data berhasil diperbarui.');
    }

    public function destroy(Article $article): RedirectResponse
    {
        // TODO: ganti aksi hapus artikel saat mengerjakan PBI-25.
        return $this->notAvailable('PBI-25');
    }
}
