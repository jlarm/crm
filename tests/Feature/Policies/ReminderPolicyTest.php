<?php

declare(strict_types=1);

use App\Models\Reminder;
use App\Models\User;
use App\Policies\ReminderPolicy;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);

    $this->permissions = [
        'view_any_reminder', 'view_reminder', 'create_reminder', 'update_reminder',
        'delete_reminder', 'delete_any_reminder', 'force_delete_reminder',
        'force_delete_any_reminder', 'restore_reminder', 'restore_any_reminder',
        'replicate_reminder', 'reorder_reminder',
    ];

    foreach ($this->permissions as $perm) {
        Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
    }

    $this->policy = new ReminderPolicy;
    $this->reminder = new Reminder;
});

it('allows actions when user has permissions', function () {
    $user = User::factory()->create();
    foreach ($this->permissions as $perm) {
        $user->givePermissionTo($perm);
    }

    expect($this->policy->viewAny($user))->toBeTrue()
        ->and($this->policy->view($user, $this->reminder))->toBeTrue()
        ->and($this->policy->create($user))->toBeTrue()
        ->and($this->policy->update($user, $this->reminder))->toBeTrue()
        ->and($this->policy->delete($user, $this->reminder))->toBeTrue()
        ->and($this->policy->deleteAny($user))->toBeTrue()
        ->and($this->policy->forceDelete($user, $this->reminder))->toBeTrue()
        ->and($this->policy->forceDeleteAny($user))->toBeTrue()
        ->and($this->policy->restore($user, $this->reminder))->toBeTrue()
        ->and($this->policy->restoreAny($user))->toBeTrue()
        ->and($this->policy->replicate($user, $this->reminder))->toBeTrue()
        ->and($this->policy->reorder($user))->toBeTrue();
});

it('denies actions when user lacks permissions', function () {
    $user = User::factory()->create();

    expect($this->policy->viewAny($user))->toBeFalse()
        ->and($this->policy->view($user, $this->reminder))->toBeFalse()
        ->and($this->policy->create($user))->toBeFalse()
        ->and($this->policy->update($user, $this->reminder))->toBeFalse()
        ->and($this->policy->delete($user, $this->reminder))->toBeFalse()
        ->and($this->policy->deleteAny($user))->toBeFalse()
        ->and($this->policy->forceDelete($user, $this->reminder))->toBeFalse()
        ->and($this->policy->forceDeleteAny($user))->toBeFalse()
        ->and($this->policy->restore($user, $this->reminder))->toBeFalse()
        ->and($this->policy->restoreAny($user))->toBeFalse()
        ->and($this->policy->replicate($user, $this->reminder))->toBeFalse()
        ->and($this->policy->reorder($user))->toBeFalse();
});

it('grants only specific permissions', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('view_reminder');
    $user->givePermissionTo('create_reminder');

    expect($this->policy->view($user, $this->reminder))->toBeTrue()
        ->and($this->policy->create($user))->toBeTrue()
        ->and($this->policy->update($user, $this->reminder))->toBeFalse()
        ->and($this->policy->delete($user, $this->reminder))->toBeFalse();
});
