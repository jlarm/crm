<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\Failed;
use Spatie\Activitylog\Facades\LogBatch;
use Spatie\Activitylog\ActivityLogger;
use Spatie\Activitylog\ActivityLogStatus;

class LogAuthenticationEvents
{
    /**
     * Log successful login attempts.
     */
    public function handleLogin(Login $event): void
    {
        $user = $event->user;
        
        activity()
            ->causedBy($user)
            ->withProperties([
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ])
            ->log('Logged in');
    }

    /**
     * Log logout events.
     */
    public function handleLogout(Logout $event): void
    {
        if ($event->user) {
            activity()
                ->causedBy($event->user)
                ->withProperties([
                    'ip' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ])
                ->log('Logged out');
        }
    }

    /**
     * Log failed login attempts.
     */
    public function handleFailed(Failed $event): void
    {
        activity()
            ->withProperties([
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'credentials' => [
                    'email' => $event->credentials['email'] ?? null,
                ]
            ])
            ->log('Failed login attempt');
    }
}
