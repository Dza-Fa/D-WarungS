<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Vendor;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    public function index()
    {
        $cart = session()->get('cart', []);
        $vendors = Vendor::where('status', 'active')->get();

        return view('checkout.index', compact('cart', 'vendors'));
    }

    public function store(Request $request)
    {
        try {
            $cart = session()->get('cart', []);

            if (empty($cart)) {
                return redirect()->route('cart.index')->with('error', 'Keranjang kosong!');
            }

            // Validate vendor consistency
            $vendorIds = array_unique(array_column($cart, 'vendor_id'));
            if (count($vendorIds) > 1) {
                return redirect()->route('cart.index')->with('error', 'Pesanan hanya boleh dari satu vendor!');
            }

            $vendorId = $vendorIds[0];

            // Calculate totals safely
            $subtotal = 0;
            foreach ($cart as $item) {
                $subtotal += $item['price'] * $item['quantity'];
            }

            $order = Order::create([
                'user_id' => auth()->id(),
                'vendor_id' => $vendorId,
                'order_number' => 'ORD-'.date('Ymd').'-'.str_pad(Order::count() + 1, 4, '0', STR_PAD_LEFT),
                'subtotal' => $subtotal,
                'total_amount' => $subtotal,
                'status' => 'pending',
                'payment_method' => $request->payment_method ?? 'cod',
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

            return redirect()->route('checkout.success', $order->order_number)->with('success', 'Pesanan berhasil dibuat!');
        } catch (\Exception $e) {
            Log::error('Checkout failed: ' . $e->getMessage(), ['user_id' => auth()->id()]);
            return redirect()->route('cart.index')->with('error', 'Gagal membuat pesanan. Silakan coba lagi.');
        }
    }

    public function success($orderNumber)
    {
        $order = Order::where('order_number', $orderNumber)->firstOrFail();

        return view('checkout.success', compact('order'));
    }
}
