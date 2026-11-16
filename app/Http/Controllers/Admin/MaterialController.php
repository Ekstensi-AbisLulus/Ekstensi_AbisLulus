<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreMaterialRequest;
use App\Models\LearningMaterial;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class MaterialController extends Controller
{
    public function index(): View
    {
        // TODO: placeholder — ganti daftar materi saat mengerjakan PBI-11
        return view('placeholders.panel', [
            'title' => 'Manajemen E-Learning',
            'pbi' => 'PBI-11–13',
            'actionUrl' => route('admin.materials.create'),
            'actionLabel' => 'Tambah Materi Baru',
        ]);
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
        // TODO: placeholder — ganti saat mengerjakan PBI-11
        return $this->panelPlaceholder('Manajemen E-Learning · Detail', 'PBI-11');
    }

    public function edit(LearningMaterial $material): View
    {
        // TODO: placeholder — ganti saat mengerjakan PBI-12
        return $this->panelPlaceholder('Manajemen E-Learning · Ubah', 'PBI-12');
    }

    public function update(LearningMaterial $material): RedirectResponse
    {
        // TODO: placeholder — ganti saat mengerjakan PBI-12
        return $this->notAvailable('PBI-12');
    }

    public function destroy(LearningMaterial $material): RedirectResponse
    {
        // TODO: placeholder — ganti saat mengerjakan PBI-13
        return $this->notAvailable('PBI-13');
    }
}
