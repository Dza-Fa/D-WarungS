# Prompt untuk Blackbox AI - Phase 3: Backend Implementation

---

## Project Context
- **Project Name:** D-WarungS (O2O Food-Court Platform)
- **Tech Stack:** PHP, Laravel 10.x, MySQL, XAMPP
- **Location:** c:/xampp/htdocs/D-WarungS

---

## Phase 3: Backend Implementation

### Tasks to Complete

#### 1. Install Laravel Breeze (Authentication)
```bash
composer require laravel/breeze --dev
php artisan breeze:install blade
npm install && npm run build
```

#### 2. Create Custom Middleware for Role-Based Access Control

**app/Http/Middleware/CheckRole.php**
```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (!$request->user() || $request->user()->role !== $role) {
            abort(403, 'Unauthorized access.');
        }
        return $next($request);
    }
}
```

**Register di bootstrap/app.php:**
```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'role' => \App\Http\Middleware\CheckRole::class,
    ]);
})
```

#### 3. Create Controllers

**app/Http/Controllers/Frontend/HomeController.php**
```php
public function index()
{
    $vendors = Vendor::where('status', 'active')->with('categories')->get();
    return view('home', compact('vendors'));
}
```

**app/Http/Controllers/Frontend/VendorController.php**
```php
public function index()
{
    $vendors = Vendor::where('status', 'active')->with('categories', 'products')->paginate(12);
    return view('vendors.index', compact('vendors'));
}

public function show(Vendor $vendor)
{
    $vendor->load('categories.products');
    return view('vendors.show', compact('vendor'));
}
```

**app/Http/Controllers/Frontend/ProductController.php**
```php
public function show(Product $product)
{
    return view('products.show', compact('product'));
}
```

**app/Http/Controllers/Frontend/CartController.php**
```php
public function index()
{
    $cart = session()->get('cart', []);
    return view('cart.index', compact('cart'));
}

public function add(Request $request)
{
    $product = Product::findOrFail($request->product_id);
    $cart = session()->get('cart', []);
    
    $cart[$product->id] = [
        'name' => $product->name,
        'price' => $product->price,
        'quantity' => $request->quantity ?? 1,
        'image' => $product->image,
    ];
    
    session()->put('cart', $cart);
    return redirect()->back()->with('success', 'Product added to cart!');
}

public function update(Request $request)
{
    $cart = session()->get('cart', []);
    $cart[$request->id]['quantity'] = $request->quantity;
    session()->put('cart', $cart);
    return response()->json(['success' => true]);
}

public function remove(Request $request)
{
    $cart = session()->get('cart', []);
    unset($cart[$request->id]);
    session()->put('cart', $cart);
    return response()->json(['success' => true]);
}
```

**app/Http/Controllers/Frontend/CheckoutController.php**
```php
public function index()
{
    $cart = session()->get('cart', []);
    $vendors = Vendor::where('status', 'active')->get();
    return view('checkout.index', compact('cart', 'vendors'));
}

public function store(Request $request)
{
    $cart = session()->get('cart', []);
    
    $order = Order::create([
        'user_id' => auth()->id(),
        'vendor_id' => $request->vendor_id,
        'order_number' => 'ORD-' . date('Ymd') . '-' . str_pad(Order::count() + 1, 4, '0', STR_PAD_LEFT),
        'subtotal' => array_sum(array_map(function($item) {
            return $item['price'] * $item['quantity'];
        }, $cart)),
        'total_amount' => array_sum(array_map(function($item) {
            return $item['price'] * $item['quantity'];
        }, $cart)),
        'status' => 'pending',
        'payment_method' => $request->payment_method,
        'payment_status' => 'pending',
    ]);
    
    foreach ($cart as $productId => $item) {
        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $productId,
            'quantity' => $item['quantity'],
            'unit_price' => $item['price'],
            'subtotal' => $item['price'] * $item['quantity'],
        ]);
    }
    
    session()->forget('cart');
    return redirect()->route('checkout.success', $order->order_number)->with('success', 'Order placed successfully!');
}

public function success($orderNumber)
{
    $order = Order::where('order_number', $orderNumber)->firstOrFail();
    return view('checkout.success', compact('order'));
}
```

