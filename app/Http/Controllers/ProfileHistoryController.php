<?php

namespace App\Http\Controllers;

use App\Models\EventRegistration;
use App\Models\QuizAttempt;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ProfileHistoryController extends Controller
{
    public function learning(): View
    {
        $user = request()->user()->load('profile');
        $latestCompletedAttempts = DB::table('quiz_attempts')
            ->select('quiz_id')
            ->selectRaw('MAX(completed_at) as latest_completed_at')
            ->where('user_id', $user->id)
            ->whereNotNull('completed_at')
            ->groupBy('quiz_id');
        $latestAttemptIds = DB::table('quiz_attempts')
            ->joinSub($latestCompletedAttempts, 'latest_completed', function ($join): void {
                $join->on('quiz_attempts.quiz_id', '=', 'latest_completed.quiz_id')
                    ->on('quiz_attempts.completed_at', '=', 'latest_completed.latest_completed_at');
            })
            ->where('quiz_attempts.user_id', $user->id)
            ->selectRaw('MAX(quiz_attempts.id)')
            ->groupBy('quiz_attempts.quiz_id');
        $attempts = QuizAttempt::query()
            ->with(['quiz.material'])
            ->where('user_id', $user->id)
            ->whereIn('id', $latestAttemptIds)
            ->orderByDesc('completed_at')
            ->orderByDesc('id')
            ->paginate(10);

        return view('profile.history.learning', compact('user', 'attempts'));
    }

    public function events(): View
    {
        $user = request()->user()->load('profile');
        $registrations = EventRegistration::query()
            ->with('event')
            ->where('user_id', $user->id)
            ->orderByDesc('registered_at')
            ->paginate(10);

        return view('profile.history.events', compact('user', 'registrations'));
    }
}
