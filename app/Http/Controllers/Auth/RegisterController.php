<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class RegisterController extends Controller
{
    public function create(): View
    {
        // TODO: placeholder — ganti saat mengerjakan PBI-01
        return $this->placeholder('Daftar', 'PBI-01');
    }

    public function store(): RedirectResponse
    {
        // TODO: placeholder — ganti saat mengerjakan PBI-01
        return $this->notAvailable('PBI-01');
    }
}
