<?php

namespace App\Http\Controllers;

use App\Models\CareerEvent;
use Illuminate\View\View;

class EventController extends Controller
{
    public function index(): View
    {
        // TODO: placeholder — ganti saat mengerjakan F6 (sisi user)
        return $this->placeholder('Kegiatan', 'F6 (sisi user)');
    }

    public function show(CareerEvent $event): View
    {
        // TODO: placeholder — ganti saat mengerjakan F6 (sisi user)
        return $this->placeholder($event->title, 'F6 (sisi user)');
    }
}
