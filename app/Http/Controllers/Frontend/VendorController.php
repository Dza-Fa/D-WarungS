<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Vendor;
use Illuminate\Http\Request;

class VendorController extends Controller
{
    public function index()
    {
        $vendors = Vendor::where('status', 'active')->with('categories', 'products')->paginate(12);
        return view('vendors.index', compact('vendors'));
    }

    public function show(Vendor $vendor)
    {
        $vendor->load('categories.products');
        return view('vendors.show', compact('vendor'));
    }
}