**app/Http/Controllers/Frontend/OrderController.php**
```php
public function index()
{
    $orders = Order::where('user_id', auth()->id())->with('vendor', 'orderItems')->latest()->get();
    return view('orders.index', compact('orders'));
}

public function show(Order $order)
{
    $order->load('orderItems.product', 'vendor');
    return view('orders.show', compact('order'));
}
```

#### 4. Create Vendor Panel Controllers

**app/Http/Controllers/Vendor/DashboardController.php**
```php
public function index()
{
    $vendor = auth()->user()->vendor;
    $orders = Order::where('vendor_id', $vendor->id)->with('user')->latest()->take(10)->get();
    $totalOrders = Order::where('vendor_id', $vendor->id)->count();
    $totalRevenue = Order::where('vendor_id', $vendor->id)->where('payment_status', 'paid')->sum('total_amount');
    
    return view('vendor.dashboard', compact('vendor', 'orders', 'totalOrders', 'totalRevenue'));
}
```

**app/Http/Controllers/Vendor/OrderController.php**
```php
public function index()
{
    $orders = Order::where('vendor_id', auth()->user()->vendor->id)->with('user', 'orderItems')->latest()->get();
    return view('vendor.orders.index', compact('orders'));
}

public function updateStatus(Request $request, Order $order)
{
    $order->update(['status' => $request->status]);
    return redirect()->back()->with('success', 'Order status updated!');
}
```

**app/Http/Controllers/Vendor/ProductController.php**
```php
public function index()
{
    $products = Product::where('vendor_id', auth()->user()->vendor->id)->with('category')->get();
    return view('vendor.products.index', compact('products'));
}

public function store(Request $request)
{
    $request->validate([
        'name' => 'required',
        'price' => 'required|numeric',
    ]);
    
    Product::create([
        'vendor_id' => auth()->user()->vendor->id,
        'category_id' => $request->category_id,
        'name' => $request->name,
        'description' => $request->description,
        'price' => $request->price,
        'status' => 'active',
    ]);
    
    return redirect()->route('vendor.products.index')->with('success', 'Product created!');
}

public function update(Request $request, Product $product)
{
    $product->update($request->all());
    return redirect()->back()->with('success', 'Product updated!');
}

public function destroy(Product $product)
{
    $product->delete();
    return redirect()->back()->with('success', 'Product deleted!');
}
```

**app/Http/Controllers/Vendor/CategoryController.php**
```php
public function index()
{
    $categories = Category::where('vendor_id', auth()->user()->vendor->id)->get();
    return view('vendor.categories.index', compact('categories'));
}

public function store(Request $request)
{
    Category::create([
        'vendor_id' => auth()->user()->vendor->id,
        'name' => $request->name,
        'description' => $request->description,
        'status' => 'active',
    ]);
    
    return redirect()->back()->with('success', 'Category created!');
}
```

#### 5. Create Admin Panel Controllers

**app/Http/Controllers/Admin/DashboardController.php**
```php
public function index()
{
    $totalUsers = User::count();
    $totalVendors = Vendor::count();
    $totalOrders = Order::count();
    $totalRevenue = Order::where('payment_status', 'paid')->sum('total_amount');
    
    return view('admin.dashboard', compact('totalUsers', 'totalVendors', 'totalOrders', 'totalRevenue'));
}
```

**app/Http/Controllers/Admin/VendorController.php**
```php
public function index()
{
    $vendors = Vendor::with('user')->get();
    return view('admin.vendors.index', compact('vendors'));
}

public function approve(Vendor $vendor)
{
    $vendor->update(['status' => 'active']);
    return redirect()->back()->with('success', 'Vendor approved!');
}

public function reject(Request $request, Vendor $vendor)
{
    $vendor->update(['status' => 'suspended']);
    return redirect()->back()->with('error', 'Vendor rejected!');
}
```

