<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Order;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::where('user_id', auth()->id())->with('vendor', 'orderItems')->latest()->get();

        return view('orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        // Ensure the order belongs to this user
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }
        $order->load('orderItems.product', 'vendor');

        return view('orders.show', compact('order'));
    }

    /**
     * Customer picks up their order.
     */
    public function pickup(Order $order)
    {
        // Ensure the order belongs to this user
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        if (! $order->canBePickedUpByCustomer()) {
            return redirect()->back()->with('error', 'Order cannot be picked up yet!');
        }

        $order->update(['status' => 'completed']);

        return redirect()->back()->with('success', 'Terima kasih! Pesanan telah diambil.');
    }

    /**
     * Customer cancels their order.
     */
    public function cancel(Order $order)
    {
        // Ensure the order belongs to this user
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        // Only allow cancellation if order status allows it
        if (! in_array($order->status, ['pending', 'confirmed'])) {
            return redirect()->back()->with('error', 'Pesanan tidak dapat dibatalkan pada status saat ini.');
        }

        $order->update(['status' => 'cancelled']);

        return redirect()->back()->with('success', 'Pesanan telah dibatalkan.');
    }
}
