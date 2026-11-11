<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ArticleController extends Controller
{
    public function index(): View
    {
        // TODO: placeholder — ganti saat mengerjakan PBI-22–25
        return $this->panelPlaceholder('Manajemen Artikel', 'PBI-22–25');
    }

    public function create(): View
    {
        // TODO: placeholder — ganti saat mengerjakan PBI-22–25
        return $this->panelPlaceholder('Manajemen Artikel · Tambah', 'PBI-22–25');
    }

    public function store(): RedirectResponse
    {
        // TODO: placeholder — ganti saat mengerjakan PBI-22–25
        return $this->notAvailable('PBI-22–25');
    }

    public function show(Article $article): View
    {
        // TODO: placeholder — ganti saat mengerjakan PBI-22–25
        return $this->panelPlaceholder('Manajemen Artikel · Detail', 'PBI-22–25');
    }

    public function edit(Article $article): View
    {
        // TODO: placeholder — ganti saat mengerjakan PBI-22–25
        return $this->panelPlaceholder('Manajemen Artikel · Ubah', 'PBI-22–25');
    }

    public function update(Article $article): RedirectResponse
    {
        // TODO: placeholder — ganti saat mengerjakan PBI-22–25
        return $this->notAvailable('PBI-22–25');
    }

    public function destroy(Article $article): RedirectResponse
    {
        // TODO: placeholder — ganti saat mengerjakan PBI-22–25
        return $this->notAvailable('PBI-22–25');
    }
}
