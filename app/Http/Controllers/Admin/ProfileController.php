<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(): View
    {
        // TODO: placeholder — ganti saat mengerjakan PBI-07 & PBI-08
        return $this->panelPlaceholder('Profil Admin', 'PBI-07 & PBI-08');
    }

    public function update(): RedirectResponse
    {
        // TODO: placeholder — ganti saat mengerjakan PBI-07 & PBI-08
        return $this->notAvailable('PBI-07 & PBI-08');
    }
}
