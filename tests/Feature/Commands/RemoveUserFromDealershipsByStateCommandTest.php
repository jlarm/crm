<?php

declare(strict_types=1);

use App\Models\Dealership;
use App\Models\User;

describe('user:remove-dealerships', function (): void {
    it('errors when the user is not found', function (): void {
        $this->artisan('user:remove-dealerships', [
            'userId' => 9999,
            'states' => ['CA'],
        ])
            ->expectsOutput('User not found')
            ->assertExitCode(0);
    });

    it('errors when no dealerships match the given states', function (): void {
        $user = User::factory()->create();

        Dealership::factory()->create(['state' => 'TX']);

        $this->artisan('user:remove-dealerships', [
            'userId' => $user->id,
            'states' => ['CA'],
        ])
            ->expectsOutput('No dealerships found for the given states')
            ->assertExitCode(0);
    });

    it('detaches the user from dealerships in the matching states', function (): void {
        $user = User::factory()->create();
        $dealership1 = Dealership::factory()->create(['state' => 'CA']);
        $dealership2 = Dealership::factory()->create(['state' => 'NY']);
        $dealership3 = Dealership::factory()->create(['state' => 'TX']);

        $user->dealerships()->attach([$dealership1->id, $dealership2->id, $dealership3->id]);

        $this->artisan('user:remove-dealerships', [
            'userId' => $user->id,
            'states' => ['CA', 'NY'],
        ])->assertExitCode(0);

        expect($user->dealerships()->pluck('dealerships.id')->all())
            ->toContain($dealership3->id)
            ->not->toContain($dealership1->id)
            ->not->toContain($dealership2->id);
    });

    it('does not detach anything during a dry run', function (): void {
        $user = User::factory()->create();
        $dealership = Dealership::factory()->create(['state' => 'CA']);
        $user->dealerships()->attach($dealership->id);

        $this->artisan('user:remove-dealerships', [
            'userId' => $user->id,
            'states' => ['CA'],
            '--dry-run' => true,
        ])
            ->expectsOutputToContain('Would remove user ID')
            ->expectsOutput('Dry run complete. No changes made.')
            ->assertExitCode(0);

        expect($user->dealerships()->where('dealerships.id', $dealership->id)->exists())->toBeTrue();
    });
});
