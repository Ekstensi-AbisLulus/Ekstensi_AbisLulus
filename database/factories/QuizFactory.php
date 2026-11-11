<?php

namespace Database\Factories;

use App\Models\Question;
use App\Models\Quiz;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Quiz>
 */
class QuizFactory extends Factory
{
    public function definition(): array
    {
        return [
            'material_id' => null,
            'created_by' => User::factory()->admin(),
            'title' => 'Kuis '.rtrim(fake()->sentence(3), '.'),
            'description' => fake()->sentence(),
        ];
    }

    /**
     * Tambah $questions soal, masing-masing $options opsi dengan tepat 1 opsi benar (opsi pertama).
     */
    public function withQuestions(int $questions = 5, int $options = 4): static
    {
        return $this->afterCreating(function (Quiz $quiz) use ($questions, $options) {
            for ($q = 1; $q <= $questions; $q++) {
                /** @var Question $question */
                $question = $quiz->questions()->create([
                    'question_text' => rtrim(fake()->sentence(8), '.').'?',
                    'sort_order' => $q,
                ]);

                for ($o = 1; $o <= $options; $o++) {
                    $question->options()->create([
                        'option_text' => rtrim(fake()->sentence(3), '.'),
                        'is_correct' => $o === 1,
                        'sort_order' => $o,
                    ]);
                }
            }
        });
    }
}
