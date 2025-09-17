<?php

declare(strict_types=1);

namespace App\Providers;

use BezhanSalleh\PanelSwitch\PanelSwitch;
use Carbon\Carbon;
use Filament\Notifications\Livewire\DatabaseNotifications;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        DatabaseNotifications::trigger('notifications.database-notifications-trigger');
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Carbon::macro('inApplicationTimezone', function () {
            return $this->tz(config('app.timezone_display'));
        });
        Carbon::macro('inUserTimezone', function () {
            return $this->tz(auth()->user()?->timezone ?? config('app.timezone_display'));
        });
        DatabaseNotifications::trigger('notifications.database-notifications-trigger');
        PanelSwitch::configureUsing(function (PanelSwitch $panelSwitch) {
            $panelSwitch
                ->visible(fn (): bool => auth()->user()?->hasAnyRole([
                    'super_admin',
                    'Sales Development Rep',
                ]))
                ->labels([
                    'admin' => 'Standard View',
                    'development' => 'Development Rep View',
                ])
                ->simple();
        });
    }
}
