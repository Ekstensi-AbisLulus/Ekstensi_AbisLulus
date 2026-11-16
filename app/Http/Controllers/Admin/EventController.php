<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CareerEvent;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class EventController extends Controller
{
    public function index(): View
    {
        // TODO: placeholder — ganti saat mengerjakan PBI-18–21
        return $this->panelPlaceholder('Manajemen Kegiatan', 'PBI-18–21');
    }

    public function create(): View
    {
        // TODO: placeholder — ganti saat mengerjakan PBI-18–21
        return $this->panelPlaceholder('Manajemen Kegiatan · Tambah', 'PBI-18–21');
    }

    public function store(): RedirectResponse
    {
        // TODO: placeholder — ganti saat mengerjakan PBI-18–21
        return $this->notAvailable('PBI-18–21');
    }

    public function show(CareerEvent $event): View
    {
        // TODO: placeholder — ganti saat mengerjakan PBI-18–21
        return $this->panelPlaceholder('Manajemen Kegiatan · Detail', 'PBI-18–21');
    }

    public function edit(CareerEvent $event): View
    {
        // TODO: placeholder — ganti saat mengerjakan PBI-18–21
        return $this->panelPlaceholder('Manajemen Kegiatan · Ubah', 'PBI-18–21');
    }

    public function update(CareerEvent $event): RedirectResponse
    {
        // TODO: placeholder — ganti saat mengerjakan PBI-18–21
        return $this->notAvailable('PBI-18–21');
    }

    public function destroy(CareerEvent $event): RedirectResponse
    {
        // TODO: placeholder — ganti saat mengerjakan PBI-18–21
        return $this->notAvailable('PBI-18–21');
    }

    public function participants(CareerEvent $event): View
    {
        // TODO: placeholder — ganti saat mengerjakan PBI-19
        return $this->panelPlaceholder('Peserta Kegiatan', 'PBI-19');
    }
}
