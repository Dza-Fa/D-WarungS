<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    /**
     * Display a listing of the vendor's orders.
     */
    public function index(Request $request)
    {
        $status = $request->get('status', 'all');

        $vendor = auth()->user()->vendor;
        if (! $vendor) {
            abort(403, 'Vendor profile not found.');
        }

        $query = Order::where('vendor_id', $vendor->id)
            ->where('payment_status', 'paid'); // Only paid orders

        if ($status !== 'all' && $status !== 'paid') {
            $query->where('status', $status);
        }

        $orders = $query->with('user', 'orderItems')->latest()->get();

        return view('vendor.orders.index', compact('orders', 'status'));
    }

    /**
     * Display the specified order.
     */
    public function show(Order $order)
    {
        $vendor = auth()->user()->vendor;
        if (! $vendor) {
            abort(403, 'Vendor profile not found.');
        }

        // Ensure order belongs to this vendor
        if ($order->vendor_id !== $vendor->id) {
            abort(403);
        }

        $order->load('orderItems.product', 'user');

        return view('vendor.orders.show', compact('order'));
    }

    /**
     * Mark order as ready for pickup (only action vendor can do).
     * Vendor can only mark order as ready when payment is confirmed.
     */
    public function markReady(Order $order)
    {
        try {
            $vendor = auth()->user()->vendor;
            if (! $vendor) {
                throw new \Exception('Vendor profile not found.');
            }

            // Vendor can only mark as ready when payment is paid and status is preparing
            if (! $order->isPaid() || $order->status !== Order::STATUS_PREPARING) {
                return redirect()->back()->with('error', 'Pesanan belum dalam tahap persiapan. Harap mulai siapkan pesanan terlebih dahulu.');
            }

            if ($order->vendor_id !== $vendor->id) {
                throw new \Exception('Unauthorized vendor.');
            }

            $order->update(['status' => Order::STATUS_READY]);

            return redirect()->back()->with('success', 'Pesanan siap diambil!');
        } catch (\Exception $e) {
            Log::error('Mark ready failed: ' . $e->getMessage(), ['order_id' => $order->id, 'vendor_id' => auth()->id()]);
            return redirect()->back()->with('error', 'Gagal update status. Silakan coba lagi.');
        }
    }

    /**
     * Start preparing order
     */
    public function start(Order $order)
    {
        try {
            $vendor = auth()->user()->vendor;
            if (! $vendor) {
                throw new \Exception('Vendor profile not found.');
            }

            if ($order->vendor_id !== $vendor->id) {
                throw new \Exception('Unauthorized vendor.');
            }

            if (! $order->isPaid() || $order->status !== Order::STATUS_CONFIRMED) {
                return redirect()->back()->with('error', 'Pesanan belum dapat diproses.');
            }

            $order->update(['status' => Order::STATUS_PREPARING]);

            return redirect()->back()->with('success', 'Pesanan mulai dipersiapkan!');
        } catch (\Exception $e) {
            Log::error('Start preparation failed: ' . $e->getMessage(), ['order_id' => $order->id, 'vendor_id' => auth()->id()]);
            return redirect()->back()->with('error', 'Gagal memulai persiapan. Silakan coba lagi.');
        }
    }

    /**
     * Update order status (legacy method).
     */
    public function updateStatus(Request $request, Order $order)
    {
        $vendor = auth()->user()->vendor;
        if (! $vendor) {
            abort(403, 'Vendor profile not found.');
        }

        if ($order->vendor_id !== $vendor->id) {
            abort(403);
        }

        $order->update(['status' => $request->status]);

        return redirect()->back()->with('success', 'Order status updated!');
    }
}