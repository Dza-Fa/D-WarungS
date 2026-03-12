<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Order;

class DashboardController extends Controller
{
    public function index()
    {
        $vendor = auth()->user()->vendor;
        // Hanya menampilkan pesanan yang sudah lunas (sudah dikonfirmasi kasir)
        $orders = Order::where('vendor_id', $vendor->id)
            ->where('payment_status', 'paid')
            ->with('user')
            ->latest()
            ->take(10)
            ->get();
        $totalOrders = Order::where('vendor_id', $vendor->id)->where('payment_status', 'paid')->count();
        $totalRevenue = Order::where('vendor_id', $vendor->id)->where('payment_status', 'paid')->sum('total_amount');

        return view('vendor.dashboard', compact('vendor', 'orders', 'totalOrders', 'totalRevenue'));
    }
}
