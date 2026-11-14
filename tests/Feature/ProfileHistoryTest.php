<?php

namespace Tests\Feature;

use App\Enums\EventCategory;
use App\Enums\EventPlatform;
use App\Enums\EventStatus;
use App\Enums\RegistrationStatus;
use App\Models\CareerEvent;
use App\Models\LearningMaterial;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileHistoryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_user_only_sees_their_own_learning_and_event_history(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $material = LearningMaterial::factory()->create(['title' => 'Materi Milik User']);
        $quiz = Quiz::factory()->create(['material_id' => $material->id, 'title' => 'Kuis Milik User']);
        QuizAttempt::create([
            'user_id' => $user->id,
            'quiz_id' => $quiz->id,
            'score' => 85,
            'correct_count' => 4,
            'incorrect_count' => 1,
            'started_at' => now()->subHour(),
            'completed_at' => now()->subMinutes(30),
        ]);
        QuizAttempt::create([
            'user_id' => $otherUser->id,
            'quiz_id' => $quiz->id,
            'score' => 20,
            'correct_count' => 1,
            'incorrect_count' => 4,
            'started_at' => now()->subHour(),
            'completed_at' => now()->subMinutes(20),
        ]);

        $event = $this->event('Kegiatan User');
        $event->registrations()->create([
            'user_id' => $user->id,
            'status' => RegistrationStatus::Registered,
            'registered_at' => now(),
        ]);
        $otherEvent = $this->event('Kegiatan User Lain');
        $otherEvent->registrations()->create([
            'user_id' => $otherUser->id,
            'status' => RegistrationStatus::Registered,
            'registered_at' => now(),
        ]);

        $this->actingAs($user)->get(route('profile.history.elearning'))
            ->assertOk()
            ->assertSee('Kuis Milik User')
            ->assertDontSee('class="badge text-bg-danger">20</span>', false);
        $this->get(route('profile.history.events'))
            ->assertOk()
            ->assertSee('Kegiatan User')
            ->assertDontSee('Kegiatan User Lain');
    }

    public function test_empty_states_are_shown_on_both_history_pages(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('profile.history.elearning'))
            ->assertOk()
            ->assertSee('bi-journal-x')
            ->assertSee('Belum ada riwayat aktivitas')
            ->assertSee(route('materials.index'));

        $this->get(route('profile.history.events'))
            ->assertOk()
            ->assertSee('bi-calendar2-x')
            ->assertSee('Belum ada riwayat aktivitas')
            ->assertSee(route('events.index'));
    }

    public function test_cancelled_event_registration_does_not_show_meeting_link(): void
    {
        $user = User::factory()->create();
        $event = $this->event('Kegiatan Dibatalkan');
        $event->registrations()->create([
            'user_id' => $user->id,
            'status' => RegistrationStatus::Cancelled,
            'registered_at' => now(),
        ]);

        $this->actingAs($user)->get(route('profile.history.events'))
            ->assertOk()
            ->assertSee('Kegiatan Dibatalkan')
            ->assertSee('Dibatalkan')
            ->assertDontSee('Buka Link');
    }

    public function test_latest_attempt_per_quiz_and_score_badges_are_displayed(): void
    {
        $user = User::factory()->create();
        $quiz = Quiz::factory()->create(['title' => 'Kuis Berulang']);
        foreach ([40, 60, 90] as $index => $score) {
            QuizAttempt::create([
                'user_id' => $user->id,
                'quiz_id' => $quiz->id,
                'score' => $score,
                'correct_count' => $index + 1,
                'incorrect_count' => 5 - ($index + 1),
                'started_at' => now()->subDays(3 - $index),
                'completed_at' => now()->subDays(3 - $index),
            ]);
        }

        $this->actingAs($user)->get(route('profile.history.elearning'))
            ->assertOk()
            ->assertSee('Kuis Berulang')
            ->assertSee('class="badge text-bg-success">90</span>', false)
            ->assertDontSee('class="badge text-bg-danger">40</span>', false)
            ->assertDontSee('class="badge text-bg-warning">60</span>', false);
    }

    public function test_both_history_routes_require_user_role(): void
    {
        foreach ([
            [User::factory()->admin()->create(), 403],
            [User::factory()->superAdmin()->create(), 403],
        ] as [$staff, $status]) {
            $this->actingAs($staff)->get(route('profile.history.elearning'))->assertStatus($status);
            $this->get(route('profile.history.events'))->assertStatus($status);
        }

        auth()->logout();
        $this->get(route('profile.history.elearning'))->assertRedirect(route('login'));
        $this->get(route('profile.history.events'))->assertRedirect(route('login'));
    }

    public function test_both_history_routes_are_accessible_to_users_and_highlight_current_menu(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('profile.history.elearning'))
            ->assertOk()
            ->assertSee('Aktivitas E-Learning')
            ->assertSee('class="nav-link active" href="'.route('profile.history.elearning').'"', false);
        $this->get(route('profile.history.events'))
            ->assertOk()
            ->assertSee('Aktivitas Kegiatan')
            ->assertSee('class="nav-link active" href="'.route('profile.history.events').'"', false);
    }

    private function event(string $title): CareerEvent
    {
        return CareerEvent::factory()->create([
            'title' => $title,
            'start_at' => now()->addDays(3),
            'end_at' => now()->addDays(3)->addHours(2),
            'status' => EventStatus::Published,
            'category' => EventCategory::Hardskill,
            'platform' => EventPlatform::Zoom,
        ]);
    }
}
