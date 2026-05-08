<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Support\Carbon;

it('records last_login_at on the first authenticated request', function (): void {
    $user = User::factory()->create(['last_login_at' => null]);

    $this->actingAs($user)->get('/dashboard')->assertOk();

    expect($user->fresh()->last_login_at)->not->toBeNull();
});

it('does not update last_login_at on subsequent requests in the same session', function (): void {
    $user = User::factory()->create(['last_login_at' => null]);

    $this->actingAs($user)->get('/dashboard')->assertOk();

    $firstLogin = $user->fresh()->last_login_at;
    expect($firstLogin)->not->toBeNull();

    Carbon::setTestNow(Carbon::now()->addMinutes(5));

    $this->get('/dashboard')->assertOk();

    expect($user->fresh()->last_login_at?->equalTo($firstLogin))->toBeTrue();

    Carbon::setTestNow();
});

it('does not record last_login_at for guest requests', function (): void {
    User::factory()->create(['last_login_at' => null]);

    $this->get('/dashboard')->assertRedirect('/login');

    expect(User::query()->whereNotNull('last_login_at')->exists())->toBeFalse();
});
