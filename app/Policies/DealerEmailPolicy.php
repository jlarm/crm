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
        return $user->hasRole('super_admin');
    }

    public function view(User $user, DealerEmail $dealerEmail): bool
    {
        return $user->hasRole('super_admin');
    }

    public function create(User $user): bool
    {
        return $user->can('create_dealer::email',);
    }

    public function update(User $user, DealerEmail $dealerEmail): bool
    {
        return $user->id === $dealerEmail->user_id;
    }

    public function delete(User $user, DealerEmail $dealerEmail): bool
    {
        return $user->id === $dealerEmail->user_id;
    }
}
