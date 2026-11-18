<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\HeroSection;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class PageController extends Controller
{
    public function home(): View
    {
        $heroSections = Schema::hasTable('hero_sections')
            ? HeroSection::active()->latest('id')->get()
            : collect();

        $latestArticles = Schema::hasTable('articles')
            ? Article::published()
                ->with('author')
                ->latest('published_at')
                ->latest('id')
                ->take(3)
                ->get()
            : collect();

        return view('landing', compact('heroSections', 'latestArticles'));
    }

    public function about(): View
    {
        // TODO: placeholder
        return $this->placeholder('Tentang Kami');
    }
}
