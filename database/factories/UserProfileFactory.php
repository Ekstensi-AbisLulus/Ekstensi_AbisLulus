<?php

namespace Database\Factories;

use App\Enums\EducationLevel;
use App\Enums\Gender;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UserProfile>
 */
class UserProfileFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'phone' => '08'.fake()->numerify('##########'),
            'education_level' => fake()->randomElement(EducationLevel::cases()),
            'institution' => fake()->randomElement(['SMKN 1 Bandung', 'SMAN 3 Bandung', 'Telkom University', 'Universitas Padjadjaran']),
            'graduation_year' => fake()->numberBetween(2022, 2026),
            'bio' => fake()->sentence(12),
            'birth_date' => fake()->dateTimeBetween('-24 years', '-17 years')->format('Y-m-d'),
            'gender' => fake()->randomElement(Gender::cases()),
            'address' => fake()->city(),
            'linkedin_url' => null,
            'avatar_path' => null,
        ];
    }
}
