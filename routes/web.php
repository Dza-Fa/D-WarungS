<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\VendorController as AdminVendorController;
use App\Http\Controllers\Cashier\DashboardController as CashierDashboardController;
use App\Http\Controllers\Cashier\OrderController as CashierOrderController;
use App\Http\Controllers\Frontend\CartController;
use App\Http\Controllers\Frontend\CheckoutController;
use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\Frontend\OrderController;
use App\Http\Controllers\Frontend\ProductController;
use App\Http\Controllers\Frontend\VendorController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Vendor\CategoryController as VendorCategoryController;
use App\Http\Controllers\Vendor\DashboardController as VendorDashboardController;
use App\Http\Controllers\Vendor\OrderController as VendorOrderController;
use App\Http\Controllers\Vendor\ProductController as VendorProductController;
use Illuminate\Support\Facades\Route;

// Public Routes
Route::get('/', [HomeController::class, 'index'])->name('home');

// Component Test Page
Route::get('/components/test', function () {
    return view('components.test');
})->name('components.test');

Route::get('/vendors', [VendorController::class, 'index'])->name('vendors.index');
Route::get('/vendors/{vendor}', [VendorController::class, 'show'])->name('vendors.show');
Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');

// Cart Routes
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
Route::post('/cart/update', [CartController::class, 'update'])->name('cart.update');
Route::post('/cart/remove', [CartController::class, 'remove'])->name('cart.remove');
Route::get('/cart/update', function () {
    return redirect()->route('cart.index');
})->name('cart.update.get');
Route::get('/cart/recalculate', [CartController::class, 'recalculate'])->name('cart.recalculate');
Route::get('/cart/count', [CartController::class, 'count'])->name('cart.count');

// Main Dashboard - Redirect based on role
Route::middleware(['auth', 'recaptcha'])->group(function () {
    Route::get('/dashboard', function () {
        $user = auth()->user();
        if ($user->role === 'admin') {
            return redirect()->route('admin.dashboard');
        } elseif ($user->role === 'vendor') {
            return redirect()->route('vendor.dashboard.index');
        } elseif ($user->role === 'cashier') {
            return redirect()->route('cashier.dashboard');
        }

        return redirect()->route('home');
    })->name('dashboard');

    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
    Route::get('/checkout/success/{orderNumber}', [CheckoutController::class, 'success'])->name('checkout.success');
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::post('/orders/{order}/pickup', [OrderController::class, 'pickup'])->name('orders.pickup');
    Route::post('/orders/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Vendor Routes
Route::middleware(['auth', 'role:vendor'])->prefix('vendor')->name('vendor.')->group(function () {
    Route::get('/dashboard', [VendorDashboardController::class, 'index'])->name('dashboard.index');
    Route::get('/orders', [VendorOrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [VendorOrderController::class, 'show'])->name('orders.show');
    Route::put('/orders/{order}/start', [VendorOrderController::class, 'start'])->name('orders.start');
    // Vendor can only mark order as ready when payment is confirmed
    Route::put('/orders/{order}/ready', [VendorOrderController::class, 'markReady'])->name('orders.ready');

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
    Route::get('/vendors/create', [AdminVendorController::class, 'create'])->name('admin.vendors.create');
    Route::post('/vendors', [AdminVendorController::class, 'store'])->name('admin.vendors.store');
    Route::put('/vendors/{vendor}/approve', [AdminVendorController::class, 'approve'])->name('vendors.approve');

    Route::put('/vendors/{vendor}/reject', [AdminVendorController::class, 'reject'])->name('vendors.reject');

    Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders.index');
    Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
    Route::put('/users/{user}/status', [AdminUserController::class, 'updateStatus'])->name('users.updateStatus');
});

// Cashier Routes
Route::middleware(['auth', 'role:cashier'])->prefix('cashier')->name('cashier.')->group(function () {
    Route::get('/dashboard', [CashierDashboardController::class, 'index'])->name('dashboard');
    Route::get('/orders', [CashierOrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [CashierOrderController::class, 'show'])->name('orders.show');
    Route::post('/orders/{order}/confirm', [CashierOrderController::class, 'confirmPayment'])->name('orders.confirm');
    Route::post('/orders/{order}/cancel', [CashierOrderController::class, 'cancel'])->name('orders.cancel');
});

require __DIR__.'/auth.php';
