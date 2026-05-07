<?php

declare(strict_types=1);

use App\Models\SentEmail;
use App\Models\User;
use App\Policies\SentEmailPolicy;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);

    $this->policy = new SentEmailPolicy;
    $this->sentEmail = new SentEmail;
});

it('allows all actions for super_admin', function () {
    $user = User::factory()->create();
    $user->assignRole('super_admin');

    expect($this->policy->viewAny($user))->toBeTrue()
        ->and($this->policy->view($user, $this->sentEmail))->toBeTrue()
        ->and($this->policy->create($user))->toBeTrue()
        ->and($this->policy->update($user, $this->sentEmail))->toBeTrue()
        ->and($this->policy->delete($user, $this->sentEmail))->toBeTrue()
        ->and($this->policy->restore($user, $this->sentEmail))->toBeTrue()
        ->and($this->policy->forceDelete($user, $this->sentEmail))->toBeTrue();
});

it('denies all actions for non super_admin', function () {
    $user = User::factory()->create();
    $user->assignRole('Admin');

    expect($this->policy->viewAny($user))->toBeFalse()
        ->and($this->policy->view($user, $this->sentEmail))->toBeFalse()
        ->and($this->policy->create($user))->toBeFalse()
        ->and($this->policy->update($user, $this->sentEmail))->toBeFalse()
        ->and($this->policy->delete($user, $this->sentEmail))->toBeFalse()
        ->and($this->policy->restore($user, $this->sentEmail))->toBeFalse()
        ->and($this->policy->forceDelete($user, $this->sentEmail))->toBeFalse();
});

it('denies all actions for users without role', function () {
    $user = User::factory()->create();

    expect($this->policy->viewAny($user))->toBeFalse()
        ->and($this->policy->view($user, $this->sentEmail))->toBeFalse()
        ->and($this->policy->create($user))->toBeFalse()
        ->and($this->policy->update($user, $this->sentEmail))->toBeFalse()
        ->and($this->policy->delete($user, $this->sentEmail))->toBeFalse()
        ->and($this->policy->restore($user, $this->sentEmail))->toBeFalse()
        ->and($this->policy->forceDelete($user, $this->sentEmail))->toBeFalse();
});
