<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::create([
            'name' => 'Joe Lohr',
            'email' => 'jlohr@autorisknow.com',
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
        ]);

        $user->assignRole('super_admin');
    }
}
