<?php

namespace App\Policies;

use App\Models\Reminder;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ReminderPolicy
{
    use HandlesAuthorization;

    public function view(User $user, Reminder $reminder): bool
    {
        return $reminder->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Reminder $reminder): bool
    {
        return $reminder->user_id === $user->id || $user->hasRole('Sales Development Rep');
    }

    public function delete(User $user, Reminder $reminder): bool
    {
        return $reminder->user_id === $user->id;
    }
}
