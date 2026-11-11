<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    public function create(): View
    {
        // TODO: placeholder — ganti saat mengerjakan PBI-03
        return $this->placeholder('Lupa Password', 'PBI-03');
    }

    public function store(): RedirectResponse
    {
        // TODO: placeholder — ganti saat mengerjakan PBI-03
        return $this->notAvailable('PBI-03');
    }
}
