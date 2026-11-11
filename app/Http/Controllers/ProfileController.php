<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(): View
    {
        // TODO: placeholder — ganti saat mengerjakan PBI-04 & PBI-05
        return $this->placeholder('Profil Saya', 'PBI-04 & PBI-05');
    }

    public function update(): RedirectResponse
    {
        // TODO: placeholder — ganti saat mengerjakan PBI-04 & PBI-05
        return $this->notAvailable('PBI-04 & PBI-05');
    }
}
