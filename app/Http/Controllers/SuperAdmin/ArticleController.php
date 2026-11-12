<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Enums\ArticleStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\SuperAdmin\StoreArticleRequest;
use App\Models\Article;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ArticleController extends Controller
{
    public function index(): View
    {
        // TODO: ganti daftar artikel saat mengerjakan PBI-23.
        return view('placeholders.panel', [
            'title' => 'Manajemen Artikel',
            'pbi' => 'PBI-23',
            'actionUrl' => route('super-admin.articles.create'),
            'actionLabel' => 'Tambah Artikel',
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
        // TODO: ganti pratinjau detail artikel saat mengerjakan PBI-23.
        return $this->panelPlaceholder('Manajemen Artikel · Detail', 'PBI-23');
    }

    public function edit(Article $article): View
    {
        // TODO: ganti form ubah artikel saat mengerjakan PBI-24.
        return $this->panelPlaceholder('Manajemen Artikel · Ubah', 'PBI-24');
    }

    public function update(Article $article): RedirectResponse
    {
        // TODO: ganti aksi simpan perubahan artikel saat mengerjakan PBI-24.
        return $this->notAvailable('PBI-24');
    }

    public function destroy(Article $article): RedirectResponse
    {
        // TODO: ganti aksi hapus artikel saat mengerjakan PBI-25.
        return $this->notAvailable('PBI-25');
    }
}
