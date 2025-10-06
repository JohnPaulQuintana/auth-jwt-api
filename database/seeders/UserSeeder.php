<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'System Administrator',
                'password' => Hash::make('admin123'),
                'role' => 'administrator',
            ]
        );

        User::updateOrCreate(
            ['email' => 'dev@example.com'],
            [
                'name' => 'System Developer',
                'password' => Hash::make('developer123'),
                'role' => 'developer',
            ]
        );
    }
}
