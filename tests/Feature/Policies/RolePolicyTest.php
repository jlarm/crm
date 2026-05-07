<?php

declare(strict_types=1);

use App\Models\User;
use App\Policies\RolePolicy;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);

    $this->permissions = [
        'view_any_role', 'view_role', 'create_role', 'update_role',
        'delete_role', 'delete_any_role',
    ];

    foreach ($this->permissions as $perm) {
        Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
    }

    // The unfilled stub permissions used by the forceDelete/restore/etc. methods.
    $this->stubPermissions = [
        '{{ ForceDelete }}', '{{ ForceDeleteAny }}', '{{ Restore }}',
        '{{ RestoreAny }}', '{{ Replicate }}', '{{ Reorder }}',
    ];

    foreach ($this->stubPermissions as $perm) {
        Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
    }

    $this->policy = new RolePolicy;
    $this->role = Role::firstOrCreate(['name' => 'TestRole', 'guard_name' => 'web']);
});

it('allows core role actions when user has permissions', function () {
    $user = User::factory()->create();
    foreach ($this->permissions as $perm) {
        $user->givePermissionTo($perm);
    }

    expect($this->policy->viewAny($user))->toBeTrue()
        ->and($this->policy->view($user, $this->role))->toBeTrue()
        ->and($this->policy->create($user))->toBeTrue()
        ->and($this->policy->update($user, $this->role))->toBeTrue()
        ->and($this->policy->delete($user, $this->role))->toBeTrue()
        ->and($this->policy->deleteAny($user))->toBeTrue();
});

it('denies core role actions when user lacks permissions', function () {
    $user = User::factory()->create();

    expect($this->policy->viewAny($user))->toBeFalse()
        ->and($this->policy->view($user, $this->role))->toBeFalse()
        ->and($this->policy->create($user))->toBeFalse()
        ->and($this->policy->update($user, $this->role))->toBeFalse()
        ->and($this->policy->delete($user, $this->role))->toBeFalse()
        ->and($this->policy->deleteAny($user))->toBeFalse();
});

it('exercises stub permission methods - denies without those literal permissions', function () {
    $user = User::factory()->create();

    expect($this->policy->forceDelete($user, $this->role))->toBeFalse()
        ->and($this->policy->forceDeleteAny($user))->toBeFalse()
        ->and($this->policy->restore($user, $this->role))->toBeFalse()
        ->and($this->policy->restoreAny($user))->toBeFalse()
        ->and($this->policy->replicate($user, $this->role))->toBeFalse()
        ->and($this->policy->reorder($user))->toBeFalse();
});

it('exercises stub permission methods - allows when literal permissions granted', function () {
    $user = User::factory()->create();
    foreach ($this->stubPermissions as $perm) {
        $user->givePermissionTo($perm);
    }

    expect($this->policy->forceDelete($user, $this->role))->toBeTrue()
        ->and($this->policy->forceDeleteAny($user))->toBeTrue()
        ->and($this->policy->restore($user, $this->role))->toBeTrue()
        ->and($this->policy->restoreAny($user))->toBeTrue()
        ->and($this->policy->replicate($user, $this->role))->toBeTrue()
        ->and($this->policy->reorder($user))->toBeTrue();
});
