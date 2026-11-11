<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function index(): View
    {
        // TODO: placeholder — ganti saat mengerjakan PBI-26–29
        return $this->panelPlaceholder('Manajemen Akun Admin', 'PBI-26–29');
    }

    public function create(): View
    {
        // TODO: placeholder — ganti saat mengerjakan PBI-26–29
        return $this->panelPlaceholder('Manajemen Akun Admin · Tambah', 'PBI-26–29');
    }

    public function store(): RedirectResponse
    {
        // TODO: placeholder — ganti saat mengerjakan PBI-26–29
        return $this->notAvailable('PBI-26–29');
    }

    public function show(User $admin): View
    {
        // TODO: placeholder — ganti saat mengerjakan PBI-26–29
        return $this->panelPlaceholder('Manajemen Akun Admin · Detail', 'PBI-26–29');
    }

    public function edit(User $admin): View
    {
        // TODO: placeholder — ganti saat mengerjakan PBI-26–29
        return $this->panelPlaceholder('Manajemen Akun Admin · Ubah', 'PBI-26–29');
    }

    public function update(User $admin): RedirectResponse
    {
        // TODO: placeholder — ganti saat mengerjakan PBI-26–29
        return $this->notAvailable('PBI-26–29');
    }

    public function destroy(User $admin): RedirectResponse
    {
        // TODO: placeholder — ganti saat mengerjakan PBI-26–29
        return $this->notAvailable('PBI-26–29');
    }
}
