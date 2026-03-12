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
        $cart = session()->get('cart', []);

        if (empty($cart)) {
            return redirect()->route('cart.index')->with('error', 'Cart is empty!');
        }

        // Get vendor_id from first item in cart
        $firstItem = reset($cart);
        $vendorId = $firstItem['vendor_id'] ?? null;

        $order = Order::create([
            'user_id' => auth()->id(),
            'vendor_id' => $vendorId,
            'order_number' => 'ORD-'.date('Ymd').'-'.str_pad(Order::count() + 1, 4, '0', STR_PAD_LEFT),
            'subtotal' => array_sum(array_map(function ($item) {
                return $item['price'] * $item['quantity'];
            }, $cart)),
            'total_amount' => array_sum(array_map(function ($item) {
                return $item['price'] * $item['quantity'];
            }, $cart)),
            'status' => 'pending',
            'payment_method' => $request->payment_method,
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

        return redirect()->route('checkout.success', $order->order_number)->with('success', 'Order placed successfully!');
    }

    public function success($orderNumber)
    {
        $order = Order::where('order_number', $orderNumber)->firstOrFail();

        return view('checkout.success', compact('order'));
    }
}
