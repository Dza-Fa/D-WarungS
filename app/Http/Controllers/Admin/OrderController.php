<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::with('user', 'vendor')->latest()->get();

        return view('admin.orders.index', compact('orders'));
    }
}
