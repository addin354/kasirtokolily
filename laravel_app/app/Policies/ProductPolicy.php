<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;

class ProductPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isOwner();
    }

    /**
     * Katalog baca saja — role pelanggan (route /katalog).
     */
    public function viewCatalog(User $user): bool
    {
        return $user->isPelanggan();
    }

    public function view(User $user, Product $product): bool
    {
        return $user->isAdmin() || $user->isOwner();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isOwner();
    }

    public function update(User $user, Product $product): bool
    {
        return $user->isAdmin() || $user->isOwner();
    }

    public function delete(User $user, Product $product): bool
    {
        return $user->isAdmin() || $user->isOwner();
    }
}
