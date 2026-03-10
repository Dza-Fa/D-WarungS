# Prompt untuk Blackbox AI - Phase 2: Database Setup

---

## Project Context
- **Project Name:** D-WarungS (O2O Food-Court Platform)
- **Tech Stack:** PHP, Laravel 10.x, MySQL, XAMPP
- **Location:** c:/xampp/htdocs/D-WarungS

---

## Tasks to Complete

### 1. Create .env Configuration File
Buat file `.env` di root project dengan konfigurasi:
```
APP_NAME="D-WarungS"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=d_warung_s
DB_USERNAME=root
DB_PASSWORD=

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120
```

### 2. Generate Application Key
Jalankan perintah:
```bash
php artisan key:generate
```

### 3. Create Database
Buat database MySQL dengan nama `d_warung_s` via phpMyAdmin atau command line.

### 4. Create Laravel Migrations
Buat migrations untuk 7 tabel berikut di `database/migrations/`:

#### a. Modify users_table.php
Tambahkan kolom-kolom ini ke migration users yang sudah ada:
```php
$table->enum('role', ['customer', 'vendor', 'cashier', 'admin'])->default('customer');
$table->string('phone', 20)->nullable();
$table->string('avatar')->nullable();
$table->enum('status', ['active', 'inactive', 'suspended'])->default('active');
$table->timestamp('email_verified_at')->nullable();
```

#### b. vendors_table.php (Baru)
```php
Schema::create('vendors', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('user_id');
    $table->string('name');
    $table->text('description')->nullable();
    $table->string('logo')->nullable();
    $table->string('cover_image')->nullable();
    $table->text('address')->nullable();
    $table->string('phone', 20)->nullable();
    $table->json('operating_hours')->nullable();
    $table->enum('status', ['pending', 'active', 'inactive', 'suspended'])->default('pending');
    $table->decimal('rating', 3, 2)->default(0);
    $table->timestamps();
    
    $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
    $table->index('status');
    $table->index('user_id');
});
```

#### c. categories_table.php (Baru)
```php
Schema::create('categories', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('vendor_id');
    $table->string('name');
    $table->text('description')->nullable();
    $table->string('image')->nullable();
    $table->integer('display_order')->default(0);
    $table->enum('status', ['active', 'inactive'])->default('active');
    $table->timestamps();
    
    $table->foreign('vendor_id')->references('id')->on('vendors')->onDelete('cascade');
    $table->index('vendor_id');
    $table->index('status');
});
```

#### d. products_table.php (Baru)
```php
Schema::create('products', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('vendor_id');
    $table->unsignedBigInteger('category_id')->nullable();
    $table->string('name');
    $table->text('description')->nullable();
    $table->string('image')->nullable();
    $table->decimal('price', 10, 2);
    $table->decimal('original_price', 10, 2)->nullable();
    $table->enum('status', ['active', 'inactive', 'out_of_stock'])->default('active');
    $table->boolean('is_featured')->default(false);
    $table->timestamps();
    
    $table->foreign('vendor_id')->references('id')->on('vendors')->onDelete('cascade');
    $table->foreign('category_id')->references('id')->on('categories')->onDelete('set null');
    $table->index('vendor_id');
    $table->index('category_id');
    $table->index('status');
    $table->index('is_featured');
});
```

#### e. orders_table.php (Baru)
```php
Schema::create('orders', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('user_id');
    $table->unsignedBigInteger('vendor_id');
    $table->string('order_number')->unique();
    $table->decimal('subtotal', 10, 2);
    $table->decimal('tax_amount', 10, 2)->default(0);
    $table->decimal('discount_amount', 10, 2)->default(0);
    $table->decimal('total_amount', 10, 2);
    $table->enum('status', ['pending', 'confirmed', 'preparing', 'ready', 'completed', 'cancelled'])->default('pending');
    $table->enum('payment_method', ['cash', 'digital_wallet', 'card'])->notNull();
    $table->enum('payment_status', ['pending', 'paid', 'failed', 'refunded'])->default('pending');
    $table->string('payment_proof')->nullable();
    $table->text('notes')->nullable();
    $table->timestamps();
    
    $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
    $table->foreign('vendor_id')->references('id')->on('vendors')->onDelete('cascade');
    $table->index('user_id');
    $table->index('vendor_id');
    $table->index('order_number');
    $table->index('status');
    $table->index('payment_status');
    $table->index('created_at');
});
```

#### f. order_items_table.php (Baru)
```php
Schema::create('order_items', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('order_id');
    $table->unsignedBigInteger('product_id');
    $table->integer('quantity');
    $table->decimal('unit_price', 10, 2);
    $table->decimal('subtotal', 10, 2);
    $table->text('notes')->nullable();
    $table->timestamps();
    
    $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
    $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
    $table->index('order_id');
    $table->index('product_id');
});
```

