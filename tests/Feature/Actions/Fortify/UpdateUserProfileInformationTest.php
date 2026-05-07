<?php

declare(strict_types=1);

use App\Actions\Fortify\UpdateUserProfileInformation;
use App\Models\User;
use Illuminate\Validation\ValidationException;

describe('UpdateUserProfileInformation', function (): void {
    it('updates the user profile when the email is unchanged', function (): void {
        $user = User::factory()->create([
            'name' => 'Old Name',
            'email' => 'same@example.com',
        ]);

        (new UpdateUserProfileInformation)->update($user, [
            'name' => 'New Name',
            'email' => 'same@example.com',
        ]);

        $fresh = $user->fresh();
        expect($fresh->name)->toBe('New Name')
            ->and($fresh->email)->toBe('same@example.com');
    });

    it('updates the email when it changes (User does not implement MustVerifyEmail)', function (): void {
        $user = User::factory()->create([
            'email' => 'old@example.com',
            'email_verified_at' => now(),
        ]);

        (new UpdateUserProfileInformation)->update($user, [
            'name' => 'Updated Name',
            'email' => 'new@example.com',
        ]);

        $fresh = $user->fresh();
        expect($fresh->email)->toBe('new@example.com')
            ->and($fresh->name)->toBe('Updated Name')
            ->and($fresh->email_verified_at)->not->toBeNull();
    });

    it('throws a validation exception when name is missing', function (): void {
        $user = User::factory()->create();

        expect(fn () => (new UpdateUserProfileInformation)->update($user, [
            'name' => '',
            'email' => $user->email,
        ]))->toThrow(ValidationException::class);
    });

    it('throws a validation exception when email is already taken', function (): void {
        $existing = User::factory()->create(['email' => 'taken@example.com']);
        $user = User::factory()->create();

        expect(fn () => (new UpdateUserProfileInformation)->update($user, [
            'name' => 'OK',
            'email' => 'taken@example.com',
        ]))->toThrow(ValidationException::class);
    });
});
