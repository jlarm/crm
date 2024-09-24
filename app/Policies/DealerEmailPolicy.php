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
        return true;
    }

    public function view(User $user, DealerEmail $dealerEmail): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, DealerEmail $dealerEmail): bool
    {
        return true;
    }

    public function delete(User $user, DealerEmail $dealerEmail): bool
    {
        return true;
    }
}
