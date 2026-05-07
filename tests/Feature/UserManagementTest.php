<?php

declare(strict_types=1);

use App\Models\User;
use Spatie\Permission\Models\Role;

function superAdmin(): User
{
    $user = User::factory()->create();
    $user->assignRole('super_admin');

    return $user;
}

beforeEach(function () {
    Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
});

describe('User Index', function () {
    it('renders the users page for super admins', function () {
        $admin = superAdmin();
        User::factory()->count(3)->create();

        $this->actingAs($admin)
            ->get('/users')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Users/Index')
                ->has('users.data', 4)
                ->has('roles')
            );
    });

    it('forbids users without the super_admin role', function () {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/users')
            ->assertForbidden();
    });

    it('forbids users with other roles', function () {
        $user = User::factory()->create();
        $user->assignRole('Admin');

        $this->actingAs($user)
            ->get('/users')
            ->assertForbidden();
    });

    it('filters trashed users', function () {
        $admin = superAdmin();
        $deleted = User::factory()->create();
        $deleted->delete();

        $this->actingAs($admin)
            ->get('/users?filter=trashed')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->has('users.data', 1));
    });

    it('searches by name', function () {
        $admin = superAdmin();
        User::factory()->create(['name' => 'Distinct Person']);
        User::factory()->create(['name' => 'Someone Else']);

        $this->actingAs($admin)
            ->get('/users?search=Distinct')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->has('users.data', 1));
    });
});

describe('User Store', function () {
    it('creates a user with valid data and assigns roles', function () {
        $admin = superAdmin();
        $role = Role::where('name', 'Admin')->first();

        $this->actingAs($admin)
            ->post('/users', [
                'name' => 'New Person',
                'email' => 'new@example.com',
                'password' => 'Password123!',
                'password_confirmation' => 'Password123!',
                'roles' => [$role->id],
            ])
            ->assertRedirect('/users');

        $created = User::where('email', 'new@example.com')->firstOrFail();

        expect($created->name)->toBe('New Person')
            ->and($created->hasRole('Admin'))->toBeTrue();
    });

    it('validates required fields', function () {
        $admin = superAdmin();

        $this->actingAs($admin)
            ->post('/users', [])
            ->assertSessionHasErrors(['name', 'email', 'password']);
    });

    it('rejects unauthenticated users', function () {
        $this->post('/users', [])->assertRedirect('/login');
    });

    it('forbids non super admins', function () {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/users', [
                'name' => 'X',
                'email' => 'x@example.com',
                'password' => 'Password123!',
                'password_confirmation' => 'Password123!',
            ])
            ->assertForbidden();
    });
});

describe('User Update', function () {
    it('updates a user and syncs roles', function () {
        $admin = superAdmin();
        $target = User::factory()->create(['name' => 'Original']);
        $role = Role::where('name', 'Admin')->first();

        $this->actingAs($admin)
            ->put("/users/{$target->id}", [
                'name' => 'Updated',
                'email' => $target->email,
                'roles' => [$role->id],
            ])
            ->assertRedirect('/users');

        $target->refresh();
        expect($target->name)->toBe('Updated')
            ->and($target->hasRole('Admin'))->toBeTrue();
    });

    it('keeps the password when not provided', function () {
        $admin = superAdmin();
        $target = User::factory()->create();
        $hash = $target->password;

        $this->actingAs($admin)
            ->put("/users/{$target->id}", [
                'name' => $target->name,
                'email' => $target->email,
            ])
            ->assertRedirect();

        expect($target->refresh()->password)->toBe($hash);
    });
});

describe('User Destroy', function () {
    it('soft deletes a user', function () {
        $admin = superAdmin();
        $target = User::factory()->create();

        $this->actingAs($admin)
            ->delete("/users/{$target->id}")
            ->assertRedirect('/users');

        expect(User::find($target->id))->toBeNull()
            ->and(User::withTrashed()->find($target->id))->not->toBeNull();
    });

    it('prevents users from deleting themselves', function () {
        $admin = superAdmin();

        $this->actingAs($admin)
            ->delete("/users/{$admin->id}")
            ->assertSessionHasErrors('user');

        expect(User::find($admin->id))->not->toBeNull();
    });
});

describe('User Restore', function () {
    it('restores a soft-deleted user', function () {
        $admin = superAdmin();
        $target = User::factory()->create();
        $target->delete();

        $this->actingAs($admin)
            ->patch("/users/{$target->id}/restore")
            ->assertRedirect('/users');

        expect($target->refresh()->trashed())->toBeFalse();
    });
});

describe('User Create page', function () {
    it('renders the create page with available roles', function () {
        $admin = superAdmin();

        $this->actingAs($admin)
            ->get('/users/create')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Users/Create')
                ->has('roles')
            );
    });
});

describe('User Edit page', function () {
    it('renders the edit page for a user with their roles', function () {
        $admin = superAdmin();
        $target = User::factory()->create();
        $target->assignRole('Admin');

        $this->actingAs($admin)
            ->get("/users/{$target->id}/edit")
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Users/Edit')
                ->has('user')
                ->has('roles')
            );
    });
});

describe('User Update with password', function () {
    it('changes the password when one is provided', function () {
        $admin = superAdmin();
        $target = User::factory()->create();
        $oldHash = $target->password;

        $this->actingAs($admin)
            ->put("/users/{$target->id}", [
                'name' => $target->name,
                'email' => $target->email,
                'password' => 'NewPassword123!',
                'password_confirmation' => 'NewPassword123!',
            ])
            ->assertRedirect();

        expect($target->refresh()->password)->not->toBe($oldHash);
    });
});
