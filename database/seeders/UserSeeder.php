<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Outside local development the admin gets a random password, printed once,
     * so seeding a deployed database never leaves a guessable super admin login.
     */
    public function run(): void
    {
        $password = app()->isLocal() ? 'password' : Str::password(24);

        $user = User::create([
            'name' => 'Joe Lohr',
            'email' => 'jlohr@autorisknow.com',
            'email_verified_at' => now(),
            'password' => bcrypt($password),
        ]);

        $user->assignRole('super_admin');

        if (! app()->isLocal()) {
            $this->command?->warn("Admin password for {$user->email}: {$password}");
        }
    }
}
