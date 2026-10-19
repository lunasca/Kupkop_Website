<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create a Default Admin User for testing
        User::firstOrCreate(
            ['email' => 'admin@kupkop.test'],
            [
                'name'     => 'Shelter Admin',
                'password' => Hash::make('password123'),
                'is_admin' => true,
            ]
        );

        // 2. Create a Regular User for testing
        User::firstOrCreate(
            ['email' => 'user@kupkop.test'],
            [
                'name'     => 'Regular Adopter',
                'password' => Hash::make('password123'),
                'is_admin' => false,
            ]
        );
    }
}
