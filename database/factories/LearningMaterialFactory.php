<?php

namespace Database\Factories;

use App\Models\LearningMaterial;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LearningMaterial>
 */
class LearningMaterialFactory extends Factory
{
    public function definition(): array
    {
        return [
            'admin_id' => User::factory()->admin(),
            'title' => rtrim(fake()->sentence(4), '.'),
            'description' => fake()->paragraph(3),
            'video_url' => 'https://www.youtube.com/watch?v='.fake()->regexify('[A-Za-z0-9_-]{11}'),
            'thumbnail_url' => 'https://picsum.photos/seed/'.fake()->unique()->word().'/640/360',
            'is_published' => true,
        ];
    }

    public function unpublished(): static
    {
        return $this->state(fn () => ['is_published' => false]);
    }
}
