<?php

namespace App\Policies;

use App\Models\HeroSection;
use App\Models\User;

class HeroSectionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    public function view(User $user, HeroSection $heroSection): bool
    {
        return $user->isSuperAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    public function update(User $user, HeroSection $heroSection): bool
    {
        return $user->isSuperAdmin();
    }

    public function delete(User $user, HeroSection $heroSection): bool
    {
        return $user->isSuperAdmin();
    }
}
