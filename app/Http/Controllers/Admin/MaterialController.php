<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LearningMaterial;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class MaterialController extends Controller
{
    public function index(): View
    {
        // TODO: placeholder — ganti saat mengerjakan PBI-10–13
        return $this->panelPlaceholder('Manajemen E-Learning', 'PBI-10–13');
    }

    public function create(): View
    {
        // TODO: placeholder — ganti saat mengerjakan PBI-10–13
        return $this->panelPlaceholder('Manajemen E-Learning · Tambah', 'PBI-10–13');
    }

    public function store(): RedirectResponse
    {
        // TODO: placeholder — ganti saat mengerjakan PBI-10–13
        return $this->notAvailable('PBI-10–13');
    }

    public function show(LearningMaterial $material): View
    {
        // TODO: placeholder — ganti saat mengerjakan PBI-10–13
        return $this->panelPlaceholder('Manajemen E-Learning · Detail', 'PBI-10–13');
    }

    public function edit(LearningMaterial $material): View
    {
        // TODO: placeholder — ganti saat mengerjakan PBI-10–13
        return $this->panelPlaceholder('Manajemen E-Learning · Ubah', 'PBI-10–13');
    }

    public function update(LearningMaterial $material): RedirectResponse
    {
        // TODO: placeholder — ganti saat mengerjakan PBI-10–13
        return $this->notAvailable('PBI-10–13');
    }

    public function destroy(LearningMaterial $material): RedirectResponse
    {
        // TODO: placeholder — ganti saat mengerjakan PBI-10–13
        return $this->notAvailable('PBI-10–13');
    }
}
