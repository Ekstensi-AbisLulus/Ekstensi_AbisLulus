<?php

namespace App\Http\Controllers;

use App\Models\LearningMaterial;
use Illuminate\View\View;

class MaterialController extends Controller
{
    public function index(): View
    {
        // TODO: placeholder — ganti saat mengerjakan F4 (sisi user)
        return $this->placeholder('E-Learning', 'F4 (sisi user)');
    }

    public function show(LearningMaterial $material): View
    {
        // TODO: placeholder — ganti saat mengerjakan F4 (sisi user)
        return $this->placeholder($material->title, 'F4 (sisi user)');
    }
}
