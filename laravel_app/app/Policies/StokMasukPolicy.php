<?php

namespace App\Policies;

use App\Models\User;

class StokMasukPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isOwner() || $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isOwner() || $user->isAdmin();
    }
}
