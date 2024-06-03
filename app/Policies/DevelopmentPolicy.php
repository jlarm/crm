<?php

namespace App\Policies;

use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Auth\Access\HandlesAuthorization;

class DevelopmentPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        if (Filament::getCurrentPanel()->getId() === 'development') {
            return $user->hasAnyRole([
                'Sales Development Rep',
            ]);
        }

        return false;
    }
}
