<?php

declare(strict_types=1);

use App\Models\Contact;
use App\Models\User;
use App\Policies\ContactPolicy;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);

    $this->permissions = [
        'view_any_contact', 'view_contact', 'create_contact', 'update_contact',
        'delete_contact', 'delete_any_contact', 'force_delete_contact',
        'force_delete_any_contact', 'restore_contact', 'restore_any_contact',
        'replicate_contact', 'reorder_contact',
    ];

    foreach ($this->permissions as $perm) {
        Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
    }

    $this->policy = new ContactPolicy;
    $this->contact = new Contact;
});

it('allows actions when user has permissions', function () {
    $user = User::factory()->create();
    foreach ($this->permissions as $perm) {
        $user->givePermissionTo($perm);
    }

    expect($this->policy->viewAny($user))->toBeTrue()
        ->and($this->policy->view($user, $this->contact))->toBeTrue()
        ->and($this->policy->create($user))->toBeTrue()
        ->and($this->policy->update($user, $this->contact))->toBeTrue()
        ->and($this->policy->delete($user, $this->contact))->toBeTrue()
        ->and($this->policy->deleteAny($user))->toBeTrue()
        ->and($this->policy->forceDelete($user, $this->contact))->toBeTrue()
        ->and($this->policy->forceDeleteAny($user))->toBeTrue()
        ->and($this->policy->restore($user, $this->contact))->toBeTrue()
        ->and($this->policy->restoreAny($user))->toBeTrue()
        ->and($this->policy->replicate($user, $this->contact))->toBeTrue()
        ->and($this->policy->reorder($user))->toBeTrue();
});

it('denies actions when user lacks permissions', function () {
    $user = User::factory()->create();

    expect($this->policy->viewAny($user))->toBeFalse()
        ->and($this->policy->view($user, $this->contact))->toBeFalse()
        ->and($this->policy->create($user))->toBeFalse()
        ->and($this->policy->update($user, $this->contact))->toBeFalse()
        ->and($this->policy->delete($user, $this->contact))->toBeFalse()
        ->and($this->policy->deleteAny($user))->toBeFalse()
        ->and($this->policy->forceDelete($user, $this->contact))->toBeFalse()
        ->and($this->policy->forceDeleteAny($user))->toBeFalse()
        ->and($this->policy->restore($user, $this->contact))->toBeFalse()
        ->and($this->policy->restoreAny($user))->toBeFalse()
        ->and($this->policy->replicate($user, $this->contact))->toBeFalse()
        ->and($this->policy->reorder($user))->toBeFalse();
});

it('grants only specific permissions', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('view_any_contact');
    $user->givePermissionTo('view_contact');

    expect($this->policy->viewAny($user))->toBeTrue()
        ->and($this->policy->view($user, $this->contact))->toBeTrue()
        ->and($this->policy->create($user))->toBeFalse()
        ->and($this->policy->update($user, $this->contact))->toBeFalse();
});
