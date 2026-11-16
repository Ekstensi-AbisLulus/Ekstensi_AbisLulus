<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        // TODO: placeholder — ganti saat mengerjakan Dashboard admin [ASUMSI]
        return $this->panelPlaceholder('Dashboard Admin', 'Dashboard admin [ASUMSI]');
    }
}
