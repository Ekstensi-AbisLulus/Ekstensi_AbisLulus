<?php

namespace Database\Factories;

use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Testimonial>
 */
class TestimonialFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'content' => fake()->sentence(20),
            'rating' => fake()->numberBetween(4, 5),
            'is_visible' => true,
        ];
    }
}
