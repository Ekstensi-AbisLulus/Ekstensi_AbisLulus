<?php

namespace Database\Factories;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'role' => UserRole::User,
            'status' => UserStatus::Active,
            'remember_token' => Str::random(10),
        ];
    }

    public function admin(): static
    {
        return $this->state(fn () => ['role' => UserRole::Admin]);
    }

    public function superAdmin(): static
    {
        return $this->state(fn () => ['role' => UserRole::SuperAdmin]);
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['status' => UserStatus::Inactive]);
    }

    /**
     * Buat baris user_profiles kosong/terisi untuk user ini.
     */
    public function withProfile(array $attributes = []): static
    {
        return $this->has(UserProfile::factory()->state($attributes), 'profile');
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
