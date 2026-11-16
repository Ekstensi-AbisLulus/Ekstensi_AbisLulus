<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use App\Models\QuizAttempt;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class QuizController extends Controller
{
    public function show(Quiz $quiz): View
    {
        // TODO: placeholder — ganti saat mengerjakan F5 (sisi user)
        return $this->placeholder($quiz->title, 'F5 (sisi user)');
    }

    public function submit(Quiz $quiz): RedirectResponse
    {
        // TODO: placeholder — ganti saat mengerjakan F5 (sisi user)
        return $this->notAvailable('F5 (sisi user)');
    }

    public function result(QuizAttempt $attempt): View
    {
        // TODO: placeholder — ganti saat mengerjakan F5 (sisi user)
        return $this->placeholder('Hasil Kuis', 'F5 (sisi user)');
    }
}
