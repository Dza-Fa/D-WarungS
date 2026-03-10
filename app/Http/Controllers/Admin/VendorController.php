<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Vendor;
use Illuminate\Http\Request;

class VendorController extends Controller
{
    public function index()
    {
        $vendors = Vendor::with('user')->get();
        return view('admin.vendors.index', compact('vendors'));
    }

    public function approve(Vendor $vendor)
    {
        $vendor->update(['status' => 'active']);
        return redirect()->back()->with('success', 'Vendor approved!');
    }

    public function reject(Request $request, Vendor $vendor)
    {
        $vendor->update(['status' => 'suspended']);
        return redirect()->back()->with('error', 'Vendor rejected!');
    }
}

