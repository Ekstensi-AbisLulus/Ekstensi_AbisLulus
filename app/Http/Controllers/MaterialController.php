<?php

namespace App\Http\Controllers;

use App\Models\LearningMaterial;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MaterialController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) ($request->input('search') ?? $request->input('q')));

        $materials = LearningMaterial::published()
            ->with('admin')
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->when($search !== '', fn (Builder $query) => $query->where('title', 'like', '%'.$search.'%'))
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        return view('materials.index', compact('materials', 'search'));
    }

    public function show(LearningMaterial $material): View
    {
        abort_unless($material->is_published, 404);

        $material->load([
            'admin',
            'quizzes',
            'reviews' => fn ($query) => $query->with('user')->latest('id'),
        ]);

        return view('materials.show', compact('material'));
    }
}
