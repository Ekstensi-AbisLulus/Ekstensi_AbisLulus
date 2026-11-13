<?php

namespace Database\Factories;

use App\Enums\ArticleStatus;
use App\Models\Article;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Article>
 */
class ArticleFactory extends Factory
{
    public function definition(): array
    {
        return [
            'super_admin_id' => User::factory()->superAdmin(),
            'title' => rtrim(fake()->sentence(6), '.'),
            'excerpt' => fake()->sentence(15),
            'content' => implode("\n\n", fake()->paragraphs(4)),
            'cover_image_url' => '/images/placeholders/article-cover.svg',
            'status' => ArticleStatus::Published,
        ];
    }

    public function draft(): static
    {
        return $this->state(fn () => ['status' => ArticleStatus::Draft, 'published_at' => null]);
    }
}
