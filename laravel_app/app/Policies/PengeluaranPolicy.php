<?php

namespace App\Policies;

use App\Models\Pengeluaran;
use App\Models\User;

class PengeluaranPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isOwner();
    }

    public function view(User $user, Pengeluaran $pengeluaran): bool
    {
        return $user->isAdmin() || $user->isOwner();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isOwner();
    }

    public function update(User $user, Pengeluaran $pengeluaran): bool
    {
        return $user->isAdmin() || $user->isOwner();
    }

    public function delete(User $user, Pengeluaran $pengeluaran): bool
    {
        return $user->isAdmin() || $user->isOwner();
    }
}
