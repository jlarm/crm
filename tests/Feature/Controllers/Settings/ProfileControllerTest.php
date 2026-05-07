<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Support\Facades\Hash;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\delete;
use function Pest\Laravel\get;
use function Pest\Laravel\patch;

beforeEach(function () {
    $this->user = User::factory()->create([
        'password' => Hash::make('password'),
    ]);
    actingAs($this->user);
});

describe('Settings ProfileController edit', function () {
    it('renders the profile settings page', function () {
        get(route('settings.profile.edit'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('settings/Profile')
                ->has('mustVerifyEmail')
                ->has('status')
            );
    });
});

describe('Settings ProfileController update', function () {
    it('updates the user profile', function () {
        patch(route('settings.profile.update'), [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
        ])->assertRedirect(route('settings.profile.edit'));

        $this->user->refresh();

        expect($this->user->name)->toBe('Updated Name')
            ->and($this->user->email)->toBe('updated@example.com');
    });

    it('rejects an invalid email', function () {
        patch(route('settings.profile.update'), [
            'name' => 'Bob',
            'email' => 'not-an-email',
        ])->assertSessionHasErrors();
    });
});

describe('Settings ProfileController destroy', function () {
    it('requires the current password', function () {
        delete(route('settings.profile.destroy'), [
            'password' => 'wrong-password',
        ])->assertSessionHasErrors('password');

        $this->assertDatabaseHas('users', ['id' => $this->user->id]);
    });

    it('deletes the user and logs them out', function () {
        delete(route('settings.profile.destroy'), [
            'password' => 'password',
        ])->assertRedirect('/');

        $this->assertSoftDeleted('users', ['id' => $this->user->id]);
        expect(auth()->check())->toBeFalse();
    });
});
