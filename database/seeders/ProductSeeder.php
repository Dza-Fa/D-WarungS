<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Vendor;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $vendor = Vendor::first();

        if (! $vendor) {
            return;
        }

        $category1 = Category::where('name', 'Makanan Utama')->first();
        $category2 = Category::where('name', 'Minuman')->first();

        if (! $category1 || ! $category2) {
            return;
        }

        // Product 1: Nasi Goreng Special
        Product::firstOrCreate(
            ['vendor_id' => $vendor->id, 'name' => 'Nasi Goreng Special'],
            [
                'category_id' => $category1->id,
                'description' => 'Fried rice with egg, shrimp paste, and vegetables',
                'price' => 25000,
                'original_price' => 30000,
                'status' => 'active',
                'is_featured' => true,
            ]
        );

        // Product 2: Mie Goreng Jawa
        Product::firstOrCreate(
            ['vendor_id' => $vendor->id, 'name' => 'Mie Goreng Jawa'],
            [
                'category_id' => $category1->id,
                'description' => 'Javanese style fried noodles',
                'price' => 22000,
                'status' => 'active',
            ]
        );

        // Product 3: Es Teh Manis
        Product::firstOrCreate(
            ['vendor_id' => $vendor->id, 'name' => 'Es Teh Manis'],
            [
                'category_id' => $category2->id,
                'description' => 'Sweet iced tea',
                'price' => 5000,
                'status' => 'active',
            ]
        );

        // Product 4: Es Jeruk
        Product::firstOrCreate(
            ['vendor_id' => $vendor->id, 'name' => 'Es Jeruk'],
            [
                'category_id' => $category2->id,
                'description' => 'Fresh orange juice',
                'price' => 8000,
                'status' => 'active',
            ]
        );
    }
}
