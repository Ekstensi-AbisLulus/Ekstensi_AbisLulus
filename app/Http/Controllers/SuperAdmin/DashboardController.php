<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        // TODO: placeholder — ganti saat mengerjakan PBI-34
        return $this->panelPlaceholder('Dashboard Super Admin', 'PBI-34');
    }
}
