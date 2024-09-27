<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use App\Models\Contact;
use App\Models\DealerEmail;
use App\Models\DealerEmailTemplate;
use App\Models\Reminder;
use App\Models\SentEmail;
use App\Models\User;
use App\Policies\ActivityPolicy;
use App\Policies\ContactPolicy;
use App\Policies\DealerEmailPolicy;
use App\Policies\DealerEmailTemplatePolicy;
use App\Policies\ReminderPolicy;
use App\Policies\SentEmailPolicy;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Spatie\Activitylog\Models\Activity;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Activity::class => ActivityPolicy::class,
        'Spatie\Permission\Models\Role' => 'App\Policies\RolePolicy',
        User::class => UserPolicy::class,
        Contact::class => ContactPolicy::class,
        Reminder::class => ReminderPolicy::class,
        DealerEmail::class => DealerEmailPolicy::class,
        DealerEmailTemplate::class => DealerEmailTemplatePolicy::class,
        SentEmail::class => SentEmailPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        Gate::define('viewPulse', function (User $user) {
            return $user->hasRole('super_admin');
        });
    }
}
