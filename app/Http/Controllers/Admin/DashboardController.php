<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use App\Models\Vendor;

class DashboardController extends Controller
{
    public function index()
    {
        $totalUsers = User::count();
        $totalVendors = Vendor::count();
        $totalOrders = Order::count();
        $totalRevenue = Order::where('payment_status', 'paid')->sum('total_amount');

        return view('admin.dashboard', compact('totalUsers', 'totalVendors', 'totalOrders', 'totalRevenue'));
    }
}
