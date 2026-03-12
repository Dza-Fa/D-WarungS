<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use App\Models\Order;

class DashboardController extends Controller
{
    public function index()
    {
        // Kasir dapat melihat semua pesanan yang belum lunas
        $orders = Order::with('user', 'vendor')
            ->whereIn('payment_status', ['pending', 'paid'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('cashier.dashboard', compact('orders'));
    }

    public function confirmPayment(Order $order)
    {
        $order->update([
            'payment_status' => 'paid',
            'status' => 'confirmed',
        ]);

        return redirect()->back()->with('success', 'Pembayaran berhasil dikonfirmasi!');
    }

    public function show(Order $order)
    {
        $order->load('orderItems.product', 'vendor', 'user');

        return view('cashier.orders.show', compact('order'));
    }
}
