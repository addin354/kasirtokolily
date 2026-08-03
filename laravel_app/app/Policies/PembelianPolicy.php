<?php

namespace App\Policies;

use App\Models\Pembelian;
use App\Models\User;

class PembelianPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isOwner();
    }

    public function view(User $user, Pembelian $pembelian): bool
    {
        return $user->isAdmin() || $user->isOwner();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isOwner();
    }

    public function update(User $user, Pembelian $pembelian): bool
    {
        return $user->isAdmin() || $user->isOwner();
    }

    public function delete(User $user, Pembelian $pembelian): bool
    {
        return $user->isAdmin() || $user->isOwner();
    }
}
