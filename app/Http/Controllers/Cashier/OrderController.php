<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * Display a listing of all orders (filtered by status).
     */
    public function index(Request $request)
    {
        $status = $request->get('status', 'all');

        $query = Order::with('user', 'vendor', 'orderItems');

        if ($status !== 'all') {
            if ($status === 'pending_payment') {
                $query->where('payment_status', 'pending');
            } elseif ($status === 'paid') {
                $query->where('payment_status', 'paid');
            } else {
                $query->where('status', $status);
            }
        }

        $orders = $query->latest()->get();

        // Get counts for statistics
        $pendingCount = Order::where('payment_status', 'pending')->count();
        $paidCount = Order::where('payment_status', 'paid')->count();
        $preparingCount = Order::where('status', 'preparing')->count();
        $readyCount = Order::where('status', 'ready')->count();

        return view('cashier.orders.index', compact('orders', 'status', 'pendingCount', 'paidCount', 'preparingCount', 'readyCount'));
    }

    /**
     * Display the specified order.
     */
    public function show(Order $order)
    {
        $order->load('orderItems.product', 'vendor', 'user');

        return view('cashier.orders.show', compact('order'));
    }

    /**
     * Confirm payment for an order.
     */
    public function confirmPayment(Order $order)
    {
        if (! $order->canBeConfirmedByCashier()) {
            return redirect()->back()->with('error', 'Order cannot be confirmed!');
        }

        $order->update([
            'payment_status' => 'paid',
            'status' => 'confirmed', // Order confirmed and ready for preparation
        ]);

        return redirect()->route('cashier.orders.index')->with('success', 'Pembayaran berhasil dikonfirmasi! Pesanan akan diproses oleh vendor.');
    }

    /**
     * Cancel an order.
     */
    public function cancel(Request $request, Order $order)
    {
        $order->update([
            'status' => 'cancelled',
            'notes' => $request->notes ?? 'Dibatalkan oleh kasir',
        ]);

        return redirect()->route('cashier.orders.index')->with('success', 'Pesanan telah dibatalkan.');
    }
}
