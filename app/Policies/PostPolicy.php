<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Post;
use App\Models\User;

final class PostPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('write blog');
    }

    public function view(User $user, Post $post): bool
    {
        return $user->can('write blog');
    }

    public function create(User $user): bool
    {
        return $user->can('write blog');
    }

    public function update(User $user, Post $post): bool
    {
        return $user->can('write blog');
    }

    public function delete(User $user, Post $post): bool
    {
        return $user->can('write blog');
    }
}