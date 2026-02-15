<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'kaosar3662@gmail.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('Hikaosar@366'),
                'role' => 'admin',
                'email_verified_at' => now(),
                'email_verification_token' => null,
            ]
        );
    }
}
