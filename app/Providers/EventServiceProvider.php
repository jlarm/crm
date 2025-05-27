<?php

namespace App\Providers;

use App\Models\Contact;
use App\Models\DealerEmail;
use App\Models\DealerEmailTemplate;
use App\Events\ContactTagSync;
use App\Listeners\SyncContactTagsWithMailcoach;
use App\Observers\ContactObserver;
use App\Observers\DealerEmailObserver;
use App\Observers\DealerEmailTemplateObserver;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\Failed;
use App\Listeners\LogAuthenticationEvents;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        Login::class => [
            [LogAuthenticationEvents::class, 'handleLogin'],
        ],
        Logout::class => [
            [LogAuthenticationEvents::class, 'handleLogout'],
        ],
        Failed::class => [
            [LogAuthenticationEvents::class, 'handleFailed'],
        ],
        ContactTagSync::class => [
            SyncContactTagsWithMailcoach::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        Contact::observe(ContactObserver::class);
        DealerEmail::observe(DealerEmailObserver::class);
        DealerEmailTemplate::observe(DealerEmailTemplateObserver::class);
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