**app/Http/Controllers/Admin/OrderController.php**
```php
public function index()
{
    $orders = Order::with('user', 'vendor')->latest()->get();
    return view('admin.orders.index', compact('orders'));
}
```

**app/Http/Controllers/Admin/UserController.php**
```php
public function index()
{
    $users = User::all();
    return view('admin.users.index', compact('users'));
}

public function updateStatus(Request $request, User $user)
{
    $user->update(['status' => $request->status]);
    return redirect()->back()->with('success', 'User status updated!');
}
```

#### 6. Update Routes (routes/web.php)

```php
use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\Frontend\VendorController;
use App\Http\Controllers\Frontend\ProductController;
use App\Http\Controllers\Frontend\CartController;
use App\Http\Controllers\Frontend\CheckoutController;
use App\Http\Controllers\Frontend\OrderController;
use App\Http\Controllers\Vendor\DashboardController as VendorDashboardController;
use App\Http\Controllers\Vendor\OrderController as VendorOrderController;
use App\Http\Controllers\Vendor\ProductController as VendorProductController;
use App\Http\Controllers\Vendor\CategoryController as VendorCategoryController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\VendorController as AdminVendorController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\UserController as AdminUserController;

// Frontend Routes
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/vendors', [VendorController::class, 'index'])->name('vendors.index');
Route::get('/vendors/{vendor}', [VendorController::class, 'show'])->name('vendors.show');
Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');

// Cart Routes
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
Route::post('/cart/update', [CartController::class, 'update'])->name('cart.update');
Route::post('/cart/remove', [CartController::class, 'remove'])->name('cart.remove');

// Checkout Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
    Route::get('/checkout/success/{orderNumber}', [CheckoutController::class, 'success'])->name('checkout.success');
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
});

// Vendor Routes
Route::middleware(['auth', 'role:vendor'])->prefix('vendor')->name('vendor.')->group(function () {
    Route::get('/dashboard', [VendorDashboardController::class, 'index'])->name('dashboard');
    Route::get('/orders', [VendorOrderController::class, 'index'])->name('orders.index');
    Route::put('/orders/{order}/status', [VendorOrderController::class, 'updateStatus'])->name('orders.updateStatus');
    Route::get('/products', [VendorProductController::class, 'index'])->name('products.index');
    Route::post('/products', [VendorProductController::class, 'store'])->name('products.store');
    Route::put('/products/{product}', [VendorProductController::class, 'update'])->name('products.update');
    Route::delete('/products/{product}', [VendorProductController::class, 'destroy'])->name('products.destroy');
    Route::get('/categories', [VendorCategoryController::class, 'index'])->name('categories.index');
    Route::post('/categories', [VendorCategoryController::class, 'store'])->name('categories.store');
});

// Admin Routes
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/vendors', [AdminVendorController::class, 'index'])->name('vendors.index');
    Route::put('/vendors/{vendor}/approve', [AdminVendorController::class, 'approve'])->name('vendors.approve');
    Route::put('/vendors/{vendor}/reject', [AdminVendorController::class, 'reject'])->name('vendors.reject');
    Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders.index');
    Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
    Route::put('/users/{user}/status', [AdminUserController::class, 'updateStatus'])->name('users.updateStatus');
});
```

---

## Output yang Diharapkan

1. Laravel Breeze terinstall dengan konfigurasi yang sesuai
2. CheckRole middleware dibuat dan diregister
3. Semua controller dibuat:
   - Frontend: Home, Vendor, Product, Cart, Checkout, Order
   - Vendor: Dashboard, Order, Product, Category
   - Admin: Dashboard, Vendor, Order, User
4. Routes di web.php diupdate dengan benar
5. Sistem role-based access working

---

## Testing Credentials (After Setup)

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@dwarungs.com | password123 |
| Vendor | vendor@dwarungs.com | password123 |
| Cashier | cashier@dwarungs.com | password123 |
| Customer | customer@dwarungs.com | password123 |


