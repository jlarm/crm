<?php

declare(strict_types=1);

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Validation\Rules\Password;

describe('Carbon macros registered by AppServiceProvider', function (): void {
    it('inApplicationTimezone returns the configured display timezone', function (): void {
        config(['app.timezone_display' => 'America/New_York']);

        $instance = Carbon::parse('2025-01-01 12:00:00', 'UTC')->inApplicationTimezone();

        expect($instance->timezoneName)->toBe('America/New_York');
    });

    it('inApplicationTimezone falls back to UTC when no display timezone is configured', function (): void {
        config(['app.timezone_display' => null]);

        $instance = Carbon::parse('2025-01-01 12:00:00', 'UTC')->inApplicationTimezone();

        expect($instance->timezoneName)->toBe('UTC');
    });

    it('inUserTimezone uses the authenticated user timezone when set', function (): void {
        $user = User::factory()->create(['timezone' => 'America/Chicago']);
        $this->actingAs($user);

        $instance = Carbon::parse('2025-01-01 12:00:00', 'UTC')->inUserTimezone();

        expect($instance->timezoneName)->toBe('America/Chicago');
    });

    it('inUserTimezone falls back to the configured display timezone when no user is authenticated', function (): void {
        config(['app.timezone_display' => 'Europe/Berlin']);

        $instance = Carbon::parse('2025-01-01 12:00:00', 'UTC')->inUserTimezone();

        expect($instance->timezoneName)->toBe('Europe/Berlin');
    });

    it('inUserTimezone falls back to UTC when nothing is configured', function (): void {
        config(['app.timezone_display' => null]);

        $instance = Carbon::parse('2025-01-01 12:00:00', 'UTC')->inUserTimezone();

        expect($instance->timezoneName)->toBe('UTC');
    });
});

describe('Password::defaults configured by AppServiceProvider', function (): void {
    it('returns a strong password rule when re-bootstrapped in production', function (): void {
        $original = $this->app['env'];
        $this->app['env'] = 'production';

        try {
            (new App\Providers\AppServiceProvider($this->app))->boot();

            $rule = Password::default();

            expect($rule)->toBeInstanceOf(Password::class);

            // Now run validation against a too-weak password to prove the
            // strong-password constraints from the production closure
            // (mixedCase, letters, numbers, symbols, min:12) are active.
            $validator = validator()->make(
                ['password' => 'short'],
                ['password' => Password::default()]
            );

            expect($validator->fails())->toBeTrue()
                ->and($validator->errors()->first('password'))->toBeString();
        } finally {
            $this->app['env'] = $original;
            (new App\Providers\AppServiceProvider($this->app))->boot();
        }
    });

    it('returns a default rule outside of production', function (): void {
        $original = $this->app['env'];
        $this->app['env'] = 'testing';

        try {
            (new App\Providers\AppServiceProvider($this->app))->boot();

            $rule = Password::default();

            // The non-production callback returns null, so Password::default()
            // hands back a vanilla Password instance.
            expect($rule)->toBeInstanceOf(Password::class);
        } finally {
            $this->app['env'] = $original;
            (new App\Providers\AppServiceProvider($this->app))->boot();
        }
    });
});
