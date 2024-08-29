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
        return $user->can('view dealer-emails');
//        return true;
    }

    public function view(User $user, DealerEmail $dealerEmail): bool
    {
    }

    public function create(User $user): bool
    {
    }

    public function update(User $user, DealerEmail $dealerEmail): bool
    {
    }

    public function delete(User $user, DealerEmail $dealerEmail): bool
    {
    }

    public function restore(User $user, DealerEmail $dealerEmail): bool
    {
    }

    public function forceDelete(User $user, DealerEmail $dealerEmail): bool
    {
    }
}
