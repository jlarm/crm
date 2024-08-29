<?php

namespace App\Policies;

use App\Models\DealerEmail;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class DealerEmailPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('viewAny');
    }

    public function view(User $user, DealerEmail $dealerEmail): bool
    {
        return $user->can('view');
    }

    public function create(User $user): bool
    {
        return $user->can('create');
    }

    public function update(User $user, DealerEmail $dealerEmail): bool
    {
        return $user->can('update');
    }

    public function delete(User $user, DealerEmail $dealerEmail): bool
    {
        return $user->can('delete');
    }
}
