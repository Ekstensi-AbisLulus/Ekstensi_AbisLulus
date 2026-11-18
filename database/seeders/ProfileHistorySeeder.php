<?php

namespace Database\Seeders;

use App\Enums\RegistrationStatus;
use App\Enums\UserRole;
use App\Models\CareerEvent;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProfileHistorySeeder extends Seeder
{
    public function run(): void
    {
        if (app()->isProduction()) {
            return;
        }

        $user = User::query()->where('role', UserRole::User)->orderBy('id')->first();
        if (! $user) {
            return;
        }

        $quiz = Quiz::query()->orderBy('id')->first();
        if ($quiz && ! $quiz->attempts()->where('user_id', $user->id)->exists()) {
            QuizAttempt::create([
                'user_id' => $user->id,
                'quiz_id' => $quiz->id,
                'score' => 80,
                'correct_count' => 4,
                'incorrect_count' => 1,
                'started_at' => now()->subMinutes(15),
                'completed_at' => now()->subMinutes(5),
            ]);
        }

        $event = CareerEvent::query()->orderBy('id')->first();
        if ($event) {
            $event->registrations()->firstOrCreate(
                ['user_id' => $user->id],
                [
                    'status' => RegistrationStatus::Registered,
                    'registered_at' => now()->subDays(2),
                ],
            );
        }
    }
}
