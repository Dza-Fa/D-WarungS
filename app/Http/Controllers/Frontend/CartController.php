<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index()
    {
        $cart = session()->get('cart', []);
        return view('cart.index', compact('cart'));
    }

    public function add(Request $request)
    {
        $product = Product::findOrFail($request->product_id);
        $cart = session()->get('cart', []);
        
        $cart[$product->id] = [
            'name' => $product->name,
            'price' => $product->price,
            'quantity' => $request->quantity ?? 1,
            'image' => $product->image,
            'vendor_id' => $product->vendor_id,
        ];
        
        session()->put('cart', $cart);
        return redirect()->back()->with('success', 'Product added to cart!');
    }

    public function update(Request $request)
    {
        $cart = session()->get('cart', []);
        $cart[$request->id]['quantity'] = $request->quantity;
        session()->put('cart', $cart);
        return response()->json(['success' => true]);
    }

    public function remove(Request $request)
    {
        $cart = session()->get('cart', []);
        unset($cart[$request->id]);
        session()->put('cart', $cart);
        return response()->json(['success' => true]);
    }
}

