<?php

namespace Database\Factories;

use App\Enums\EventCategory;
use App\Enums\EventPlatform;
use App\Enums\EventStatus;
use App\Models\CareerEvent;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CareerEvent>
 */
class CareerEventFactory extends Factory
{
    public function definition(): array
    {
        $start = fake()->dateTimeBetween('+3 days', '+30 days')->setTime(fake()->numberBetween(9, 19), 0);

        return [
            'admin_id' => User::factory()->admin(),
            'title' => 'Webinar '.rtrim(fake()->sentence(3), '.'),
            'description' => fake()->paragraph(3),
            'start_at' => $start,
            'end_at' => (clone $start)->modify('+2 hours'),
            'meeting_url' => 'https://zoom.us/j/'.fake()->numerify('###########'),
            'platform' => EventPlatform::Zoom,
            'capacity' => 100,
            'status' => EventStatus::Published,
            'category' => fake()->randomElement(EventCategory::cases()),
            'thumbnail_url' => 'https://picsum.photos/seed/'.fake()->unique()->word().'/640/360',
        ];
    }

    public function draft(): static
    {
        return $this->state(fn () => ['status' => EventStatus::Draft]);
    }
}
