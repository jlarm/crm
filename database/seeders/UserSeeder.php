<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run()
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
