<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreMaterialRequest;
use App\Http\Requests\Admin\UpdateMaterialRequest;
use App\Models\LearningMaterial;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class MaterialController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', LearningMaterial::class);

        $search = trim((string) ($request->input('search') ?? $request->input('q')));

        $materials = LearningMaterial::with('admin')
            ->when($search !== '', fn (Builder $query) => $query->where('title', 'like', '%'.$search.'%'))
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        return view('admin.materials.index', compact('materials', 'search'));
    }

    public function create(): View
    {
        Gate::authorize('create', LearningMaterial::class);

        return view('admin.materials.create');
    }

    public function store(StoreMaterialRequest $request): RedirectResponse
    {
        Gate::authorize('create', LearningMaterial::class);

        $thumbnailUrl = $request->validated('thumbnail_url');

        if ($request->hasFile('thumbnail')) {
            $thumbnailUrl = $request->file('thumbnail')->store('materials', 'public');
        }

        LearningMaterial::create([
            'admin_id' => $request->user()->id,
            'title' => $request->validated('title'),
            'description' => $request->validated('description'),
            'video_url' => $request->validated('video_url'),
            'thumbnail_url' => $thumbnailUrl,
            'is_published' => $request->has('is_published') ? $request->boolean('is_published') : true,
        ]);

        return redirect()
            ->route('admin.materials.index')
            ->with('success', 'Materi pembelajaran berhasil ditambahkan.');
    }

    public function show(LearningMaterial $material): View
    {
        Gate::authorize('view', $material);

        $material->load(['admin', 'quizzes'])
            ->loadAvg('reviews', 'rating')
            ->loadCount('reviews');

        return view('admin.materials.show', compact('material'));
    }

    public function edit(LearningMaterial $material): View
    {
        Gate::authorize('update', $material);

        return view('admin.materials.edit', compact('material'));
    }

    public function update(UpdateMaterialRequest $request, LearningMaterial $material): RedirectResponse
    {
        Gate::authorize('update', $material);

        $thumbnailUrl = $material->thumbnail_url;
        $wasExternalUrl = is_string($thumbnailUrl)
            && (str_starts_with($thumbnailUrl, 'http://') || str_starts_with($thumbnailUrl, 'https://'));

        if ($request->hasFile('thumbnail')) {
            $material->deleteStoredThumbnail();
            $thumbnailUrl = $request->file('thumbnail')->store('materials', 'public');
        } elseif ($request->filled('thumbnail_url')) {
            $newThumbnailUrl = $request->validated('thumbnail_url');
            if ($newThumbnailUrl !== $material->thumbnail_url) {
                $material->deleteStoredThumbnail();
            }
            $thumbnailUrl = $newThumbnailUrl;
        } elseif ($wasExternalUrl && $request->exists('thumbnail_url')) {
            $thumbnailUrl = null;
        }

        $material->update([
            'title' => $request->validated('title'),
            'description' => $request->validated('description'),
            'video_url' => $request->validated('video_url'),
            'thumbnail_url' => $thumbnailUrl,
            'is_published' => $request->has('is_published')
                ? $request->boolean('is_published')
                : $material->is_published,
        ]);

        return redirect()
            ->route('admin.materials.index')
            ->with('success', 'Materi pembelajaran berhasil diperbarui.');
    }

    public function destroy(LearningMaterial $material): RedirectResponse
    {
        // TODO: placeholder — ganti saat mengerjakan PBI-13
        return $this->notAvailable('PBI-13');
    }
}
