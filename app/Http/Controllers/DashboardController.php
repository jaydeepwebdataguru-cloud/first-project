<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        // Exclude the logged-in user from the list
        $users = User::where('id', '!=', Auth::id())->get();

        return view('dashboard', compact('users'));
    }
}
