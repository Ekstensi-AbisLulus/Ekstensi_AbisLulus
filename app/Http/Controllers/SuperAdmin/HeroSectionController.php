<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SuperAdmin\StoreHeroSectionRequest;
use App\Http\Requests\SuperAdmin\UpdateHeroSectionRequest;
use App\Models\HeroSection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class HeroSectionController extends Controller
{
    public function index(): View
    {
        Gate::authorize('viewAny', HeroSection::class);

        $heroSections = HeroSection::with('author')
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        return view('super-admin.hero-sections.index', compact('heroSections'));
    }

    public function create(): View
    {
        Gate::authorize('create', HeroSection::class);

        return view('super-admin.hero-sections.create');
    }

    public function store(StoreHeroSectionRequest $request): RedirectResponse
    {
        Gate::authorize('create', HeroSection::class);

        $imagePath = $request->file('image')->store('hero', 'public');

        HeroSection::create([
            'super_admin_id' => $request->user()->id,
            'title' => $request->validated('title'),
            'subtitle' => $request->validated('subtitle'),
            'image_url' => $imagePath,
            'target_url' => $request->validated('target_url'),
            'is_active' => $request->has('is_active') ? $request->boolean('is_active') : true,
        ]);

        return redirect()
            ->route('super-admin.hero-sections.index')
            ->with('success', 'Hero section berhasil ditambahkan.');
    }

    public function show(HeroSection $heroSection): View
    {
        Gate::authorize('view', $heroSection);

        $heroSection->load('author');

        return view('super-admin.hero-sections.show', compact('heroSection'));
    }

    public function edit(HeroSection $heroSection): View
    {
        Gate::authorize('update', $heroSection);

        return view('super-admin.hero-sections.edit', compact('heroSection'));
    }

    public function update(UpdateHeroSectionRequest $request, HeroSection $heroSection): RedirectResponse
    {
        Gate::authorize('update', $heroSection);

        $imagePath = $heroSection->image_url;

        if ($request->hasFile('image')) {
            $heroSection->deleteStoredImage();
            $imagePath = $request->file('image')->store('hero', 'public');
        }

        $heroSection->update([
            'title' => $request->validated('title'),
            'subtitle' => $request->validated('subtitle'),
            'image_url' => $imagePath,
            'target_url' => $request->validated('target_url'),
            'is_active' => $request->has('is_active') ? $request->boolean('is_active') : $heroSection->is_active,
        ]);

        return redirect()
            ->route('super-admin.hero-sections.index')
            ->with('success', 'Data berhasil diperbarui.');
    }

    public function toggle(HeroSection $heroSection): RedirectResponse
    {
        Gate::authorize('update', $heroSection);

        $heroSection->update([
            'is_active' => ! $heroSection->is_active,
        ]);

        return redirect()
            ->route('super-admin.hero-sections.index')
            ->with('success', 'Data berhasil diperbarui.');
    }

    public function destroy(HeroSection $heroSection): RedirectResponse
    {
        // TODO: ganti aksi hapus hero section saat mengerjakan PBI-25.
        return $this->notAvailable('PBI-25');
    }
}
