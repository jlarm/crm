<?php

declare(strict_types=1);

use App\Actions\Fortify\UpdateUserPassword;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

describe('UpdateUserPassword', function (): void {
    it('updates the user password when the current password is correct', function (): void {
        $user = User::factory()->create([
            'password' => Hash::make('current-password'),
        ]);
        $this->actingAs($user);

        (new UpdateUserPassword)->update($user, [
            'current_password' => 'current-password',
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ]);

        expect(Hash::check('new-password-123', $user->fresh()->password))->toBeTrue();
    });

    it('throws a validation exception when the current password is incorrect', function (): void {
        $user = User::factory()->create([
            'password' => Hash::make('current-password'),
        ]);
        $this->actingAs($user);

        expect(fn () => (new UpdateUserPassword)->update($user, [
            'current_password' => 'wrong-password',
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ]))->toThrow(ValidationException::class);
    });

    it('throws a validation exception when the password confirmation does not match', function (): void {
        $user = User::factory()->create([
            'password' => Hash::make('current-password'),
        ]);
        $this->actingAs($user);

        expect(fn () => (new UpdateUserPassword)->update($user, [
            'current_password' => 'current-password',
            'password' => 'new-password-123',
            'password_confirmation' => 'mismatch',
        ]))->toThrow(ValidationException::class);
    });
});
