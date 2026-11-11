<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class NewPasswordController extends Controller
{
    public function create(string $token): View
    {
        // TODO: placeholder — ganti saat mengerjakan PBI-03
        return $this->placeholder('Atur Ulang Password', 'PBI-03');
    }

    public function store(): RedirectResponse
    {
        // TODO: placeholder — ganti saat mengerjakan PBI-03
        return $this->notAvailable('PBI-03');
    }
}
