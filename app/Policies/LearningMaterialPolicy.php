<?php

namespace App\Policies;

use App\Models\LearningMaterial;
use App\Models\User;

class LearningMaterialPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isSuperAdmin();
    }

    public function view(User $user, LearningMaterial $material): bool
    {
        return $user->isAdmin() || $user->isSuperAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isSuperAdmin();
    }

    public function update(User $user, LearningMaterial $material): bool
    {
        return $user->isSuperAdmin() || ($user->isAdmin() && $material->admin_id === $user->id);
    }

    public function delete(User $user, LearningMaterial $material): bool
    {
        return $user->isSuperAdmin() || ($user->isAdmin() && $material->admin_id === $user->id);
    }
}
