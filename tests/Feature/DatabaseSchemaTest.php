<?php

namespace Tests\Feature;

use App\Enums\RegistrationStatus;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\Article;
use App\Models\CareerEvent;
use App\Models\HeroSection;
use App\Models\LearningMaterial;
use App\Models\LearningReview;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\QuizAnswer;
use App\Models\QuizAttempt;
use App\Models\Testimonial;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_creates_accounts_and_content_per_prd(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertSame(1, User::where('role', UserRole::SuperAdmin)->count());
        $this->assertSame(2, User::where('role', UserRole::Admin)->count());
        $this->assertSame(5, User::where('role', UserRole::User)->count());
        $this->assertSame(0, User::doesntHave('profile')->count());

        $superAdmin = User::where('email', 'superadmin@abislulus.test')->firstOrFail();
        $this->assertTrue($superAdmin->isSuperAdmin());
        $this->assertTrue(password_verify('password', $superAdmin->password));

        $this->assertDatabaseCount('learning_materials', 6);
        $this->assertDatabaseCount('quizzes', 2);
        $this->assertDatabaseCount('questions', 10);
        $this->assertDatabaseCount('answer_options', 40);
        $this->assertDatabaseCount('career_events', 4);
        $this->assertDatabaseCount('articles', 3);
        $this->assertSame(1, HeroSection::active()->count());
        $this->assertGreaterThan(0, LearningReview::count());
        $this->assertGreaterThan(0, Testimonial::count());

        foreach (Question::withCount(['options as correct_count' => fn ($q) => $q->where('is_correct', true)])->get() as $question) {
            $this->assertSame(1, $question->correct_count, 'Setiap soal harus punya tepat 1 opsi benar');
        }
    }

    public function test_new_user_defaults_to_user_role_and_active_status(): void
    {
        $user = User::create(['name' => 'Baru', 'email' => 'baru@example.com', 'password' => 'password'])->fresh();

        $this->assertSame(UserRole::User, $user->role);
        $this->assertSame(UserStatus::Active, $user->status);
    }

    public function test_material_average_rating_and_reviews_count(): void
    {
        $material = LearningMaterial::factory()->create();
        $this->assertNull($material->average_rating);
        $this->assertSame(0, $material->reviews_count);

        foreach ([5, 4, 4] as $rating) {
            LearningReview::factory()->for($material, 'material')->create(['rating' => $rating]);
        }

        $listed = LearningMaterial::withAvg('reviews', 'rating')->withCount('reviews')->find($material->id);
        $this->assertSame(4.3, $listed->average_rating);
        $this->assertSame(3, $listed->reviews_count);
        $this->assertSame(4.3, $material->fresh()->average_rating);
    }

    public function test_review_is_unique_per_user_and_material(): void
    {
        $review = LearningReview::factory()->create();

        $this->expectException(QueryException::class);

        LearningReview::factory()->create([
            'user_id' => $review->user_id,
            'material_id' => $review->material_id,
        ]);
    }

    public function test_calculate_score_counts_unanswered_questions_as_incorrect(): void
    {
        $quiz = Quiz::factory()->withQuestions(5, 4)->create();
        $user = User::factory()->create();
        $attempt = QuizAttempt::create(['user_id' => $user->id, 'quiz_id' => $quiz->id, 'started_at' => now()]);

        // 2 benar, 1 salah, 2 tidak dijawab.
        foreach ($quiz->questions()->with('options')->get()->take(3) as $i => $question) {
            $option = $question->options->firstWhere('is_correct', $i < 2);
            QuizAnswer::create([
                'attempt_id' => $attempt->id,
                'question_id' => $question->id,
                'selected_option_id' => $option->id,
                'is_correct' => $option->is_correct,
                'answered_at' => now(),
            ]);
        }

        $attempt->calculateScore()->refresh();

        $this->assertSame(2, $attempt->correct_count);
        $this->assertSame(3, $attempt->incorrect_count);
        $this->assertSame('40.00', $attempt->score);
        $this->assertNotNull($attempt->completed_at);
    }

    public function test_remaining_seats_ignores_cancelled_registrations(): void
    {
        $event = CareerEvent::factory()->create(['capacity' => 3]);
        $users = User::factory()->count(3)->create();

        $event->registrations()->create(['user_id' => $users[0]->id, 'status' => RegistrationStatus::Registered, 'registered_at' => now()]);
        $event->registrations()->create(['user_id' => $users[1]->id, 'status' => RegistrationStatus::Cancelled, 'registered_at' => now()]);

        $this->assertSame(2, $event->remaining_seats);
        $this->assertNull(CareerEvent::factory()->create(['capacity' => null])->remaining_seats);
        $this->assertTrue($users[0]->registeredEvents->contains($event));
    }

    public function test_deleting_material_cascades_reviews_and_nulls_quiz_material(): void
    {
        $material = LearningMaterial::factory()->create();
        $review = LearningReview::factory()->for($material, 'material')->create();
        $quiz = Quiz::factory()->create(['material_id' => $material->id]);

        $material->delete();

        $this->assertModelMissing($review);
        $this->assertNull($quiz->fresh()->material_id);
    }

    public function test_deleting_quiz_cascades_questions_options_and_attempts(): void
    {
        $quiz = Quiz::factory()->withQuestions(2, 2)->create();
        QuizAttempt::create(['user_id' => User::factory()->create()->id, 'quiz_id' => $quiz->id, 'started_at' => now()]);

        $quiz->delete();

        $this->assertDatabaseCount('questions', 0);
        $this->assertDatabaseCount('answer_options', 0);
        $this->assertDatabaseCount('quiz_attempts', 0);
    }

    public function test_article_slug_is_generated_and_unique(): void
    {
        $first = Article::factory()->create(['title' => 'Tips Karier Pertama']);
        $second = Article::factory()->create(['title' => 'Tips Karier Pertama']);

        $this->assertSame('tips-karier-pertama', $first->slug);
        $this->assertSame('tips-karier-pertama-2', $second->slug);
        $this->assertNotNull($first->published_at);
    }
}
