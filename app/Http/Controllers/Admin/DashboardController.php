<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;

class DashboardController extends Controller
{
    public function __invoke()
    {
        return view('admin.dashboard', [
            'clientCount' => User::where('role', 'client')->count(),
            'adminCount' => User::where('role', 'admin')->count(),
            'verifiedCount' => User::whereNotNull('email_verified_at')->count(),
        ]);
    }
}
