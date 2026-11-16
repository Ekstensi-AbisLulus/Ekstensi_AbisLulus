<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\HeroSection;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class HeroSectionController extends Controller
{
    public function index(): View
    {
        // TODO: placeholder — ganti saat mengerjakan PBI-22–25
        return $this->panelPlaceholder('Hero Section', 'PBI-22–25');
    }

    public function create(): View
    {
        // TODO: placeholder — ganti saat mengerjakan PBI-22–25
        return $this->panelPlaceholder('Hero Section · Tambah', 'PBI-22–25');
    }

    public function store(): RedirectResponse
    {
        // TODO: placeholder — ganti saat mengerjakan PBI-22–25
        return $this->notAvailable('PBI-22–25');
    }

    public function show(HeroSection $heroSection): View
    {
        // TODO: placeholder — ganti saat mengerjakan PBI-22–25
        return $this->panelPlaceholder('Hero Section · Detail', 'PBI-22–25');
    }

    public function edit(HeroSection $heroSection): View
    {
        // TODO: placeholder — ganti saat mengerjakan PBI-22–25
        return $this->panelPlaceholder('Hero Section · Ubah', 'PBI-22–25');
    }

    public function update(HeroSection $heroSection): RedirectResponse
    {
        // TODO: placeholder — ganti saat mengerjakan PBI-22–25
        return $this->notAvailable('PBI-22–25');
    }

    public function destroy(HeroSection $heroSection): RedirectResponse
    {
        // TODO: placeholder — ganti saat mengerjakan PBI-22–25
        return $this->notAvailable('PBI-22–25');
    }
}
