<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $vendor = auth()->user()->vendor;
        $orders = Order::where('vendor_id', $vendor->id)->with('user')->latest()->take(10)->get();
        $totalOrders = Order::where('vendor_id', $vendor->id)->count();
        $totalRevenue = Order::where('vendor_id', $vendor->id)->where('payment_status', 'paid')->sum('total_amount');
        
        return view('vendor.dashboard', compact('vendor', 'orders', 'totalOrders', 'totalRevenue'));
    }
}

