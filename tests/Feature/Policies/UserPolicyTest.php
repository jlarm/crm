<?php

declare(strict_types=1);

use App\Models\User;
use App\Policies\UserPolicy;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);

    $this->permissions = [
        'view_any_user', 'view_user', 'create_user', 'update_user',
        'delete_user', 'delete_any_user', 'force_delete_user',
        'force_delete_any_user', 'restore_user', 'restore_any_user',
        'replicate_user', 'reorder_user',
    ];

    foreach ($this->permissions as $perm) {
        Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
    }

    $this->policy = new UserPolicy;
});

it('allows actions when user has permissions', function () {
    $user = User::factory()->create();
    foreach ($this->permissions as $perm) {
        $user->givePermissionTo($perm);
    }

    expect($this->policy->viewAny($user))->toBeTrue()
        ->and($this->policy->view($user))->toBeTrue()
        ->and($this->policy->create($user))->toBeTrue()
        ->and($this->policy->update($user))->toBeTrue()
        ->and($this->policy->delete($user))->toBeTrue()
        ->and($this->policy->deleteAny($user))->toBeTrue()
        ->and($this->policy->forceDelete($user))->toBeTrue()
        ->and($this->policy->forceDeleteAny($user))->toBeTrue()
        ->and($this->policy->restore($user))->toBeTrue()
        ->and($this->policy->restoreAny($user))->toBeTrue()
        ->and($this->policy->replicate($user))->toBeTrue()
        ->and($this->policy->reorder($user))->toBeTrue();
});

it('denies actions when user lacks permissions', function () {
    $user = User::factory()->create();

    expect($this->policy->viewAny($user))->toBeFalse()
        ->and($this->policy->view($user))->toBeFalse()
        ->and($this->policy->create($user))->toBeFalse()
        ->and($this->policy->update($user))->toBeFalse()
        ->and($this->policy->delete($user))->toBeFalse()
        ->and($this->policy->deleteAny($user))->toBeFalse()
        ->and($this->policy->forceDelete($user))->toBeFalse()
        ->and($this->policy->forceDeleteAny($user))->toBeFalse()
        ->and($this->policy->restore($user))->toBeFalse()
        ->and($this->policy->restoreAny($user))->toBeFalse()
        ->and($this->policy->replicate($user))->toBeFalse()
        ->and($this->policy->reorder($user))->toBeFalse();
});

it('grants only specific permissions', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('view_any_user');
    $user->givePermissionTo('view_user');

    expect($this->policy->viewAny($user))->toBeTrue()
        ->and($this->policy->view($user))->toBeTrue()
        ->and($this->policy->create($user))->toBeFalse()
        ->and($this->policy->delete($user))->toBeFalse();
});
