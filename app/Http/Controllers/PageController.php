<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class PageController extends Controller
{
    public function about(): View
    {
        // TODO: placeholder
        return $this->placeholder('Tentang Kami');
    }
}
