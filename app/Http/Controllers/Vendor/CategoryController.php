<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::where('vendor_id', auth()->user()->vendor->id)->get();

        return view('vendor.categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        Category::create([
            'vendor_id' => auth()->user()->vendor->id,
            'name' => $request->name,
            'description' => $request->description,
            'status' => 'active',
        ]);

        return redirect()->back()->with('success', 'Category created!');
    }
}
