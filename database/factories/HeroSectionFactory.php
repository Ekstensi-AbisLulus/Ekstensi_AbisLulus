<?php

namespace Database\Factories;

use App\Models\HeroSection;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<HeroSection>
 */
class HeroSectionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'super_admin_id' => User::factory()->superAdmin(),
            'title' => rtrim(fake()->sentence(5), '.'),
            'subtitle' => fake()->sentence(12),
            'image_url' => 'https://picsum.photos/seed/'.fake()->unique()->word().'/1200/600',
            'target_url' => null,
            'is_active' => true,
        ];
    }
}
