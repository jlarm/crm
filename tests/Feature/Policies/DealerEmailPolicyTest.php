<?php

declare(strict_types=1);

use App\Models\DealerEmail;
use App\Models\User;
use App\Policies\DealerEmailPolicy;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);

    $this->policy = new DealerEmailPolicy;
    $this->dealerEmail = new DealerEmail;
});

it('allows all actions for any user (always returns true)', function () {
    $user = User::factory()->create();

    expect($this->policy->viewAny($user))->toBeTrue()
        ->and($this->policy->view($user, $this->dealerEmail))->toBeTrue()
        ->and($this->policy->create($user))->toBeTrue()
        ->and($this->policy->update($user, $this->dealerEmail))->toBeTrue()
        ->and($this->policy->delete($user, $this->dealerEmail))->toBeTrue();
});

it('allows all actions for super_admin', function () {
    $user = User::factory()->create();
    $user->assignRole('super_admin');

    expect($this->policy->viewAny($user))->toBeTrue()
        ->and($this->policy->view($user, $this->dealerEmail))->toBeTrue()
        ->and($this->policy->create($user))->toBeTrue()
        ->and($this->policy->update($user, $this->dealerEmail))->toBeTrue()
        ->and($this->policy->delete($user, $this->dealerEmail))->toBeTrue();
});
