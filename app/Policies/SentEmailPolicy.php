<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\SentEmail;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SentEmailPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasRole('super_admin');
    }

    public function view(User $user, SentEmail $sentEmail): bool
    {
        return $user->hasRole('super_admin');
    }

    public function create(User $user): bool
    {
        return $user->hasRole('super_admin');
    }

    public function update(User $user, SentEmail $sentEmail): bool
    {
        return $user->hasRole('super_admin');
    }

    public function delete(User $user, SentEmail $sentEmail): bool
    {
        return $user->hasRole('super_admin');
    }

    public function restore(User $user, SentEmail $sentEmail): bool
    {
        return $user->hasRole('super_admin');
    }

    public function forceDelete(User $user, SentEmail $sentEmail): bool
    {
        return $user->hasRole('super_admin');
    }
}
