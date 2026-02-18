<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\FileUpload;
use App\Models\User;

final class FileUploadPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('manage files');
    }

    public function view(User $user, FileUpload $fileUpload): bool
    {
        return $user->can('manage files');
    }

    public function create(User $user): bool
    {
        return $user->can('manage files');
    }

    public function update(User $user, FileUpload $fileUpload): bool
    {
        return $user->can('manage files');
    }

    public function delete(User $user, FileUpload $fileUpload): bool
    {
        return $user->can('manage files');
    }
}