<?php

namespace Database\Factories;

use App\Models\LearningMaterial;
use App\Models\LearningReview;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LearningReview>
 */
class LearningReviewFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'material_id' => LearningMaterial::factory(),
            'rating' => fake()->numberBetween(1, 5),
            'review_text' => fake()->optional(0.8)->sentence(12),
        ];
    }
}
