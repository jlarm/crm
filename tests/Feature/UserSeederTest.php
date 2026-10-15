<?php

declare(strict_types=1);

use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Support\Facades\Hash;

it('gives the seeded admin a random password outside local development', function () {
    $this->seed([RoleAndPermissionSeeder::class, UserSeeder::class]);

    $admin = User::sole();

    expect($admin->hasRole('super_admin'))->toBeTrue()
        ->and(Hash::check('password', $admin->password))->toBeFalse();
});

it('keeps the easy password for local development', function () {
    app()->detectEnvironment(fn (): string => 'local');

    $this->seed([RoleAndPermissionSeeder::class, UserSeeder::class]);

    expect(Hash::check('password', User::sole()->password))->toBeTrue();
});
