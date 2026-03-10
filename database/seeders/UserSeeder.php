<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Admin
        User::firstOrCreate(
            ['email' => 'admin@dwarungs.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('password123'),
                'role' => 'admin',
                'phone' => '081234567890',
                'status' => 'active',
            ]
        );

        // Vendor
        User::firstOrCreate(
            ['email' => 'vendor@dwarungs.com'],
            [
                'name' => 'Vendor Test',
                'password' => Hash::make('password123'),
                'role' => 'vendor',
                'phone' => '081234567891',
                'status' => 'active',
            ]
        );

        // Cashier
        User::firstOrCreate(
            ['email' => 'cashier@dwarungs.com'],
            [
                'name' => 'Cashier Test',
                'password' => Hash::make('password123'),
                'role' => 'cashier',
                'phone' => '081234567892',
                'status' => 'active',
            ]
        );

        // Customer
        User::firstOrCreate(
            ['email' => 'customer@dwarungs.com'],
            [
                'name' => 'Customer Test',
                'password' => Hash::make('password123'),
                'role' => 'customer',
                'phone' => '081234567893',
                'status' => 'active',
            ]
        );
    }
}

