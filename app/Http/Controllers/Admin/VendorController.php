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

    public function create()
    {
        return view('admin.vendors.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'description' => 'nullable|string',
            'status' => 'required|in:pending,active,suspended',
        ]);

        // Create user first
            $user = \App\Models\User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'role' => 'vendor',
            'password' => bcrypt('password'), // Change in prod
            'status' => 'active',
        ]);

        // Create vendor
        Vendor::create([
            'user_id' => $user->id,
            'name' => $request->name,
            'address' => $request->address,
            'phone' => $request->phone,
            'description' => $request->description,
            'status' => $validated['status'],
        ]);

        return redirect()->route('admin.vendors.index')->with('success', 'Vendor baru berhasil ditambahkan!');
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

