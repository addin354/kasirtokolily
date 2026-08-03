<?php

namespace App\Policies;

use App\Models\Category;
use App\Models\User;

class CategoryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isOwner();
    }

    public function view(User $user, Category $category): bool
    {
        return $user->isAdmin() || $user->isOwner();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isOwner();
    }

    public function update(User $user, Category $category): bool
    {
        return $user->isAdmin() || $user->isOwner();
    }

    public function delete(User $user, Category $category): bool
    {
        return $user->isAdmin() || $user->isOwner();
    }
}
