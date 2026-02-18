<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Page;
use App\Models\User;

final class PagePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('manage pages');
    }

    public function view(User $user, Page $page): bool
    {
        return $user->can('manage pages');
    }

    public function create(User $user): bool
    {
        return $user->can('manage pages');
    }

    public function update(User $user, Page $page): bool
    {
        return $user->can('manage pages');
    }

    public function delete(User $user, Page $page): bool
    {
        return $user->can('manage pages');
    }
}