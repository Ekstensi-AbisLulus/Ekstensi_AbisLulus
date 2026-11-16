<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Testimonial;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TestimonialController extends Controller
{
    public function index(): View
    {
        // TODO: placeholder — ganti saat mengerjakan PBI-33
        return $this->panelPlaceholder('Testimoni', 'PBI-33');
    }

    public function toggle(Testimonial $testimonial): RedirectResponse
    {
        // TODO: placeholder — ganti saat mengerjakan PBI-33
        return $this->notAvailable('PBI-33');
    }
}