#### g. reviews_table.php (Baru)
```php
Schema::create('reviews', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('user_id');
    $table->unsignedBigInteger('vendor_id');
    $table->unsignedBigInteger('order_id')->nullable();
    $table->integer('rating')->check('rating >= 1 AND rating <= 5');
    $table->text('comment')->nullable();
    $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
    $table->timestamps();
    
    $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
    $table->foreign('vendor_id')->references('id')->on('vendors')->onDelete('cascade');
    $table->foreign('order_id')->references('id')->on('orders')->onDelete('set null');
    $table->index('user_id');
    $table->index('vendor_id');
    $table->index('order_id');
    $table->index('status');
});
```

### 5. Run Migrations
Jalankan perintah:
```bash
php artisan migrate
```

### 6. Create Database Seeders
Buat seeders di `database/seeders/`:

#### a. UserSeeder.php
```php
public function run(): void
{
    // Admin
    User::create([
        'name' => 'Admin',
        'email' => 'admin@dwarungs.com',
        'password' => Hash::make('password123'),
        'role' => 'admin',
        'phone' => '081234567890',
        'status' => 'active',
    ]);

    // Vendor
    User::create([
        'name' => 'Vendor Test',
        'email' => 'vendor@dwarungs.com',
        'password' => Hash::make('password123'),
        'role' => 'vendor',
        'phone' => '081234567891',
        'status' => 'active',
    ]);

    // Cashier
    User::create([
        'name' => 'Cashier Test',
        'email' => 'cashier@dwarungs.com',
        'password' => Hash::make('password123'),
        'role' => 'cashier',
        'phone' => '081234567892',
        'status' => 'active',
    ]);

    // Customer
    User::create([
        'name' => 'Customer Test',
        'email' => 'customer@dwarungs.com',
        'password' => Hash::make('password123'),
        'role' => 'customer',
        'phone' => '081234567893',
        'status' => 'active',
    ]);
}
```

#### b. VendorSeeder.php
```php
public function run(): void
{
    $vendorUser = User::where('role', 'vendor')->first();
    
    Vendor::create([
        'user_id' => $vendorUser->id,
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
    ]);
}
```

#### c. CategorySeeder.php
```php
public function run(): void
{
    $vendor = Vendor::first();
    
    Category::create([
        'vendor_id' => $vendor->id,
        'name' => 'Makanan Utama',
        'description' => 'Main dishes',
        'display_order' => 1,
        'status' => 'active',
    ]);
    
    Category::create([
        'vendor_id' => $vendor->id,
        'name' => 'Minuman',
        'description' => 'Beverages',
        'display_order' => 2,
        'status' => 'active',
    ]);
    
    Category::create([
        'vendor_id' => $vendor->id,
        'name' => 'Snack',
        'description' => 'Snacks',
        'display_order' => 3,
        'status' => 'active',
    ]);
}
```

#### d. ProductSeeder.php
```php
public function run(): void
{
    $vendor = Vendor::first();
    $category1 = Category::where('name', 'Makanan Utama')->first();
    $category2 = Category::where('name', 'Minuman')->first();
    
    // Main dishes
    Product::create([
        'vendor_id' => $vendor->id,
        'category_id' => $category1->id,
        'name' => 'Nasi Goreng Special',
        'description' => 'Fried rice with egg, shrimp paste, and vegetables',
        'price' => 25000,
        'original_price' => 30000,
        'status' => 'active',
        'is_featured' => true,
    ]);
    
    Product::create([
        'vendor_id' => $vendor->id,
        'category_id' => $category1->id,
        'name' => 'Mie Goreng Jawa',
        'description' => 'Javanese style fried noodles',
        'price' => 22000,
        'status' => 'active',
    ]);
    
    // Beverages
    Product::create([
        'vendor_id' => $vendor->id,
        'category_id' => $category2->id,
        'name' => 'Es Teh Manis',
        'description' => 'Sweet iced tea',
        'price' => 5000,
        'status' => 'active',
    ]);
    
    Product::create([
        'vendor_id' => $vendor->id,
        'category_id' => $category2->id,
        'name' => 'Es Jeruk',
        'description' => 'Fresh orange juice',
        'price' => 8000,
        'status' => 'active',
    ]);
}
```

#### e. Update DatabaseSeeder.php
Tambahkan pemanggilan seeders:
```php
public function run(): void
{
    $this->call([
        UserSeeder::class,
        VendorSeeder::class,
        CategorySeeder::class,
        ProductSeeder::class,
    ]);
}
```

### 7. Run Seeders
Jalankan perintah:
```bash
php artisan db:seed
```

---

## Output yang Diharapkan

1. File `.env` dibuat dengan konfigurasi database
2. Application key di-generate
3. Database `d_warung_s` dibuat
4. 7 migration files dibuat di `database/migrations/`
5. Migrations dijalankan dengan `php artisan migrate`
6. 4 seeder files dibuat di `database/seeders/`
7. Seeders dijalankan dengan `php artisan db:seed`

---

## Catatan

- Semua tabel sudah include indexes untuk performa
- Relasi foreign key sudah diatur dengan cascade delete
- Sample data sudah включая admin, vendor, cashier, customer users
- Sample vendor dengan menu items sudah tersedia untuk testing


