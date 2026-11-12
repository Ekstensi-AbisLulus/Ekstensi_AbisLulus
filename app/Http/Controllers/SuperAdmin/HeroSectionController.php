<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SuperAdmin\StoreHeroSectionRequest;
use App\Models\HeroSection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class HeroSectionController extends Controller
{
    public function index(): View
    {
        // TODO: ganti daftar hero section saat mengerjakan PBI-23.
        return view('placeholders.panel', [
            'title' => 'Hero Section',
            'pbi' => 'PBI-23',
            'actionUrl' => route('super-admin.hero-sections.create'),
            'actionLabel' => 'Tambah Hero Section',
        ]);
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
        // TODO: ganti pratinjau detail hero section saat mengerjakan PBI-23.
        return $this->panelPlaceholder('Hero Section · Detail', 'PBI-23');
    }

    public function edit(HeroSection $heroSection): View
    {
        // TODO: ganti form ubah hero section saat mengerjakan PBI-24.
        return $this->panelPlaceholder('Hero Section · Ubah', 'PBI-24');
    }

    public function update(HeroSection $heroSection): RedirectResponse
    {
        // TODO: ganti aksi simpan perubahan hero section saat mengerjakan PBI-24.
        return $this->notAvailable('PBI-24');
    }

    public function destroy(HeroSection $heroSection): RedirectResponse
    {
        // TODO: ganti aksi hapus hero section saat mengerjakan PBI-25.
        return $this->notAvailable('PBI-25');
    }
}
