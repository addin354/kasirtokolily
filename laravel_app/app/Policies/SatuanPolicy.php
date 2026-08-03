<?php

namespace App\Policies;

use App\Models\Satuan;
use App\Models\User;

class SatuanPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isOwner();
    }

    public function view(User $user, Satuan $satuan): bool
    {
        return $user->isAdmin() || $user->isOwner();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isOwner();
    }

    public function update(User $user, Satuan $satuan): bool
    {
        return $user->isAdmin() || $user->isOwner();
    }

    public function delete(User $user, Satuan $satuan): bool
    {
        return $user->isAdmin() || $user->isOwner();
    }
}
