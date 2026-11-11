<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\View\View;

class ArticleController extends Controller
{
    public function index(): View
    {
        // TODO: placeholder — ganti saat mengerjakan PBI-23
        return $this->placeholder('Artikel', 'PBI-23');
    }

    public function show(Article $article): View
    {
        // TODO: placeholder — ganti saat mengerjakan PBI-23
        return $this->placeholder($article->title, 'PBI-23');
    }
}
