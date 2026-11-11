<?php

namespace App\Http\Controllers;

use App\Models\LearningMaterial;
use Illuminate\Http\RedirectResponse;

class ReviewController extends Controller
{
    public function store(LearningMaterial $material): RedirectResponse
    {
        // TODO: placeholder — ganti saat mengerjakan PBI-30
        return $this->notAvailable('PBI-30');
    }

    public function update(LearningMaterial $material): RedirectResponse
    {
        // TODO: placeholder — ganti saat mengerjakan PBI-30
        return $this->notAvailable('PBI-30');
    }
}
