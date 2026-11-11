<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class QuizController extends Controller
{
    public function index(): View
    {
        // TODO: placeholder — ganti saat mengerjakan PBI-14–17
        return $this->panelPlaceholder('Manajemen Kuis', 'PBI-14–17');
    }

    public function create(): View
    {
        // TODO: placeholder — ganti saat mengerjakan PBI-14–17
        return $this->panelPlaceholder('Manajemen Kuis · Tambah', 'PBI-14–17');
    }

    public function store(): RedirectResponse
    {
        // TODO: placeholder — ganti saat mengerjakan PBI-14–17
        return $this->notAvailable('PBI-14–17');
    }

    public function show(Quiz $quiz): View
    {
        // TODO: placeholder — ganti saat mengerjakan PBI-14–17
        return $this->panelPlaceholder('Manajemen Kuis · Detail', 'PBI-14–17');
    }

    public function edit(Quiz $quiz): View
    {
        // TODO: placeholder — ganti saat mengerjakan PBI-14–17
        return $this->panelPlaceholder('Manajemen Kuis · Ubah', 'PBI-14–17');
    }

    public function update(Quiz $quiz): RedirectResponse
    {
        // TODO: placeholder — ganti saat mengerjakan PBI-14–17
        return $this->notAvailable('PBI-14–17');
    }

    public function destroy(Quiz $quiz): RedirectResponse
    {
        // TODO: placeholder — ganti saat mengerjakan PBI-14–17
        return $this->notAvailable('PBI-14–17');
    }
}
