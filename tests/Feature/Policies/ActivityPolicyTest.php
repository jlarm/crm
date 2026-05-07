<?php

declare(strict_types=1);

use App\Models\User;
use App\Policies\ActivityPolicy;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);

    $this->policy = new ActivityPolicy;
    $this->activity = new Activity;
});

describe('ActivityPolicy as super_admin', function () {
    beforeEach(function () {
        $this->user = User::factory()->create();
        $this->user->assignRole('super_admin');
    });

    it('allows all actions', function () {
        expect($this->policy->viewAny($this->user))->toBeTrue()
            ->and($this->policy->view($this->user, $this->activity))->toBeTrue()
            ->and($this->policy->create($this->user))->toBeTrue()
            ->and($this->policy->update($this->user, $this->activity))->toBeTrue()
            ->and($this->policy->delete($this->user, $this->activity))->toBeTrue()
            ->and($this->policy->deleteAny($this->user))->toBeTrue()
            ->and($this->policy->forceDelete($this->user, $this->activity))->toBeTrue()
            ->and($this->policy->forceDeleteAny($this->user))->toBeTrue()
            ->and($this->policy->restore($this->user, $this->activity))->toBeTrue()
            ->and($this->policy->restoreAny($this->user))->toBeTrue()
            ->and($this->policy->replicate($this->user, $this->activity))->toBeTrue()
            ->and($this->policy->reorder($this->user))->toBeTrue();
    });
});

describe('ActivityPolicy as non super_admin', function () {
    beforeEach(function () {
        $this->user = User::factory()->create();
        $this->user->assignRole('Admin');
    });

    it('denies all actions', function () {
        expect($this->policy->viewAny($this->user))->toBeFalse()
            ->and($this->policy->view($this->user, $this->activity))->toBeFalse()
            ->and($this->policy->create($this->user))->toBeFalse()
            ->and($this->policy->update($this->user, $this->activity))->toBeFalse()
            ->and($this->policy->delete($this->user, $this->activity))->toBeFalse()
            ->and($this->policy->deleteAny($this->user))->toBeFalse()
            ->and($this->policy->forceDelete($this->user, $this->activity))->toBeFalse()
            ->and($this->policy->forceDeleteAny($this->user))->toBeFalse()
            ->and($this->policy->restore($this->user, $this->activity))->toBeFalse()
            ->and($this->policy->restoreAny($this->user))->toBeFalse()
            ->and($this->policy->replicate($this->user, $this->activity))->toBeFalse()
            ->and($this->policy->reorder($this->user))->toBeFalse();
    });
});

describe('ActivityPolicy with no roles', function () {
    it('denies all actions for users without any role', function () {
        $user = User::factory()->create();

        expect($this->policy->viewAny($user))->toBeFalse()
            ->and($this->policy->view($user, $this->activity))->toBeFalse()
            ->and($this->policy->create($user))->toBeFalse()
            ->and($this->policy->update($user, $this->activity))->toBeFalse()
            ->and($this->policy->delete($user, $this->activity))->toBeFalse()
            ->and($this->policy->deleteAny($user))->toBeFalse()
            ->and($this->policy->forceDelete($user, $this->activity))->toBeFalse()
            ->and($this->policy->forceDeleteAny($user))->toBeFalse()
            ->and($this->policy->restore($user, $this->activity))->toBeFalse()
            ->and($this->policy->restoreAny($user))->toBeFalse()
            ->and($this->policy->replicate($user, $this->activity))->toBeFalse()
            ->and($this->policy->reorder($user))->toBeFalse();
    });
});
