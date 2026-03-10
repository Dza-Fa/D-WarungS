<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        $users = User::all();
        return view('admin.users.index', compact('users'));
    }

    public function updateStatus(Request $request, User $user)
    {
        $user->update(['status' => $request->status]);
        return redirect()->back()->with('success', 'User status updated!');
    }
}

