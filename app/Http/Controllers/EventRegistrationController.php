<?php

namespace App\Http\Controllers;

use App\Models\CareerEvent;
use Illuminate\Http\RedirectResponse;

class EventRegistrationController extends Controller
{
    public function store(CareerEvent $event): RedirectResponse
    {
        // TODO: placeholder — ganti saat mengerjakan F6 (pendaftaran event)
        return $this->notAvailable('F6 (pendaftaran event)');
    }

    public function destroy(CareerEvent $event): RedirectResponse
    {
        // TODO: placeholder — ganti saat mengerjakan F6 (pendaftaran event)
        return $this->notAvailable('F6 (pendaftaran event)');
    }
}
