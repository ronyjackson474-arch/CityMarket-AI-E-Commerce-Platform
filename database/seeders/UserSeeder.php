<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Admin
        User::updateOrCreate(
            ['email' => 'admin@citymarket.test'],
            [
                'name' => 'CityMarket Admin',
                'email' => 'admin@citymarket.test',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'phone' => '+919876543210',
                'email_verified_at' => now(),
                'is_active' => true,
            ]
        );

        // Customer
        User::updateOrCreate(
            ['email' => 'customer@citymarket.test'],
            [
                'name' => 'Riya Sharma',
                'email' => 'customer@citymarket.test',
                'password' => Hash::make('password'),
                'role' => 'customer',
                'phone' => '+919812345678',
                'email_verified_at' => now(),
                'is_active' => true,
            ]
        );

        // A few extra customers
        User::factory()->count(5)->create();
    }
}
