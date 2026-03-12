<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::where('vendor_id', auth()->user()->vendor->id)->with('category')->get();

        return view('vendor.products.index', compact('products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'price' => 'required|numeric',
        ]);

        Product::create([
            'vendor_id' => auth()->user()->vendor->id,
            'category_id' => $request->category_id,
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'status' => 'active',
        ]);

        return redirect()->route('vendor.products.index')->with('success', 'Product created!');
    }

    public function update(Request $request, Product $product)
    {
        $product->update($request->all());

        return redirect()->back()->with('success', 'Product updated!');
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return redirect()->back()->with('success', 'Product deleted!');
    }
}
