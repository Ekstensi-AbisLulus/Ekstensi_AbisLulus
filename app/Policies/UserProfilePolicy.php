<?php

namespace App\Policies;

use App\Models\User;
use App\Models\UserProfile;

class UserProfilePolicy
{
    /**
     * Profil hanya boleh dilihat/diubah oleh pemiliknya sendiri (NFR-02).
     */
    public function update(User $user, UserProfile $profile): bool
    {
        return $profile->user_id === $user->id;
    }
}
