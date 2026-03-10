<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Vendor;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $vendor = Vendor::first();
        
        if (!$vendor) {
            return;
        }
        
        // Category 1: Makanan Utama
        Category::firstOrCreate(
            ['vendor_id' => $vendor->id, 'name' => 'Makanan Utama'],
            [
                'description' => 'Main dishes',
                'display_order' => 1,
                'status' => 'active',
            ]
        );
        
        // Category 2: Minuman
        Category::firstOrCreate(
            ['vendor_id' => $vendor->id, 'name' => 'Minuman'],
            [
                'description' => 'Beverages',
                'display_order' => 2,
                'status' => 'active',
            ]
        );
        
        // Category 3: Snack
        Category::firstOrCreate(
            ['vendor_id' => $vendor->id, 'name' => 'Snack'],
            [
                'description' => 'Snacks',
                'display_order' => 3,
                'status' => 'active',
            ]
        );
    }
}

