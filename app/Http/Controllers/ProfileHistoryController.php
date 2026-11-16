<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class ProfileHistoryController extends Controller
{
    public function learning(): View
    {
        // TODO: placeholder — ganti saat mengerjakan PBI-06
        return $this->placeholder('Aktivitas E-Learning', 'PBI-06');
    }

    public function events(): View
    {
        // TODO: placeholder — ganti saat mengerjakan PBI-06
        return $this->placeholder('Aktivitas Kegiatan', 'PBI-06');
    }
}
