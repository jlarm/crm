<?php

declare(strict_types=1);

namespace App\Providers;

use BezhanSalleh\PanelSwitch\PanelSwitch;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Filament\Notifications\Livewire\DatabaseNotifications;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

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
        $this->configureDefaults();

        Carbon::macro('inApplicationTimezone', fn () => $this->tz(config('app.timezone_display')));
        Carbon::macro('inUserTimezone', fn () => $this->tz(auth()->user()?->timezone ?? config('app.timezone_display')));
        DatabaseNotifications::trigger('notifications.database-notifications-trigger');
        PanelSwitch::configureUsing(function (PanelSwitch $panelSwitch): void {
            $panelSwitch
                ->labels([
                    'admin' => 'Standard View',
                    'development' => 'Development Rep View',
                ])
                ->simple();
        });
    }

    protected function configureDefaults(): void
    {
        Model::preventLazyLoading(! app()->isProduction());

        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
