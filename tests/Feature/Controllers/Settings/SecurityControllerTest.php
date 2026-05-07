<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Support\Facades\Hash;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Laravel\put;

beforeEach(function () {
    $this->user = User::factory()->create([
        'password' => Hash::make('current-password'),
    ]);
    actingAs($this->user);
});

describe('Settings SecurityController edit', function () {
    it('renders the security settings page', function () {
        get(route('settings.security.edit'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('settings/Security')
                ->has('canManageTwoFactor')
                ->has('requiresConfirmation')
                ->has('twoFactorEnabled')
                ->has('recoveryCodes')
                ->has('status')
                ->where('twoFactorEnabled', false)
            );
    });

    it('decrypts and exposes recovery codes when two-factor is enabled', function () {
        $codes = ['code-one', 'code-two'];

        $this->user->forceFill([
            'two_factor_secret' => encrypt('secret-value'),
            'two_factor_recovery_codes' => encrypt(json_encode($codes)),
        ])->save();

        get(route('settings.security.edit'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('twoFactorEnabled', true)
                ->where('recoveryCodes', $codes)
            );
    });
});

describe('Settings SecurityController update', function () {
    it('updates the user password', function () {
        put(route('settings.security.update'), [
            'current_password' => 'current-password',
            'password' => 'NewSecure123!',
            'password_confirmation' => 'NewSecure123!',
        ])->assertRedirect(route('settings.security.edit'));

        expect(Hash::check('NewSecure123!', $this->user->fresh()->password))->toBeTrue();
    });

    it('rejects an incorrect current password', function () {
        put(route('settings.security.update'), [
            'current_password' => 'wrong-password',
            'password' => 'NewSecure123!',
            'password_confirmation' => 'NewSecure123!',
        ])->assertSessionHasErrors();
    });

    it('rejects a password that does not match its confirmation', function () {
        put(route('settings.security.update'), [
            'current_password' => 'current-password',
            'password' => 'NewSecure123!',
            'password_confirmation' => 'DifferentPassword!',
        ])->assertSessionHasErrors();
    });
});
