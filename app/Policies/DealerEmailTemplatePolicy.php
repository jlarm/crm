<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\DealerEmailTemplate;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class DealerEmailTemplatePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasRole('super_admin');
    }

    public function view(User $user, DealerEmailTemplate $dealerEmailTemplate): bool
    {
        return $user->hasRole('super_admin');
    }

    public function create(User $user): bool
    {
        return $user->hasRole('super_admin');
    }

    public function update(User $user, DealerEmailTemplate $dealerEmailTemplate): bool
    {
        return $user->hasRole('super_admin');
    }

    public function delete(User $user, DealerEmailTemplate $dealerEmailTemplate): bool
    {
        return $user->hasRole('super_admin');
    }

    public function restore(User $user, DealerEmailTemplate $dealerEmailTemplate): bool
    {
        return $user->hasRole('super_admin');
    }

    public function forceDelete(User $user, DealerEmailTemplate $dealerEmailTemplate): bool
    {
        return $user->hasRole('super_admin');
    }
}
