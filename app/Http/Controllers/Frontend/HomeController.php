<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Vendor;

class HomeController extends Controller
{
    public function index()
    {
        $vendors = Vendor::where('status', 'active')->with('categories')->get();

        return view('home', compact('vendors'));
    }
}
