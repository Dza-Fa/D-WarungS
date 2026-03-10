<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Vendor;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class VendorSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $vendorUser = User::where('role', 'vendor')->first();
        
        if (!$vendorUser) {
            $vendorUser = User::firstOrCreate(
                ['email' => 'vendor@dwarungs.com'],
                [
                    'name' => 'Vendor Test',
                    'password' => \Illuminate\Support\Facades\Hash::make('password123'),
                    'role' => 'vendor',
                    'phone' => '081234567891',
                    'status' => 'active',
                ]
            );
        }
        
        Vendor::firstOrCreate(
            ['user_id' => $vendorUser->id],
            [
                'name' => 'Warung Nusantara',
                'description' => 'Restaurant specializing in Indonesian cuisine',
                'logo' => null,
                'cover_image' => null,
                'address' => 'Jl. Food Court No. 1',
                'phone' => '021-1234567',
                'operating_hours' => json_encode([
                    'monday' => ['09:00', '21:00'],
                    'tuesday' => ['09:00', '21:00'],
                    'wednesday' => ['09:00', '21:00'],
                    'thursday' => ['09:00', '21:00'],
                    'friday' => ['09:00', '22:00'],
                    'saturday' => ['10:00', '22:00'],
                    'sunday' => ['10:00', '21:00'],
                ]),
                'status' => 'active',
                'rating' => 4.5,
            ]
        );
    }
}

