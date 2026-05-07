<?php

declare(strict_types=1);

use App\Models\Dealership;
use App\Models\User;
use App\Policies\DealershipPolicy;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);

    $this->permissions = [
        'view_any_dealership', 'view_dealership', 'create_dealership',
        'update_dealership', 'delete_dealership', 'delete_any_dealership',
        'force_delete_dealership', 'force_delete_any_dealership',
        'restore_dealership', 'restore_any_dealership', 'replicate_dealership',
        'reorder_dealership',
    ];

    foreach ($this->permissions as $perm) {
        Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
    }

    $this->policy = new DealershipPolicy;
    $this->dealership = new Dealership;
});

it('allows actions when user has permissions', function () {
    $user = User::factory()->create();
    foreach ($this->permissions as $perm) {
        $user->givePermissionTo($perm);
    }

    expect($this->policy->viewAny($user))->toBeTrue()
        ->and($this->policy->view($user, $this->dealership))->toBeTrue()
        ->and($this->policy->create($user))->toBeTrue()
        ->and($this->policy->update($user, $this->dealership))->toBeTrue()
        ->and($this->policy->delete($user, $this->dealership))->toBeTrue()
        ->and($this->policy->deleteAny($user))->toBeTrue()
        ->and($this->policy->forceDelete($user, $this->dealership))->toBeTrue()
        ->and($this->policy->forceDeleteAny($user))->toBeTrue()
        ->and($this->policy->restore($user, $this->dealership))->toBeTrue()
        ->and($this->policy->restoreAny($user))->toBeTrue()
        ->and($this->policy->replicate($user, $this->dealership))->toBeTrue()
        ->and($this->policy->reorder($user))->toBeTrue();
});

it('denies actions when user lacks permissions', function () {
    $user = User::factory()->create();

    expect($this->policy->viewAny($user))->toBeFalse()
        ->and($this->policy->view($user, $this->dealership))->toBeFalse()
        ->and($this->policy->create($user))->toBeFalse()
        ->and($this->policy->update($user, $this->dealership))->toBeFalse()
        ->and($this->policy->delete($user, $this->dealership))->toBeFalse()
        ->and($this->policy->deleteAny($user))->toBeFalse()
        ->and($this->policy->forceDelete($user, $this->dealership))->toBeFalse()
        ->and($this->policy->forceDeleteAny($user))->toBeFalse()
        ->and($this->policy->restore($user, $this->dealership))->toBeFalse()
        ->and($this->policy->restoreAny($user))->toBeFalse()
        ->and($this->policy->replicate($user, $this->dealership))->toBeFalse()
        ->and($this->policy->reorder($user))->toBeFalse();
});

it('grants only specific permissions', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('create_dealership');
    $user->givePermissionTo('update_dealership');

    expect($this->policy->create($user))->toBeTrue()
        ->and($this->policy->update($user, $this->dealership))->toBeTrue()
        ->and($this->policy->view($user, $this->dealership))->toBeFalse()
        ->and($this->policy->delete($user, $this->dealership))->toBeFalse();
});
