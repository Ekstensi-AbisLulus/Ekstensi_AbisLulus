<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class HistoryController extends Controller
{
    public function index(): View
    {
        // TODO: placeholder — ganti saat mengerjakan PBI-09
        return $this->panelPlaceholder('Riwayat Aktivitas', 'PBI-09');
    }
}
